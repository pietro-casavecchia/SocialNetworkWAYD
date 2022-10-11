<?php
require '../includes/db-connection.php';
require '../includes/session.php';
require '../includes/functions.php';
require '../includes/settings.php';

// check if the email already exist 
$email = $_POST['email'];

$sql_email = "SELECT email
            FROM members
            WHERE email = :email;";

$statement = pdo($pdo, $sql_email, [$email]);
$email_exist = $statement->fetch();
$email_exist = $email_exist ? 'exist' : 'new';

// email validation with regular expressions 
// first the priority of be unife then be unique 
$regex = "/@edu.unife.it/i";
$output_regex = preg_match($regex, $email) ? 'valid' : 'notValid';

// put in session the status of the email 
put_in_session('email_valid', false);
// secret universal email access else standard access 
if ($email == $universal_email) {
    put_in_session('email_valid', true);
    echo '42';
} else if ($email == '') {
    echo '';
} else if ($output_regex == 'notValid') {
    echo 'Not valid';
} else if ($email_exist == 'exist') {
    echo 'Already exist';
} else if ($email_exist == 'new' && $output_regex == 'valid') {
    // output the true state to accept the email
    put_in_session('email_valid', true);
    echo 'Valid email';
}
