<?php

session_start();

require_once 'functions.php';
require_once __DIR__ . '/../libraries/Psr4AutoloaderClass.php';

$loader = new Psr4AutoloaderClass();
$loader->register();

$loader->addNamespace('CT275DC01_lab3', __DIR__ . '/classes');

try {
    $PDO = (new CT275DC01_lab3\PDOFactory())->create([
        'dbhost' => 'localhost',
        'dbname' => 'ct275_lab3',
        'dbuser' => 'postgres',
        'dbpass' => '12345'
    ]);
} catch (Exception $ex) {
    echo 'Không thể kết nối đến PostgreSQL, kiểm tra lại username/password đến PostgreSQL.<br>';
    exit("<pre>{$ex}</pre>");
}
