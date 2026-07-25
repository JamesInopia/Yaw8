<?php
class Database {
    private static ?PDO $connection = null;
    public static function connect(): PDO {
        if (self::$connection === null) {
            $config = require '../app/config/database.php';
            self::$connection = new PDO(
            "mysql:host={$config['host']}; dbname={$config['dbname']}",
            $config['user'],
            $config['password']
            );
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$connection;
    }
}