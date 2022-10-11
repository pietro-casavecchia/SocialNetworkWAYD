<?php 
require 'includes/db-connection.php';
require 'includes/session.php';
require 'includes/functions.php';

require 'includes/settings.php';

// require login for access the page
require_login($logged_in);
// if no activity is selected redirect to the home 
if (!isset($_SESSION['user_activity'])) {
    header('Location: home.php');
}
// even if no room is selected redirect to rooms
if (!isset($_SESSION['enter_room_id'])) {
    header('Location: rooms.php');
} 

// get the activity of the room and the user name
$room_id = $_SESSION['enter_room_id'];
$room_activity = $_SESSION['user_activity'];
$user_name = $_SESSION['user_name'];

// add the message in the db
if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('message', $_POST)) {
    // get message info
    $user_message =  $_POST['message'];
    // use gmt time 
    $post_time = gmdate('H:i - j M Y', time());

    $sql_message = "INSERT INTO messages (room_activity, room_id, name, time, message)
            VALUES (:room_activity, :room_id, :user_name, :post_time, :user_message);";
    
    pdo($pdo, $sql_message, [$room_activity, $room_id, $user_name, $post_time, $user_message]);

    // store the messages in an archive because the standard ones will be delated 
    $sql_message_archive = "INSERT INTO messages_archive (room_activity, room_id, name, time, message)
            VALUES (:room_activity, :room_id, :user_name, :post_time, :user_message);";

    pdo($pdo, $sql_message_archive, [$room_activity, $room_id, $user_name, $post_time, $user_message]);
    // avoid resubmission
    header('Location: chat.php');
}
?>

<!DOCTYPE html>
<html>
    <head> 
        <?php include 'includes/head.php' ?>
        
        <script>
            // call the function when submitted the form by having an updated feed
            function live_chat() {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById('chat_feed').innerHTML = this.responseText;
                    }
                };
                xhttp.open("POST", "AJAX/live_chat.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send();
            }
            // function for the info like participants and description
            function live_info() {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById('chat_info').innerHTML = this.responseText;
                    }
                };
                xhttp.open("POST", "AJAX/live_chat_info.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send();
            }
            // call the funciton the first time page load 
            live_chat();
            live_info();
            // call the function every n seconds
            setInterval(live_chat, <?= $time_delay_chat ?>);
            setInterval(live_info, <?= $time_delay_chat ?>);
        </script>
    </head>
    <body>

    <div class="header">
        <div class="header-home">
            <a href="home.php">Home</a>
            <a href="statistics.php">&nbsp/ Stats</a>
            <a href="rooms.php">&nbsp/ Rooms</a>
        </div>
        <div class="profile">
            <a href="profile.php"><?= substr($_SESSION['user_name'], 0, 1) ?></a>
        </div>
    </div>

    <!-- 
        <p>Room activity: <?= $room_activity ?></p>
        <p>Room id: <?= $room_id ?></p>
    -->

    <div id="chat_info"></div>
    
    <div class="chat-box">
        <form action="chat.php" method="POST" onSubmit="return live_chat()">
            <div>
                <input class="message" type="text" id="message" name="message" required>
                <input class="send-message" type="submit" class="button" value=">">
            </div>
        </form>
        
        <div id="chat_feed"></div>
    </div>

<?php include 'includes/footer.php' ?>