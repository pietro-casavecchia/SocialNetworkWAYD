<?php
session_start();

$logged_in = $_SESSION['logged_in'] ?? false;
$email_verified = $_SESSION['email_verified'] ?? false;

function login() {
    session_regenerate_id(true);
    $_SESSION['logged_in'] = true;
}

function require_login($logged_in) {
    if ($logged_in == false) {
        header('Location: index.php'); 
        exit;
    }
}

function email_verfication($email_verified) {
    if ($email_verified == false) {
        header('Location: email-verification.php');
        exit;
    }
}

function logout() {
    $params = session_get_cookie_params();
    setcookie(
        'PHPSESSID',
        '',
        time() - 3600,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
    session_destroy();
}

// function to put data in session when needed 
function put_in_session($name, $value) {
    $_SESSION[$name] = $value;
}