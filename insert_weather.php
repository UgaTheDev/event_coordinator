<?php
include 'config.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//error message if appropriate parameters have not been received
if (!isset($_POST['date']) || !isset($_POST['mintemp']) || !isset($_POST['maxtemp'])) {
    die("Missing required parameters.");
}

$date = $_POST['date']; 
$mintemp = $_POST['mintemp']; 
$maxtemp = $_POST['maxtemp']; 
//null coalescing operator to avoid undefined or null errors
$weather_description = $_POST['weather'] ?? ''; 
echo $weather_description;

$rain = (strpos($weather_description, "rain patches") !== false) ? 1 : 0;
$cold = ($mintemp <= 16) ? 1 : 0;
$hot = ($maxtemp >= 27) ? 1 : 0;

function rain_insert($conn, $date, $rain) {
    $rain_sql_query = $conn->prepare("INSERT INTO weather_info (date, rain) VALUES (?, ?) ON DUPLICATE KEY UPDATE rain = $rain");
    $rain_sql_query->bind_param("si", $date, $rain);
    if ($rain_sql_query->execute()) {
        echo "Database updated successfully.";
    } else {
        echo "Error: " . $rain_sql_query->error;
    }
    $rain_sql_query->close();
}

function cold_insert($conn, $date, $cold) {
    $cold_sql_query = $conn->prepare("INSERT INTO weather_info (date, cold) VALUES (?, ?) ON DUPLICATE KEY UPDATE cold = $cold");
    $cold_sql_query->bind_param("si", $date, $cold);
    if ($cold_sql_query->execute()) {
        echo "Database updated successfully.";
    } else {
        echo "Error: " . $cold_sql_query->error;
    }
    $cold_sql_query->close();
}

function hot_insert($conn, $date, $hot) {
    $hot_sql_query = $conn->prepare("INSERT INTO weather_info (date, hot) VALUES (?, ?) ON DUPLICATE KEY UPDATE hot = $hot");
    $hot_sql_query->bind_param("si", $date, $hot);
    if ($hot_sql_query->execute()) {
        echo "Database updated successfully.";
    } else {
        echo "Error: " . $hot_sql_query->error;
    }
    $hot_sql_query->close();
}
rain_insert($conn, $date, $rain);
cold_insert($conn, $date, $cold);
hot_insert($conn, $date, $hot);
$conn->close();
?>