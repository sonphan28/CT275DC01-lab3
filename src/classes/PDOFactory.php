<?php

namespace CT275DC01_lab3;

use PDO;

class PDOFactory
{
    public function create(array $config): PDO
    {
        [
            'dbhost' => $dbhost,
            'dbname' => $dbname,
            'dbuser' => $dbuser,
            'dbpass' => $dbpass
        ] = $config;
        $dsn = "pgsql:host={$dbhost};dbname={$dbname};";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        return new PDO($dsn, $dbuser, $dbpass, $options);
    }
}
