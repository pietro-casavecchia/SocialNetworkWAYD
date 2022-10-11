<?php
require '../includes/db-connection.php';
require '../includes/session.php';
require '../includes/functions.php';

// the frequency number is updated or at the first or at every new char or at blur 
// in this way is updated in interval and not continusly that with high user volume would make the suggestions unreadable

// sorting form most frequency and echo print
function sort_frequency($array) {
    // sort associative array
    arsort($array);
    
    foreach ($array as $activity => $freq) {
        echo '<div class="activities-list">'.$freq.' :: '.$activity.'</div>';
    }
}

// print all the matching actions or actions object (it is called everytime there is an input)
// count how many are using that word for their activity count only one time for avoid infinite word activity
// for every suggestion check how many times appear into all the activites in the members actvity
function get_matching($activities) {
    // get global variable 
    global $pdo;
    // get global trought session 
    $column = $_SESSION['column'];

    foreach ($activities as $activity) {
        // get all the activities of the members
        $sql_members_activities = "SELECT activity
                                    FROM members;";
        $statement = pdo($pdo, $sql_members_activities);
        $members_activities = $statement->fetchAll();

        // loop trought every activity and count how many times match 
        $match_count = 0;
        foreach ($members_activities as $member_activity) {
            // make the member activity that is in the format abc or abc_def... as an array of elements 
            $activity_array = explode('_', $member_activity['activity']);
            // loop trought all the elements of the array
            foreach ($activity_array as $word) {
                // if the activity is inside the array of words then exit and count one 
                if ($activity[$column] == $word) {
                    $match_count += 1;
                    break;
                }
            }
        }
        // create associative array 
        $sorted_activities[$activity[$column]] = $match_count;
    }
    // print the the sorted array 
    if (isset($sorted_activities) && $sorted_activities != NULL) {
        sort_frequency($sorted_activities);
        // clear for avoid to bad memory usage 
        $sorted_activities = array(); 
    }
}

// print all actions and objects (it is called only when there is go deeper and print all like when there is no input)
function get_actions_objects() {
    // get global variable 
    global $pdo;

    // get all the actions objects 
    $sql_all_actions_objects = "SELECT action_object
                                FROM actions_objects;";

    $statement = pdo($pdo, $sql_all_actions_objects);
    $sql_all_actions_objects = $statement->fetchAll();

    // count their frequency 
    foreach ($sql_all_actions_objects as $action_object) {
        // get all the activities of the members
        $sql_members_activities = "SELECT activity
                                    FROM members;";
        $statement = pdo($pdo, $sql_members_activities);
        $members_activities = $statement->fetchAll();

        // loop trought every activity and count how many times match 
        $match_count = 0;
        foreach ($members_activities as $member_activity) {
            // make the member activity that is in the format abc or abc_def... as an array of elements 
            $activity_array = explode('_', $member_activity['activity']);
            // loop trought all the elements of the array
            foreach ($activity_array as $word) {
                // if the activity is inside the array of words then exit and count one 
                if ($action_object['action_object'] == $word) {
                    $match_count += 1;
                    break;
                }
            }
        }
        // create associative array 
        $sorted_activities[$action_object['action_object']] = $match_count;
    }
    // print the the sorted array 
    if (isset($sorted_activities) && $sorted_activities != NULL) {
        sort_frequency($sorted_activities);
        // clear for avoid to bad memory usage 
        $sorted_activities = array(); 
    }
}

// print only actions
// if no input print all the possibile activities that at the first will be only the actions with their frequency
if ($_POST['input_search'] == '') {
    // define them here then are change when add a new word after a space 
    $table = 'actions';
    $column = 'action';
    // put in session for make global access
    put_in_session('table', $table);
    put_in_session('column', $column);
    
    // get all the verbs as it is the first blank search
    $sql_all_actions = "SELECT action
                        FROM actions;";

    $statement = pdo($pdo, $sql_all_actions);
    $all_actions = $statement->fetchAll();

    // count their frequency 
    $sorted_activities;
    foreach ($all_actions as $action) {
        // get all the activities of the members
        $sql_members_activities = "SELECT activity
                                    FROM members;";
        $statement = pdo($pdo, $sql_members_activities);
        $members_activities = $statement->fetchAll();

        // loop trought every activity and count how many times match 
        $match_count = 0;
        foreach ($members_activities as $member_activity) {
            // make the member activity that is in the format abc or abc_def... as an array of elements 
            $activity_array = explode('_', $member_activity['activity']);
            // loop trought all the elements of the array
            foreach ($activity_array as $word) {
                // if the activity is inside the array of words then exit and count one 
                if ($action[$column] == $word) {
                    $match_count += 1;
                    break;
                }
            }
        }
        // create associative array 
        $sorted_activities[$action[$column]] = $match_count;
    }
    // alert the user that is already in a room and get info 
    if (isset($_SESSION['participant_room_id'])) {
        $room_id = $_SESSION['participant_room_id'];
        // get the info of the room 
        $sql_room_info = "SELECT id, activity_room, max_participants, participants 
                    FROM rooms
                    WHERE id = :room_id;";
        $statement = pdo($pdo, $sql_room_info, [$room_id]);
        $user_room = $statement->fetch();

        echo '<div class="message-search"> You are in the Room '.$user_room['id'].' of activity: '.$user_room['activity_room'].'</div>';
        echo '<div class="message-search"> Before selecting an activity, exit from your room or idle </div>';
    }

    // print the the sorted array 
    if (isset($sorted_activities) && $sorted_activities != NULL) {
        sort_frequency($sorted_activities);
        // clear for avoid to bad memory usage 
        $sorted_activities = array(); 
    }

} else {
    // start when there is an input 
    $input_search = $_POST['input_search'];
    // save a version of the input search that is not modify
    $raw_input_search = $input_search;

    // make prev input length with first = 0
    if (strpos($raw_input_search, ' ') == false) { 
        put_in_session('prev_input_length', 0);
        // initialize the prev as true given that do not exist 
        $_SESSION['activity_match_prev'] = true;
    } 

    // when there is a char space char then reset then erase the previews input search every time 
    $last_char = substr($raw_input_search, -3, 1);
    $white_space = substr($raw_input_search, -2, 1);
    $first_char = substr($raw_input_search, -1);

    // save in session the input length when there is the space
    if ($last_char != ' ' && $white_space == ' ' && $first_char != ' ') {
        $table = 'actions_objects';
        $column = 'action_object';
        // put in session for make global access
        put_in_session('table', $table);
        put_in_session('column', $column);

        // get the input length minus one that in the new char 
        $prev_input_length = strlen($input_search) - 1;
        put_in_session('prev_input_length', $prev_input_length);
        // activity match swap
        $_SESSION['activity_match_prev'] = $_SESSION['activity_match'];
    } 

    // find if input search match 
    // enter if there is no white space before the cut means that it is the first 
    // else the previeus must exist and be correct for execute the search 
    if (strpos($raw_input_search, ' ') == false
        || (isset($_SESSION['activity_match_prev']) && $_SESSION['activity_match_prev'] == true)) {

        // erase every time if is not the first 
        if (strpos($raw_input_search, ' ') == true) { 
            $input_search = substr($input_search, $_SESSION['prev_input_length']); 
        } 

        $table = $_SESSION['table'];
        $column = $_SESSION['column'];
        $sql_match = "SELECT "."$column"."
                        FROM "."$table"."
                        WHERE "."$column"." = :inputSearch";

        $statement = pdo($pdo, $sql_match, [$input_search]);
        $activity_match = $statement->fetch();
        // add to the session for keep track of it 
        put_in_session('activity_match', $activity_match);
    }

    // grant the access to submit only then activity match and the prev are both true 
    if ($_SESSION['activity_match'] == true && $_SESSION['activity_match_prev'] == true) {
        put_in_session('valid_activity', true);
    } else {
        put_in_session('valid_activity', false);
    }

    // if the user is in a room keep the valid activity true for permit the search but 
    // block the submission by check if the main php if the user is in a room as before need to exit form that room 
    if (isset($_SESSION['participant_room_id'])) {
        $room_id = $_SESSION['participant_room_id'];
        // get the info of the room 
        $sql_room_info = "SELECT id, activity_room, max_participants, participants 
                    FROM rooms
                    WHERE id = :room_id;";
        $statement = pdo($pdo, $sql_room_info, [$room_id]);
        $user_room = $statement->fetch();
    }

    // printing suggestions
    // get the matching activities
    $table = $_SESSION['table'];
    $column = $_SESSION['column'];
    $sql_suggestions = "SELECT "."$column"."
                        FROM "."$table"."
                        WHERE "."$column"." LIKE CONCAT(:input_search, '%');";

    $statement = pdo($pdo, $sql_suggestions, [$input_search]);
    $activities = $statement->fetchAll();

    // message if the user is already in a room 
    // use classess for styling 
    if (isset($_SESSION['participant_room_id'])) {
        echo '<div class="message-search"> You are in the Room '.$user_room['id'].' of activity: '.$user_room['activity_room'].'</div>';
        echo '<div class="message-search"> Before selecting an activity, exit from your room or idle </div>';
    }
    // if the input is char space char still show all the options but not all the other things
    if (isset($_SESSION['participant_room_id']) == TRUE && 
        $_SESSION['valid_activity'] == true && $first_char == ' ') {
            get_actions_objects();
    }

    if (isset($_SESSION['participant_room_id']) == FALSE) {
        if ($_SESSION['valid_activity'] == true && $first_char == ' ') {
            echo '<div class="message-search"> Valid activity but go deeper ... </div>';
            // print all the actions and objects with their frequency by calling the function
            get_actions_objects();

        } else if ($_SESSION['valid_activity'] == true) {
            echo '<div class="message-search"> Valid activity </div>';
        } else if ($_SESSION['valid_activity'] == false) {
            echo '<div class="message-search"> Not valid activity </div>';
        }
    }

    // print the matching 
    get_matching($activities);

}