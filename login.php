<?php
session_start();

if (isset($_SESSION["username"])) {
    header("Location: home.php");
    exit();
}

include "config.php";
$fusername = $_POST["txt_uname"];
$fpassword = $_POST["txt_pwd"];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
    
$sql = "SELECT * FROM users WHERE username = '$fusername'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $storedPassword = $row["password"];
    
    if (password_verify($fpassword, $storedPassword)) {
        $_SESSION["username"] = $fusername; 
        header("Location: home.php");
        exit();
    }
}
//error message and redirect back to home.php
echo "Invalid username or password.";
echo '<script>
        setTimeout(function() {
            window.location.href = "home.php";
        }, 1000); // 1 second
    </script>';
?>