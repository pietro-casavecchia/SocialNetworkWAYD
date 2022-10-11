<?php
require 'includes/db-connection.php';
require 'includes/session.php';
require 'includes/functions.php';

// require login for access the page
require_login($logged_in);

$user_name = $_SESSION['user_name'];

// stop doing the activity if press the button idle and exit from room if it is into it 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('idle_activity', $_POST)) {

    // before exit from room then change the activity with idle logically first the inner layer
    // if the user is in a room make it exit from it when be in idle
    if (isset($_SESSION['participant_room_id'])) {
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
    }

    // update the activity status to null 
    $sql_idle = "UPDATE members
                SET activity = NULL
                WHERE name = :user_name;";
    pdo($pdo, $sql_idle, [$user_name]);

    // put in session null activity 
    put_in_session('user_activity', NULL);
    // make the valid activity pass equal to false else will remain 
    put_in_session('valid_activity', false);
}

// if valid activity var not yet defined is false 
if (isset($_SESSION['valid_activity']) == false) {
    $valid_activity = false;
} else {
    $valid_activity = $_SESSION['valid_activity'];
}

// grant the update of the actvity only if activity is valid 
if ($_SERVER['REQUEST_METHOD'] == 'POST' 
    && $valid_activity == TRUE 
    && isset($_SESSION['participant_room_id']) == FALSE) {
        
    // get the activity form this page input
    $activity = $_POST['activity'];

    // erase the end space if there is 
    if (substr($activity, -1) == ' ') {
        $activity = substr($activity, 0, -1);
    }
    // replace ' ' with _
    $activity = preg_replace('/\s+/', '_', $activity);
    
    $sql = "UPDATE members
        SET activity = :activity
        WHERE name = :user_name;";

    pdo($pdo, $sql, [$activity, $user_name]);
    // add the activity to the session 
    put_in_session('user_activity', $activity);
    // redirect to statistics 
    header('Location: statistics.php');  
    
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_activity == false) {
    // avoid resubmission error by redirecting to this page 
    header('Location: home.php');
}
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include 'includes/head.php' ?>
        
        <script>
            // call it when typing or when click the first time to show all the options without an input
            function live_search(input_search) {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        if (this.responseText != 0) {
                            document.getElementById("activities").innerHTML = this.responseText;
                        } 
                    }
                };
                xhttp.open("POST", "AJAX/live_search.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("input_search=" + String(input_search));
            }
            function live_search_blur() {
                document.getElementById("activities").innerHTML = '';
            }
        </script>
    </head>
    <body>
    
    <div class="header">
        <div class="header-home">
            <a href="logout.php">Log Out</a>
            <?php if (isset($_SESSION['user_activity'])) { ?>
                <a href="statistics.php">&nbsp/ Stats</a>
            <?php } ?>
            <?php if (isset($_SESSION['participant_room_id'])) { ?>
                <a href="rooms.php">&nbsp/ Rooms</a>
            <?php } ?>
        </div>
        <div class="profile">
            <a href="profile.php"><?= substr($_SESSION['user_name'], 0, 1) ?></a>
        </div>
    </div>

    <div class="info-home">
        <?php if (isset($_SESSION['user_activity'])) { ?>
            <div>Your activity is: <?= $_SESSION['user_activity'] ?><div/>
            <form action="home.php" method="POST">
                <button class="idle" name="idle_activity" value="idle">Idle</button>
            </form>
        <?php } ?>
    </div>

    <div class="search-home">
        <div class="form-div">
            <form action="home.php" method="POST">
                <label for="activity">Search what you are doing ...</label>
                <input type="text" name="activity" autocomplete="off"
                        onfocus="live_search(this.value)"
                        onkeyup="live_search(this.value)"
                        onblur="live_search_blur()"
                        required>
            </form>
        </div>
        <div id="activities"></div>
    </div>

<?php include 'includes/footer.php' ?>