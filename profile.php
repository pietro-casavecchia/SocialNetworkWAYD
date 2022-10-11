<?php
require 'includes/db-connection.php';
require 'includes/session.php';
require 'includes/functions.php';

// require login for access the page
require_login($logged_in);

$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include 'includes/head.php' ?>
    </head>
    <body>

    <div class="header">
        <div class="header-home">
            <a href="home.php">Home</a>
            <?php if (isset($_SESSION['user_activity'])) { ?>
                <a href="statistics.php">&nbsp/ Stats</a>
            <?php } ?>
            <?php if (isset($_SESSION['participant_room_id'])) { ?>
                <a href="rooms.php">&nbsp/ Rooms</a>
            <?php } ?>
        </div>
    </div>

<?php include 'includes/footer.php' ?>