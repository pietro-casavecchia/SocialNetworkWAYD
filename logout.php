<?php
require 'includes/db-connection.php';
require 'includes/session.php';
require 'includes/functions.php';

// change the status form online to offline
// make the activity null when logout
$user_name = $_SESSION['user_name'];
$sql_update_status = "UPDATE members
                    SET live_status = 'offline', activity = NULL
                    WHERE name = :user_name;";
pdo($pdo, $sql_update_status, [$user_name]);

// if the user is in a room make it exit from it when logout
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

// delete room if participants are equal or minor then 0
// delate form all the rooms not only the ones with the same activity 
$sql_rooms_delete = "SELECT id, max_participants, participants
                    FROM rooms
                    ORDER BY id DESC;";
$statement = pdo($pdo, $sql_rooms_delete);
$all_rooms = $statement->fetchAll();
foreach ($all_rooms as $room_delete) {
    if ($room_delete['participants'] <= 0) {
        $room_id = $room_delete['id'];
        $sql_delete_room = "DELETE FROM rooms
                            WHERE id = :room_id;";
        pdo($pdo, $sql_delete_room, [$room_id]);

        // delete even all the messages from that room 
        $sql_delete_messages = "DELETE FROM messages
                                WHERE room_id = :room_id;";
        pdo($pdo, $sql_delete_messages, [$room_id]);
    }
}

// end session and redirect to index page
logout();
header('Location: index.php');      
