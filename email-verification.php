<?php
require 'includes/db-connection.php';
require 'includes/session.php';
require 'includes/functions.php';
require 'includes/settings.php';
require 'includes/mail.php';

// if user just logged in redirect to his home, can access only to this page by changing the path
if ($logged_in) {
	header('Location: home.php');
	exit;
}

// only if it is new proceed and valid with regular expression
// execute only if it is defined 
if (isset($_SESSION['email_valid'])) {
    $email_valid = $_SESSION['email_valid']; 
}

// che uniqueness of the email and the correctess of syntax is given by AJAX 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email']) && $email_valid == true) {

    // generate verfication code 
    $rand_code = mt_rand(100000, 999999);
    // hash verification code 
    $rand_hash_code = password_hash($rand_code, PASSWORD_DEFAULT);

    // get email 
    $email = $_POST['email'];
    // put email in session for access it with when checking the validation code 
    put_in_session('validation_email', $email);

    if ($port == 8889) {
        // send email 
        $address = $email;
        $subject = 'Cerification Code';
        $body = $rand_code;
        send_mail_localHost($mail_server_local, 
                            $mail_username_local, 
                            $mail_password_local, 
                            $address, 
                            $subject, 
                            $body);
    } else {
        // send email 
        $address = $email;
        $subject = 'Cerification Code';
        $body = $rand_code;
        send_mail_liveServer($mail_server_live, 
                            $mail_username_live, 
                            $mail_password_live, 
                            $address, 
                            $subject, 
                            $body);
    }

    // get the unix time of creation of the code 
    $unix_time = time();

    // put the email and the hashed code into database 
    $sql_email_verification = "INSERT INTO email_verification (email, hash_code, unix_time)
                                VALUES (:email, :rand_hash_code, :unix_time);";
    pdo($pdo, $sql_email_verification, [$email, $rand_hash_code, $unix_time]);

    // direct to register 
    header('Location: register.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include 'includes/head.php' ?>

        <script>
            function check_email_exist(email) {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById('email_exist').innerHTML = this.responseText;
                    }
                };
                xhttp.open("POST", "AJAX/email_check.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send('email=' + email);
            }
        </script>
    </head>
    <body>
		<div class="login-register">
			<h2 class="page-link"><a href="email-verification.php">Email Verification</a></h2>
			<form action="email-verification.php" method="POST" autocomplete="off"> 
                <div class="user-input">
                    <input type="text" class="input_text" name="email" placeholder="abc.xyz@edu.unife.it" 
                            onkeyup='check_email_exist(this.value)' required>
                </div>
                <div class="verification-message">
                    The verification code will expire in <?= $email_verification_time?> second 
                </div>
                <div class="submit-redirect">
                    <input type="submit" class="button" value="Send code">
                    <span id="email_exist"></span>
                    <span class="redirect-link"><a href="index.php">Login</a></span>
                </div>
			</form>
		</div>

<?php include 'includes/footer.php' ?>

