## ODBC integration for Laravel Framework using IBM Informix Database
This integration allows the use of <b>odbc_*</b> php function with Laravel framework instead of PDO.<br>
It emulates PDO class used by Laravel.<br>
This is a fork of the project mkrohn/laravel-odbc, but customized for informix.


### # How to install
> `composer require mdlymh/laravel-odbc-informix` To add source in your project

### # Usage Instructions
It's very simple to configure:

**1) Add database to database.php file**
```PHP
'odbc-informix-name' => [
    'driver' => 'odbc',
    'dsn' => env('DB_DSN', 'informix'),
    'database' => env('DB_NAME', 'laravel'),
    'odbc' => true,
    'host' => env('DB_HOST','127.0.0.1'),
    'username' => env('DB_USER','informix'),
    'password' => env('DB_PASS','')
    'options' => [
        'processor' => MDLymh\Odbc\Informix\Query\Processors\InformixProcessor::class,
        'grammar' => [
            'query' => MDLymh\Odbc\Informix\Query\Grammars\InformixGrammar::class,
            'schema' => MDLymh\Odbc\Informix\Schema\Grammars\InformixGrammar::class
        ]
    ]
]
```

### # Eloquent ORM
You can use Laravel, Eloquent ORM and other Illuminate's components as usual.
```PHP
# Facade
$books = DB::connection('odbc-connection-name')->table('books')->where...;

# ORM
$books = Book::where...->get();
```


