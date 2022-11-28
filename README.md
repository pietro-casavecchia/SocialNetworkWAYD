YOU NEED TO PUT ALL THE FILES IN HTDOCS IF WANT TO RUN LOCALLY

Layers form top to bottom 
1. Login & register 
2. Home live search & Profile & Logout
3. Statistics
4. Rooms
5. Chat

http://localhost:8888/WAYD/public/SocialNetwork_#/index.php

```
*** DB connection local: ***

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

*** DB connection live: ***
*** Associate user privilage to the database ***

<?php
$type     = 'mysql';
$server   = 'localhost';
$db       = '???';
$port     = '3306';
$charset  = 'utf8';

$username = '???';
$password = '???';

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
```



