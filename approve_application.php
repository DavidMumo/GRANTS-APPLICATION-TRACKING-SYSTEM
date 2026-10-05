<?php
session_start();
include("db_connect.php");

if($_SESSION['role']!="Administrator")
{
die("Access Denied");
}

$id=$_GET['id'];

mysqli_query($conn,"
UPDATE grant_applications
SET status='Approved'
WHERE application_id='$id'
");

header("Location: manage_grant_applications.php");
exit();
?>