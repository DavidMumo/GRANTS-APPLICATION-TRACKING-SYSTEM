<?php
session_start();


error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("db_connect.php");
include("db_connect.php");

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != "Administrator")
{
    die("Access Denied!");
}

if(!isset($_GET['id']))
{
    die("Invalid Grant.");
}

$grant_id = $_GET['id'];

// Check if the grant exists
$check = mysqli_query($conn, "SELECT * FROM grants WHERE grant_id='$grant_id'");

if(mysqli_num_rows($check) == 0)
{
    die("Grant not found.");
}

// Delete the grant
$sql = "DELETE FROM grants WHERE grant_id='$grant_id'";

if(mysqli_query($conn, $sql))
{
    header("Location: manage_grants.php?success=deleted");
    exit();
}
else
{
    echo "Database Error: " . mysqli_error($conn);
}
?>