<?php
require '../includes/db-connection.php';
require '../includes/session.php';
require '../includes/functions.php';

// get the user activity 
$user_activity = $_SESSION['user_activity'];
// get participant room id 
$room_id = $_SESSION['participant_room_id'];

// get the message limitied by the room id 
$sql_messages = "SELECT name, time, message
                FROM messages
                WHERE room_id = :room_id
                ORDER BY id DESC;";
$statement = pdo($pdo, $sql_messages, [$room_id]);
$messages = $statement->fetchAll();

// get room information like how many participants activity is just passed with when the user entered 
$sql_room_info = "SELECT max_participants, participants, description
                FROM rooms
                WHERE id = :room_id;";
$statement = pdo($pdo, $sql_room_info, [$room_id]);
$room_info = $statement->fetch();
// put the info into session to display in the main page 
put_in_session('max_participants', $room_info['max_participants']);
put_in_session('participants', $room_info['participants']);
put_in_session('description', $room_info['description']);

foreach ($messages as $message) {
    echo '<div class="chat-media">
            <div class="chat-user-meta">     
                <div class="chat-username">'.$message['name'].'</div>
                &nbsp
                <div class="chat-user-time"> @ '.$message['time'].' gmt</div>
            </div>
            <div class="chat-message">'.$message['message'].'</div>
        </div>';
}