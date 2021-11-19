<?php

namespace App\Services;
use MongoDB\Client as mongo;

class DatabaseConnection
{
    function db_connection($table)
    {
        $collect = (new mongo)->test->$table;
        return $collect;
    }
}