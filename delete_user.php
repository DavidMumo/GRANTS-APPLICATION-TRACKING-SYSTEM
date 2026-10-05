<?php
session_start();
include("db_connect.php");

// Allow only administrators
if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != "Administrator")
{
    die("Access Denied!");
}

// Check if ID is provided
if(!isset($_GET['id']))
{
    die("No user selected.");
}

$id = intval($_GET['id']);

// Prevent administrator from deleting their own account
if($id == $_SESSION['user_id'])
{
    echo "<script>
            alert('You cannot delete your own administrator account.');
            window.location='manage_users.php';
          </script>";
    exit();
}

// Check if the user exists
$check = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$id'");

if(mysqli_num_rows($check) == 0)
{
    die("User not found.");
}

// Delete the user
$sql = "DELETE FROM users WHERE user_id='$id'";

if(mysqli_query($conn, $sql))
{
    echo "<script>
            alert('User deleted successfully.');
            window.location='manage_users.php';
          </script>";
}
else
{
    echo "Error: " . mysqli_error($conn);
}
?>