<?php
require '../includes/db-connection.php';
require '../includes/session.php';
require '../includes/functions.php';

// get the user activity and user name
$user_activity = $_SESSION['user_activity'];
$user_name = $_SESSION['user_name'];

// only if not in any other room can create one or join another 
// check if the user is in a room 
$sql_check_room_participants = "SELECT *
                                FROM participants
                                WHERE participant = :user_name;";

$statement = pdo($pdo, $sql_check_room_participants, [$user_name]);
$user_in_room = $statement->fetch() ? 'taken' : 'free';
// put in session for permit to access a room or create one in the main file
put_in_session('user_in_room', $user_in_room);

// get the info of the participant
if ($user_in_room == 'taken') {
    $sql_participant = "SELECT id, room_id, participant
                        FROM participants
                        WHERE participant = :$user_name;";
    $statement = pdo($pdo, $sql_participant, [$user_name]);
    $participant = $statement->fetch();
    // take data 
    $participant_id = $participant['id'];
    $participant_room_id = $participant['room_id'];
    $participant_name = $participant['participant'];

    // add the participant room id to the sessiion 
    put_in_session('participant_room_id', $participant_room_id);
}

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

// get the room swap position from the POST trought AJAX, induced by the swipe buttons 
// start form zero 
$swipe_direction = 0;
if (isset($_POST['swipe_direction'])) {
    $swipe_direction_str = $_POST['swipe_direction']; 
    if ($swipe_direction_str == '>') {
        $swipe_direction = 1;
    } else if ($swipe_direction_str == '<') {
        $swipe_direction = -1;
    }
}

$room_card_position = $_SESSION['room_card_pos'];
// make the new room_card_position 
$room_card_position = $room_card_position + $swipe_direction;
$offset_room =  $room_card_position -1;
// update the session 
put_in_session('room_card_pos', $room_card_position);

// feed of the rooms 
// starting form the first existing in order asc 
// select all the room aside the one of the user 
// if the participant is not in any room just get all the room of the same activity else remove the one it is into 
if ($user_in_room == 'taken') {
    $sql_rooms_feed = "SELECT id, max_participants, participants, description
                        FROM rooms
                        WHERE activity_room = :user_activity 
                            AND NOT id = :participant_room_id 
                        ORDER BY id ASC
                        LIMIT 1 OFFSET ".$offset_room.";";
    $statement = pdo($pdo, $sql_rooms_feed, [$user_activity, $participant_room_id]);
    $room = $statement->fetch();

    // count the number of rooms of the same activity without the one of the user 
    $sql_rooms_number = "SELECT * 
    FROM rooms
    WHERE activity_room = :user_activity 
        AND NOT id = :participant_room_id;";
    $statement = pdo($pdo, $sql_rooms_number, [$user_activity, $participant_room_id]);
    $rooms_total = count($statement->fetchAll());
} else {
    $sql_rooms_feed = "SELECT id, max_participants, participants, description
                        FROM rooms
                        WHERE activity_room = :user_activity 
                        ORDER BY id ASC
                        LIMIT 1 OFFSET ".$offset_room.";";
    $statement = pdo($pdo, $sql_rooms_feed, [$user_activity]);
    $room = $statement->fetch();

    $sql_rooms_number = "SELECT * 
                        FROM rooms
                        WHERE activity_room = :user_activity;";
    $statement = pdo($pdo, $sql_rooms_number, [$user_activity]);
    $rooms_total = count($statement->fetchAll());
}

// feed of the room of the user
if ($user_in_room == 'taken') {
    $sql_user_room = "SELECT id, max_participants, participants, description
                    FROM rooms
                    WHERE id = :participant_room_id;";

    $statement = pdo($pdo, $sql_user_room, [$participant_room_id]);
    $user_room = $statement->fetch();
}

// button for create room if the user is already in a room do not show it 
if ($user_in_room == 'free') {
    echo '<form action="rooms.php" method="POST" onSubmit="return live_rooms()">
            <button class="create-room" name="create_room" value="new_room">Create a <br> Room</button>
        </form>';
}

// participant room info and action 
// user Session participant room id for double check that the part. has change or exit room 
if ($user_in_room == 'free') {
    echo '<div class="user-room-description">
            You are in no room, join or create one
        </div>';
} else if ($user_in_room == 'taken') {
    echo '<div class="user-room-description"> 
            You already are in a room.
            For create another, exit your room first,
            else if you want to change it, just enter in another one
        </div>';

    // div that contains all the cards rooms 
    echo '<div class="rooms-gallery">';

    // div of the room 
    echo '<div class="room-user">';

    // print info about the user room with participants
    if ($user_room['participants'] == $user_room['max_participants']) {
        echo '<div class="room-name"> Room '.$_SESSION['participant_room_id'].'</div>';
        echo '<div class="room-participants">'.$user_room['participants'].'/'.$user_room['max_participants'].' [Full]</div>';
    } else {
        echo '<div class="room-name"> Room '.$_SESSION['participant_room_id'].'</div>';
        echo '<div class="room-participants">'.$user_room['participants'].'/'.$user_room['max_participants'].' </div>';
    }
    
    // div for the buttons 
    echo '<div class="room-buttons">';
    // add as a value the room id 
    echo '  <form action="rooms.php" method="POST">
                <div>
                    <button name="enter_room_id" value="'.$_SESSION['participant_room_id'].'">Enter</button>
                </div>
            </form>';
            // put onSubmit="return live_rooms()" becuase need to update when exit as remains in the room.php
    echo '  <form action="rooms.php" method="POST" onSubmit="return live_rooms()"> 
                <div>
                    <button name="exit_room_id" value="'.$_SESSION['participant_room_id'].'">Exit</button>
                </div>
            </form>';
    
    // close divs of room-buttons and room-user 
    echo '  </div>
        </div>';
}

// the $rooms array aready has all the rooms without the room of the user if exist 
// have the swipe buttons only if there are more then two rooms 
$multiple_rooms = FALSE;
if ($rooms_total >= 2) {
    $multiple_rooms = TRUE;
}

if ($rooms_total >= 1) {
    // if user is not taken start the div that contains all the cards room and another for scroll
    if ($user_in_room != 'taken') {
        echo '<div class="rooms-gallery">';
    }

    if ($multiple_rooms == TRUE) {
        // div that contain the buttons left right
        echo '<div class="room-and-buttons">';
    
        // create two buttons for swipe rooms left and right that exist 
        // only if at one room that is not of the user exists
        // make the left swipe button appear only if the $_SESSION['room_card_pos'] > then 1 
        if ($_SESSION['room_card_pos'] > 1) {
            echo '<div>   
                <input type="button" 
                        class="swipe-button" 
                        name="swipe-button" 
                        value="<" 
                        onclick="swipe(this.value)">
            </div>';
        } else {
            echo '<div>   
                <input type="button" 
                        class="swipe-button" 
                        name="swipe-button" 
                        value="x">
            </div>';
        }
    }

    // div of the room 
    echo '<div class="room">';

    if ($room['participants'] == $room['max_participants']) {
        echo '<div class="room-name"> Room '.$room['id'].'</div>';
        echo '<div class="room-participants">'.$room['participants'].'/'.$room['max_participants'].' [Full]</div>';
    } else {
        echo '<div class="room-name"> Room '.$room['id'].'</div>';
        echo '<div class="room-participants">'.$room['participants'].'/'.$room['max_participants'].' </div>';
    }

    // echo '<p>' . $room['description'] . '</p>';

    // div for the buttons 
    echo '<div class="room-buttons">';

    echo '  <form action="rooms.php" method="POST">
                <div>   
                    <button name="enter_room_id" value="'.$room['id'].'">Enter</button>
                </div>
            </form>';
    
    // close divs of room-buttons and room
    echo '  </div>
        </div>';
}

if ($multiple_rooms == TRUE) {
    // right button swipe
    // make the right swipe button appear only if there are more to swipe 
    // else change it to another symbol 
    if ($_SESSION['room_card_pos'] < $rooms_total) {
        echo '<div>   
            <input type="button" 
                    class="swipe-button" 
                    name="swipe-button" 
                    value=">" 
                    onclick="swipe(this.value)">
        </div>';
    } else {
        echo '<div>   
            <input type="button" 
                    class="swipe-button" 
                    name="swipe-button" 
                    value="x">
        </div>';
    }

    // close the room-and-button div
    echo '</div>';
}

// close the rooms-gallery 
echo '</div>';