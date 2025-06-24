<?php

namespace Core;

use PDO;
use App\Config;

abstract class Model
{
    protected static function getDB()
    {
        static $db = null;

        if ($db === null)
        {
            $dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME . ';charset=utf8';
            $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
            $db = new PDO($dsn, Config::DB_USER, Config::DB_PASSWORD,$options);
        }

        return $db;
    }
}
