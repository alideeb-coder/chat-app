<?php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; img-src 'self' data: https:; connect-src 'self'; frame-src 'self';");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Chat App' ?></title>

    <link href="./assets/css/output.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="<?= $bodyClass ?? 'min-h-screen w-full' ?>"
    style="background:linear-gradient(120deg,#6464e3,#cb0e94a6);">