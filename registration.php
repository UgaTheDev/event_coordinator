<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include "config.php";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_POST["txt_uname"];
    $email = $_POST["txt_email"];
    $password = $_POST["txt_pwd"];
    $confirmPassword = $_POST["txt_confirm_pwd"];

    //unique username check
    $check_username_unique_sql = "SELECT username FROM users WHERE username = ?";
    $check_username = $conn->prepare($check_username_unique_sql);
    $check_username->bind_param("s", $username);
    $check_username->execute();
    $check_username_result = $check_username->get_result();

    if ($check_username_result->num_rows > 0) {
        echo "<script>alert('Username already exists.'); window.location.href = 'registration.html';</script>";
        exit();
    }

    //unique email check
    $checkEmailQuery = "SELECT email FROM users WHERE email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailQuery);
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailResult = $checkEmailStmt->get_result();

    if ($checkEmailResult->num_rows > 0) {
        echo "<script>alert('Email already exists.'); window.location.href = 'registration.html';</script>";
        exit();
    }

    //checks if passwords match
    if ($password !== $confirmPassword) {
        echo "<script>alert('Password and Confirm Password do not match.'); window.location.href = 'registration.html';</script>";
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insertQuery = "INSERT INTO users (username, password, email, registered_events, available, registered_events_dates) VALUES (?, ?, ?, '[]', '[]', '[]')";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("sss", $username, $hashedPassword, $email);

    if ($insertStmt->execute()) {
        echo "Registration successful!";
        echo "<form action='loginhtml.html' method='post'>";
        echo "<input type='submit' value='Proceed to Login' class='view-button'>";
        echo "</form>";
    } else {
        echo "Error: " . $insertStmt->error;
    }

    $insertStmt->close();
    $conn->close();
}
?>