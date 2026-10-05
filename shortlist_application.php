<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Administrator")
{
    die("Access Denied!");
}

$id = intval($_GET['id']);

mysqli_query($conn,"
UPDATE collaboration_applications
SET status='Shortlisted'
WHERE application_id='$id'
");

header("Location: manage_collaboration_applications.php");
exit();
?>