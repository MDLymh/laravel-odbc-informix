<?php

namespace MDLymh\Odbc;

use PDOStatement;

class ODBCPdoStatement extends PDOStatement
{
    protected $query;
    protected $params = [];
    protected $statement;
    protected int $fetchMode = \PDO::FETCH_BOTH;

    public function __construct($conn, $query)
    {
        $this->query = preg_replace('/(?<=\s|^):[^\s:]++/um', '?', $query);

        $this->params = $this->getParamsFromQuery($query);

        $this->statement = odbc_prepare($conn, $this->query);
    }

    protected function getParamsFromQuery($qry)
    {
        $params = [];
        $qryArray = explode(" ", $qry);
        $i = 0;

        while (isset($qryArray[$i])) {
            if (preg_match("/^:/", $qryArray[$i]))
            {
                $namedParam = substr($qryArray[$i], 1); // omite el : del nombre (MK)
                $params[$namedParam] = null;
            }

            $i++;
        }

        return $params;
    }

    public function rowCount()
    {
        return odbc_num_rows($this->statement);
    }

    public function bindValue($param, $val, $ignore = null)
    {
        $this->params[$param] = $val;
    }

    public function setFetchMode(int $mode, mixed ...$args): bool
    {
        $this->fetchMode = $mode;
        return true;
    }

    public function execute($ignore = null)
    {
        odbc_execute($this->statement, $this->params);
        $this->params = [];
    }

    public function fetchAll(int $mode = \PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        odbc_execute($this->statement, $this->params);
        $this->params = [];

        $records = [];

        if ($mode === \PDO::FETCH_DEFAULT) {
            $mode = $this->fetchMode;
        }

        switch ($mode) {
            case \PDO::FETCH_CLASS:
                $class  = $args[0] ?? 'stdClass';
                $ctor   = $args[1] ?? [];
                while ($row = odbc_fetch_array($this->statement)) {
                    $records[] = (new \ReflectionClass($class))
                                    ->newInstanceArgs(array_merge($ctor, [$row]));
                }
                break;

            case \PDO::FETCH_OBJ:
                while ($row = odbc_fetch_object($this->statement)) {
                    $records[] = $row;
                }
                break;

            default: // FETCH_ASSOC / FETCH_BOTH …
                while ($row = odbc_fetch_array($this->statement)) {
                    $records[] = $row;
                }
        }

        return $records;
    }

    public function fetch($option = null, $ignore = null, $ignore2 = null)
    {
        $mode = $option === null ? $this->fetchMode : $option;

        $rec = null;

        switch ($mode) {
            case \PDO::FETCH_OBJ:
                $rec = odbc_fetch_object($this->statement);
                break;

            default:
                $rec = odbc_fetch_array($this->statement);
        }

        if ($rec)
        {
            // odbc_fetch_array has a bounds checking bug with utf8 strings, so we sanitize it:
            if (is_array($rec)) {
                $this->sanitize_array($rec);
            }
        }

        return $rec;
    }

    private function sanitize_array(&$rec)
    {
        foreach($rec as $key => $value)
        {

            $pos = strpos($value, chr(0));

            if ($pos)
            {
                // $valueOrig = $value;
                $value = substr($value, 0, $pos);
                // \Log::info($value . "|" . $valueOrig . "|");
                $rec[$key] = $value;
            }
        }
    }

    public function fetch_into($records)
    {
    // (MK) official doc https://www.redbooks.ibm.com/redbooks/pdfs/sg247218.pdf page 186
        odbc_fetch_into($this->statement, $records);
        return $records;
    }
}
