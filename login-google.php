<?php
require_once 'includes/session.php';
require_once 'vendor/autoload.php';

$client = new Google_Client();
$config = require 'config/google.php';
$client->setClientId($config['google_client_id']);
$client->setClientSecret($config['google_client_secret']);
$client->setRedirectUri('https://chat-app-vroj.onrender.com/callback-google.php');
$client->addScope('email');
$client->addScope('profile');

$authUrl = $client->createAuthUrl();

header('Location: ' . $authUrl);
exit;
