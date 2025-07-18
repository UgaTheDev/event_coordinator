<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include "config.php";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $username = $_SESSION["username"];
    $newPassword = $_POST["password"];
    $newPassword = $conn->real_escape_string($newPassword);
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password = '$hashedPassword' WHERE username = '$username'";
    if ($conn->query($sql) === TRUE) {
        echo "Password updated successfully!";
    } else {
        echo "Error updating password: " . $conn->error;
    }
    $conn->close();
}
?>