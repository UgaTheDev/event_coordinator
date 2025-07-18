<?php
require __DIR__ . "/vendor/autoload.php";

$client = new Google\Client();
$client->setClientId("715216700104-9215hic2m4d9eg12c6std8f52ddhpi4a.apps.googleusercontent.com"); //client id generated from google cloud console
$client->setClientSecret("C4EuJ9MIO2q5NEl9gGdw0vEyzriS");  //client secret generated from google cloud console
$client->setRedirectUri("http://localhost/comsci_ia/redirect.php"); //redirect to redirect.php for login
$client->addScope("email");
$client->addScope("profile");
$url = $client->createAuthUrl();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <title>Google Login</title>
</head>
<body>
    <a href="<?= htmlspecialchars($url) ?>" class="google-btn"> Sign in with Google </a>
</body>
</html>