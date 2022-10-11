<?php
require '../includes/db-connection.php';
require '../includes/session.php';
require '../includes/functions.php';

// get the user activity 
$user_activity = $_SESSION['user_activity'];

// delete room if participants are equal or minor then 0
// delate from all the rooms not only the ones with the same activity 
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

// count how many are register
$sql_register = "SELECT * FROM members;";
$statement_register = pdo($pdo, $sql_register);
$members_registered = $statement_register->fetchAll();
$number_members = count($members_registered);

// count how many are active 
$sql_live_status = "SELECT * 
                    FROM members 
                    WHERE live_status = 'online';";
$statement_live_status = pdo($pdo, $sql_live_status);
$users_live_status = $statement_live_status->fetchAll();
$number_live_status = count($users_live_status);

// count the total numbers of rooms 
$sql_rooms_number = "SELECT * 
                    FROM rooms;";
$statement = pdo($pdo, $sql_rooms_number);
$rooms_total = count($statement->fetchAll());

// count how many are active and doing the user selected activity
// the activity is set to NULL when logout thus can just count how many doing the activity without check if they are active
$sql_doing_activity = "SELECT * 
                        FROM members 
                        WHERE live_status = 'online' AND activity = :user_activity;";
$statement_doing_activity = pdo($pdo, $sql_doing_activity, [$user_activity]);
$users_doing_activity = $statement_doing_activity->fetchAll();
$number_doing_activity = count($users_doing_activity);

// count the total numbers of rooms of the same activity of the user
$sql_rooms_number_activity = "SELECT * 
                            FROM rooms
                            WHERE activity_room = :user_activity;";
$statement = pdo($pdo, $sql_rooms_number_activity, [$user_activity]);
$rooms_activity = count($statement->fetchAll());

echo '<div class="stat-card">
        <div class="stat-name">
            Number of members:
        </div>
        <div class="stat-numb"> ' 
            . $number_members . 
        '</div>
    </div>';
echo '<div class="stat-card">
        <div class="stat-name">
            Active users:
        </div> 
        <div class="stat-numb">' 
            . $number_live_status . 
        '</div>
    </div>';
echo '<div class="stat-card">
        <div class="stat-name">
            Total Rooms:
        </div>
        <div class="stat-numb">' 
            . $rooms_total . 
        '</div>
    </div>';
echo '<div class="stat-card">
        <div class="stat-name">
            Active users doing '. $user_activity . ':  
        </div>
        <div class="stat-numb">' 
            . $number_doing_activity .
        '</div>
    </div>';
echo '<div class="stat-card">
        <div class="stat-name">
            Rooms in the activity ' . $user_activity . ': 
        </div>
        <div class="stat-numb">'
            . $rooms_activity . 
        '</div>
    </div>';