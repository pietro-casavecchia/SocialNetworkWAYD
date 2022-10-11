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

// use AJAX for get live data
// time delay for getting data form the settings file 
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include 'includes/head.php' ?>
        
        <script>
            function live_statistics() {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById('live_statistics').innerHTML = this.responseText;
                    }
                };
                xhttp.open("POST", "AJAX/live_statistics.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send();
            }
            // call the funciton the first time page load 
            live_statistics();
            // call the function every n seconds
            setInterval(live_statistics, <?= $time_delay_stats ?>);
        </script>
    </head>
    <body>

        <div class="header">
            <div class="header-home">
                <a href="home.php">Home</a>
            </div>
            <div class="profile">
                <a href="profile.php"><?= substr($_SESSION['user_name'], 0, 1) ?></a>
            </div>
        </div>

        <div id="live_statistics"></div>

        <div class="rooms-stats">
            <a href="rooms.php">Rooms</a>
        </div>

<?php include 'includes/footer.php' ?>