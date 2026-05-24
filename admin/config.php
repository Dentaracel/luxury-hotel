<?php
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'hotel_booking_db';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

session_start();

function requireLogin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
