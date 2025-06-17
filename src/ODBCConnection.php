<?php

namespace MDLymh\Odbc;

use Illuminate\Database\Connection;

class ODBCConnection extends Connection
{
    function getDefaultQueryGrammar()
    {
        $queryGrammar = $this->getConfig('options.grammar.query');
        if ($queryGrammar)
            return new $queryGrammar($this);
        return parent::getDefaultQueryGrammar();
    }

    function getDefaultSchemaGrammar()
    {
        $schemaGrammar = $this->getConfig('options.grammar.schema');
        if ($schemaGrammar)
            return new $schemaGrammar($this);
        return parent::getDefaultSchemaGrammar();
    }

    /**
     * Get the default post processor instance.
     *
     * @return ODBCProcessor
     */


    protected function getDefaultPostProcessor()
    {
        $processor = $this->getConfig('options.processor');
        if ($processor)
            return new $processor;
        return new ODBCProcessor;
    }
}
