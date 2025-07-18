
<?php
session_start();

require __DIR__ . "/vendor/autoload.php"; 

$client = new Google\Client();
$client->setClientId("715216700104-9215hic2m4d9eg12c6std8f52ddhpi4a.apps.googleusercontent.com");
$client->setClientSecret("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");
$client->setRedirectUri("http://localhost/comsci_ia/redirect.php");

if (!isset($_GET["code"])) {
    exit("Login failed!");
    header("Location: loginhtml.html");
}

//get an access token using the authorisation code
$token = $client->fetchAccessTokenWithAuthCode($_GET["code"]);
$client->setAccessToken($token['access_token']);
//create google oauth service instance
$oauth = new Google\Service\Oauth2($client);
$userinfo = $oauth->userinfo->get();

$email = $userinfo->email;

include "config.php";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//finding the relevant user information depending on the email used to sign in 
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    //if the user exists
    $row = $result->fetch_assoc();
    $_SESSION["username"] = $row["username"];
    header("Location: home.php");
    exit();
} else {
    echo "No account associated with this email.";
    header("Location: registration.html");
exit();
}

$conn->close();
?>