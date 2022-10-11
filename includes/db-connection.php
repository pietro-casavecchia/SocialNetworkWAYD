<?php
$type     = 'mysql';
$server   = 'localhost';
$db       = 'social_network_db';
$port     = '8889';
$charset  = 'utf8';

$username = 'testuser';
$password = 'testuserpassword42';

$options  = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "$type:host=$server;dbname=$db;port=$port;charset=$charset"; 
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), $e->getCode());
}