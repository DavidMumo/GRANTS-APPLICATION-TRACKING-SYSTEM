<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != "Administrator") {
    die("Access Denied!");
}

if (!isset($_GET['id'])) {
    die("Invalid Collaboration Call.");
}

$call_id = intval($_GET['id']);

// Check if the collaboration call exists
$check = mysqli_query($conn, "SELECT * FROM collaboration_calls WHERE call_id='$call_id'");

if (mysqli_num_rows($check) == 0) {
    die("Collaboration Call not found.");
}

// First delete all applications related to this call
mysqli_query($conn, "DELETE FROM collaboration_applications WHERE call_id='$call_id'");

// Then delete the collaboration call
$sql = "DELETE FROM collaboration_calls WHERE call_id='$call_id'";

if (mysqli_query($conn, $sql)) {
    header("Location: manage_calls.php?success=deleted");
    exit();
} else {
    echo "Database Error: " . mysqli_error($conn);
}
?>