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

$user_activity = $_SESSION['user_activity'];
$user_name = $_SESSION['user_name'];

// get from AJAX if the user is just in a room 
// 'free' if free else 'taken'
if (isset($_SESSION['user_in_room'])) {
    $user_in_room = $_SESSION['user_in_room'];
}

// start form the first one room in the rooms with the same activity table initialize only one time when the page load 
put_in_session('room_card_pos', 1);

// exit the room 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('exit_room_id', $_POST)) {

    $exit_room_id = $_POST['exit_room_id'];

    // delate participant from participants
    $sql_exit = "DELETE FROM participants
                WHERE participant = :user_name;";
    pdo($pdo, $sql_exit, [$user_name]);

    // decrease number of participants of the present room 
    $participant_room_id = $_SESSION['participant_room_id'];
    $sql_decrease_participant = "UPDATE rooms
                                SET participants = participants - 1
                                WHERE id = :participant_room_id;";
    pdo($pdo, $sql_decrease_participant, [$participant_room_id]);

    // make the participant room id null 
    put_in_session('participant_room_id', NULL);
    // avoid resubmission 
    header('Location: rooms.php');
}

// enter in a existing room 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('enter_room_id', $_POST)) {

    $enter_room_id = $_POST['enter_room_id'];

    // 3 type of state for access a room 

    // 1 if the user is already in a room and it is the same as the one it want to enter the chat 
    if (isset($_SESSION['participant_room_id']) && $_SESSION['participant_room_id'] == $enter_room_id) {
        put_in_session('enter_room_id', $enter_room_id);
        // direct the user to the chat 
        header('Location: chat.php');
    }

    // check how many participants there are if there are available spots then consent access else redirect to the same page
    $sql_check_number_participants = "SELECT id, max_participants, participants
                                        FROM rooms
                                        WHERE id = :enter_room_id;";
    $statement = pdo($pdo, $sql_check_number_participants, [$enter_room_id]);
    $room = $statement->fetch();

    // provide access to room if there is at least 1 availabe spot in the room that is not aready into it 
    // else can access with no limitation becuase will not increase the number 
    if ($room['participants'] < $room['max_participants']) {

        // 2 if the user is not in any room then just add and put the $_SESSION['participant_room_id'] as the room id
        if (!isset($_SESSION['participant_room_id'])) {
            // add user to participants table 
            $sql_participants = "INSERT INTO participants (room_id, participant)
                                VALUES (:enter_room_id, :user_name);";
            pdo($pdo, $sql_participants, [$enter_room_id, $user_name]);

            put_in_session('participant_room_id', $enter_room_id);
            // put in session the room id for change it form the previous room 
            put_in_session('enter_room_id', $enter_room_id);

            // increase number of participants
            $participant_room_id = $_SESSION['participant_room_id'];
            $sql_increase_participant = "UPDATE rooms
                                        SET participants = participants + 1
                                        WHERE id = :participant_room_id;";
            pdo($pdo, $sql_increase_participant, [$participant_room_id]);

            // direct the user to the chat 
            header('Location: chat.php');
        }

        // 3 if the user is already in a room and it is different form the one it want to enter the chat
        if (isset($_SESSION['participant_room_id']) && $_SESSION['participant_room_id'] != $enter_room_id) {
            // update the participant room id 
            $sql_update_participant = "UPDATE participants
                                        SET room_id = :enter_room_id
                                        WHERE participant = :user_name;";
            pdo($pdo, $sql_update_participant, [$enter_room_id, $user_name]);

            // decrease number of participants of the present room 
            $participant_room_id = $_SESSION['participant_room_id'];
            $sql_decrease_participant = "UPDATE rooms
                                        SET participants = participants - 1
                                        WHERE id = :participant_room_id;";
            pdo($pdo, $sql_decrease_participant, [$participant_room_id]);

            put_in_session('participant_room_id', $enter_room_id);
            // put in session the room id 
            put_in_session('enter_room_id', $enter_room_id);

            // increase number of participants 
            $participant_room_id = $_SESSION['participant_room_id'];
            $sql_increase_participant = "UPDATE rooms
                                        SET participants = participants + 1
                                        WHERE id = :participant_room_id;";
            pdo($pdo, $sql_increase_participant, [$participant_room_id]);

            // direct the user to the chat 
            header('Location: chat.php');
        }
    }
}

// create a new room and add the user to participant of that room 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('create_room', $_POST) && $user_in_room == 'free') {

    $first_participant = 1;

    // get room max participants from settings
    $sql_room = "INSERT INTO rooms (activity_room, max_participants, participants)
                VALUES (:user_activity, :room_max_participants, :first_participants);";

    pdo($pdo, $sql_room, [$user_activity, $room_max_participants, $first_participant]);

    // get id of newly created room
    $sql_room_id = "SELECT LAST_INSERT_ID() AS room_id
                    FROM rooms;";
    $statement = pdo($pdo, $sql_room_id);
    $room_id = $statement->fetch();
    // make the one dimensional one value array into a int 
    $room_id = $room_id['room_id'];

    // add user to participants table by adding it to the room it has created 
    $sql_participants = "INSERT INTO participants (room_id, participant)
                        VALUES (:room_id, :user_name);";
    pdo($pdo, $sql_participants, [$room_id, $user_name]);
    
    // avoid resubmission
    header('Location: rooms.php');
} 
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include 'includes/head.php' ?>
        
        <script>
            // get the feed of the rooms created by others and when submitted the form 
            function live_rooms() {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById('rooms_feed').innerHTML = this.responseText;
                    }
                };
                xhttp.open("POST", "AJAX/live_rooms.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send();
            }
            // call the funciton the first time page load 
            live_rooms();
            // call the function every n seconds
            setInterval(live_rooms, <?= $time_delay_rooms ?>);

            // swipe button function 
            function swipe(swipe_direction) {
                var xhttp = new XMLHttpRequest();
                // even here you need this code for update the feed as soon the user press se swipe
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById('rooms_feed').innerHTML = this.responseText;
                    }
                };
                xhttp.open("POST", "AJAX/live_rooms.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("swipe_direction=" + String(swipe_direction));
            }
        </script>
    </head>
    <body>

    <div class="header">
        <div class="header-home">
            <a href="home.php">Home</a>
            <a href="statistics.php">&nbsp/ Stats</a>
        </div>
        <div class="profile">
            <a href="profile.php"><?= substr($_SESSION['user_name'], 0, 1) ?></a>
        </div>
    </div>

    <div id="rooms_feed"></div>

<?php include 'includes/footer.php' ?>