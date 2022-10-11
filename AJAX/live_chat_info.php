<?php
require '../includes/db-connection.php';
require '../includes/session.php';
require '../includes/functions.php';

// get participant room id 
$room_id = $_SESSION['participant_room_id'];

// get room information like how many participants activity is just passed with when the user entered 
$sql_room_info = "SELECT max_participants, participants, description
                FROM rooms
                WHERE id = :room_id;";
$statement = pdo($pdo, $sql_room_info, [$room_id]);
$room_info = $statement->fetch();

/*
if (isset($room_info['description'])) {
    echo '<p>'.'Description: '.$room_info['description'].'</p>'; 
}
*/

echo '<div class="chat-participants">
        <div>
            Participants:
        </div>
        <div> ' 
            .$room_info['participants'].'/'.$room_info['max_participants']. 
        '</div>
    </div>';