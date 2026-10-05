<?php
session_start();
include("db_connect.php");
include("navbar.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("No application selected.");
}

$id = intval($_GET['id']);

// Update the application status
$sql = "UPDATE collaboration_applications
        SET status='Shortlisted'
        WHERE application_id=$id";

if (mysqli_query($conn, $sql)) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
} else {
    echo "Database Error: " . mysqli_error($conn);
}
?>