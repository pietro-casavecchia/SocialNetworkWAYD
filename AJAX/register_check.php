<?php
require '../includes/db-connection.php';
require '../includes/session.php';
require '../includes/functions.php';

// check if the user already exist 
$user_name = $_POST['user_name'];

$sql_name = "SELECT name
            FROM members
            WHERE name = :user_name;";

$statement = pdo($pdo, $sql_name, [$user_name]);
$user_exist = $statement->fetch();
$user_exist = $user_exist ? 'exist' : 'new';
put_in_session('user_exist', $user_exist);

if ($user_name == '') {
    echo '';
} else if ($user_exist == 'exist') {
    echo 'Already exist';
} else if ($user_exist == 'new') {
    echo 'Valid name';
} 