<?php
require_once 'includes/session.php';
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId(getenv('GOOGLE_CLIENT_ID'));
$client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
$client->setRedirectUri('https://chat-app-vroj.onrender.com/callback-google.php');
$client->addScope('email');
$client->addScope('profile');

$authUrl = $client->createAuthUrl();

header('Location: ' . $authUrl);
exit;
