<?php
session_start();
include("db_connect.php");


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if (!isset($_GET['call_id'])) {
    die("Invalid Collaboration Call.");
}

$call_id = $_GET['call_id'];
$applicant_id = $_SESSION['user_id'];

// Check if already applied
$check = mysqli_query($conn, "SELECT * FROM collaboration_applications
WHERE call_id='$call_id' AND applicant_id='$applicant_id'");

if(mysqli_num_rows($check) > 0){
    die("You have already applied for this collaboration call.");
}

if(isset($_POST['apply'])){

    $motivation = mysqli_real_escape_string($conn,$_POST['motivation']);

    $sql = "INSERT INTO collaboration_applications
    (call_id, applicant_id, motivation)

    VALUES

    ('$call_id','$applicant_id','$motivation')";

    if(mysqli_query($conn,$sql)){
        $message = "Application submitted successfully!";
    }
    else{
        $message = mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Apply for Collaboration</title>


<style>

body{
font-family:Arial;
background:#f4f4f4;
}

.container{
width:600px;
margin:40px auto;
background:white;
padding:20px;
border-radius:10px;
}

textarea{
width:100%;
height:180px;
padding:10px;
}

input[type=submit]{
background:green;
color:white;
border:none;
padding:10px 20px;
cursor:pointer;
margin-top:15px;
}

</style>

</head>

<body>



<div class="container">

<h2>Apply for Collaboration</h2>

<?php

if($message!="")
{
echo "<h3 style='color:green;'>$message</h3>";
}

?>

<form method="POST">

<label>Why should you be selected?</label>

<textarea name="motivation" required></textarea>

<br>

<input
type="submit"
name="apply"
value="Submit Application">

</form>

<br>

<a href="view_calls.php">← Back</a>

</div>



</body>
</html>