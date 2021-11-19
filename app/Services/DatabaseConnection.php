<?php

namespace App\Services;
use MongoDB\Client as mongo;

class DatabaseConnection
{
    function db_connection()
    {
        $collect = (new mongo)->test;
        return $collect;
    }
}