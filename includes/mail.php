<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

// use two function instead of only one with different parampters for better cutomization
function send_mail_localHost($mail_server_local, $mail_username_local, $mail_password_local, $address, $subject, $body) {
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug  = 0;
        // 0 = off (for production use, No debug messages)
        // 1 = client messages
        // 2 = client and server messages
        $mail->isSMTP();
        $mail->Host       = $mail_server_local;
        $mail->SMTPAuth   = true;
        $mail->Username   = $mail_username_local;
        $mail->Password   = $mail_password_local;
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->setFrom($mail_username_local);
        $mail->addAddress($address); 

        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function send_mail_liveServer($mail_server_live, $mail_username_live, $mail_password_live, $address, $subject, $body) {
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug  = 0;
        // 0 = off (for production use, No debug messages)
        // 1 = client messages
        // 2 = client and server messages
        $mail->isSMTP();
        $mail->Host       = $mail_server_live;
        $mail->SMTPAuth   = true;
        $mail->Username   = $mail_username_live;
        $mail->Password   = $mail_password_live;
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->setFrom($mail_username_live);
        $mail->addAddress($address); 

        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}