<?php
require 'includes/db-connection.php';
require 'includes/session.php';
require 'includes/functions.php';
require 'includes/settings.php';

// if user just logged in redirect to his home, can access only to this page by changing the path
if ($logged_in) {
	header('Location: home.php');
	exit;
}

// only if it is new proceed
// execute only if it is defined 
if (isset($_SESSION['user_exist'])) {
    $user_exist = $_SESSION['user_exist']; 
}

// function for erase row of email verification database 
function erase_verification_row($pdo, $email_delate) {
    $sql_delete_verification_row = "DELETE FROM email_verification
                                    WHERE email = :email;";
    pdo($pdo, $sql_delete_verification_row, [$email_delate]);
}

$member = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user_exist == 'new') {

    // given the email check if the hash code correspond and if it has not expired 
    $email = $_SESSION['validation_email'];

    // get the submitted verification code and hash it to compare 
    $verification_code = $_POST['code'];

    // get code and time and compare 
    // get the message limitied by the room id 
    $sql_get_verification_code = "SELECT hash_code, unix_time
                                FROM email_verification
                                WHERE email = :email;";     
    $statement = pdo($pdo, $sql_get_verification_code, [$email]);
    $verification_data = $statement->fetch();

    // if the code is expired redirect to email verification main page 
    // get current unix time 
    $current_unix_time = time();

    // if not expired and valid then proceed with the registration
    // then erase the row in the database 
    if ($current_unix_time - $verification_data['unix_time'] <= $email_verification_time && 
        password_verify($verification_code, $verification_data['hash_code'])) {

        // erase the row in the database
        erase_verification_row($pdo, $email);

        // the status of user that register themself is offline only if they login are online 
        $sql_register = "INSERT INTO members (name, email, password_hash, live_status)
                        VALUES (:name, :email, :password, :live_status);";

        $member['name'] = $_POST['name'];
        $member['email'] = $email;
        $member['password'] = $_POST['password'];

        $member['live_status'] = 'offline';

        $password_hash = password_hash($member['password'], PASSWORD_DEFAULT);
        $member['password'] = $password_hash;

        pdo($pdo, $sql_register, $member);

        // direct to login 
        header('Location: index.php'); 
        exit;
    }

    // in any other case just erase the row of the databse 
    erase_verification_row($pdo, $email);
    // redirect to main page thus reload page
    header('Location: email-verification.php');
    exit;
}

?>

<!DOCTYPE html>
<html>
    <head>
        <?php include 'includes/head.php' ?>
        
        <script>
            function check_user_exist(user_name) {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById('user_exist').innerHTML = this.responseText;
                    }
                };
                xhttp.open("POST", "AJAX/register_check.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send('user_name=' + user_name);
            }
        </script>
    </head>
    <body>
		<div class="login-register">
			<h2 class="page-link"><a href="register.php">Register</a></h2>
			<form action="register.php" method="POST" autocomplete="off"> 
                <div class="user-input">
					<input type="text" class="email_verification_code" name="code" placeholder="123456" required>
				</div>
				<div class="user-input">
					<input type="text" class="input_text" name="name" placeholder="Username" 
                            onkeyup='check_user_exist(this.value)' required>
				</div>
				<div class="user-input">
					<input type="password" class="input_text" name="password" placeholder="Password" required>
				</div>
				<div class="submit-redirect">
					<input type="submit" class="button" value="Register">
                    <span id="user_exist"></span>
					<span class="redirect-link"><a href="index.php">Login</a></span>
				</div>
			</form>
		</div>

<?php include 'includes/footer.php' ?>