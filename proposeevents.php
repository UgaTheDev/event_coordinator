<?php
session_start();
include 'config.php';
//sets timezone to local HK timezone, so current datetime is appropriate
date_default_timezone_set('Asia/Singapore');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = isset($_POST['event_name']) ? $_POST['event_name'] : '';
    $event_date = isset($_POST['event_date']) ? $_POST['event_date'] : '';
    $event_details = isset($_POST['event_details']) ? $_POST['event_details'] : '';
    $event_starttime = isset($_POST['event_starttime']) ? $_POST['event_starttime'] : '';
    $event_endtime = isset($_POST['event_endtime']) ? $_POST['event_endtime'] : '';
    $event_organiser = isset($_POST['event_organiser']) ? $_POST['event_organiser'] : '';
    //sets creation time to the current datetime, obtained using date() in the given format
    $creation_time = date("Y-m-d H:i:s");

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $currentusername = $_SESSION["username"];
    $recent = 0;

    $stmt = $conn->prepare("INSERT INTO events (event_date, event_name, event_details, event_starttime, event_endtime, event_organiser, creation_time, recent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sssssssi", $event_date, $event_name, $event_details, $event_starttime, $event_endtime, $currentusername, $creation_time, $recent);
    if ($stmt->execute()) {
        echo "Event proposed successfully. Thank you!";
        echo '<script>
                setTimeout(function() {
                    window.location.href = "home.php";
                }, 1000); // 1 second
              </script>';
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>