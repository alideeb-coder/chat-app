<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../config/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
