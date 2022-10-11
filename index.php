<?php
require 'includes/db-connection.php';
require 'includes/session.php';
require 'includes/functions.php';

// if user just logged in redirect to his home, can access only to this page by changing the path
if ($logged_in) {
	header('Location: home.php');
	exit;
}

// if name and password are correct then grant access to home
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$user_name =  $_POST['name'];
	$user_password =  $_POST['password'];

    // get stored hash password given the input name 
	$sql_check = "SELECT id, name, password_hash
			FROM members
			WHERE name = :user_name;";

	$statement = pdo($pdo, $sql_check, [$user_name]);
	$user = $statement->fetch();
	
	if (!$user) {
		header('Location: index.php');
	} else if ($user) {
		$autentication = password_verify($user_password, $user['password_hash']);
		if (!$autentication) {
			header('Location: index.php');
		} else if ($autentication) {
			// change the status form offline to online
			$sql_update_status = "UPDATE members
								SET live_status = 'online'
								WHERE name = :user_name;";
			pdo($pdo, $sql_update_status, [$user_name]);
			// login() function return true
			login();
            // store the name in session
            put_in_session('user_name', $user_name);
			header('Location: home.php');
			exit;
		}
	}
}
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include 'includes/head.php' ?>
    </head>
    <body>
		<div class="login-register">
			<h2 class="page-link"><a href="index.php">Login</a></h2>
			<form action="index.php" method="POST" autocomplete="off"> 
				<div class="user-input">
					<input type="text" class="input_text" name="name" placeholder="Username" required>
				</div>
				<div class="user-input">
					<input type="password" class="input_text" name="password" placeholder="Password" required>
				</div>
				<div class="submit-redirect">
					<input type="submit" class="button" value="Login">
					<span class="redirect-link"><a href="email-verification.php">Register</a></span>
				</div>
			</form>
		</div>

<?php include 'includes/footer.php' ?>