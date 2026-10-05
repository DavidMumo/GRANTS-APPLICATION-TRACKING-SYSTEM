<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "grants_application_system";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Database connected successfully!";
?>