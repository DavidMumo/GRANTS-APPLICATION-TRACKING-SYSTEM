<?php
session_start();
include("db_connect.php");


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if (!isset($_GET['grant_id'])) {
    die("No grant selected.");
}

$grant_id = $_GET['grant_id'];

if(isset($_POST['apply']))
{
    $user_id = $_SESSION['user_id'];

    $proposal = $_FILES['proposal']['name'];
    $temp = $_FILES['proposal']['tmp_name'];

    move_uploaded_file($temp,"uploads/".$proposal);

    $date = date("Y-m-d");

    $sql = "INSERT INTO grant_applications
    (user_id,grant_id,proposal_file,application_date,status)

    VALUES

    ('$user_id',
    '$grant_id',
    '$proposal',
    '$date',
    'Pending')";

    if(mysqli_query($conn,$sql))
    {
        $message="Application Submitted Successfully!";
    }
    else
    {
        $message="Application Failed!";
    }
}

// Check if grant ID exists
if (!isset($_GET['grant_id'])) {
    die("No grant selected.");
}

$grant_id = $_GET['grant_id'];

// Get grant details
$sql = "SELECT * FROM grants WHERE grant_id='$grant_id'";
$result = mysqli_query($conn, $sql);
$grant = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>

<head>
   

<title>Apply for Grant</title>

<style>

body{
    font-family:Arial;
    background:#f4f4f4;
    margin:40px;
}

.container{
    background:white;
    width:700px;
    margin:auto;
    padding:20px;
    border-radius:8px;
}

input, textarea{
    width:100%;
    padding:10px;
    margin-top:8px;
    margin-bottom:15px;
}

input[type=submit]{
    background:green;
    color:white;
    border:none;
    cursor:pointer;
}

input[type=submit]:hover{
    background:darkgreen;
}

</style>

</head>

<body>



<div class="container">

<h2>Grant Application</h2>
<?php

if($message!="")
{
    echo "<p style='color:green;'><b>$message</b></p>";
}

?>

<h3><?php echo $grant['title']; ?></h3>

<p><b>Organization:</b> <?php echo $grant['organization']; ?></p>

<p><b>Description:</b> <?php echo $grant['description']; ?></p>

<p><b>Funding:</b> Ksh <?php echo number_format($grant['funding_amount']); ?></p>

<p><b>Deadline:</b> <?php echo $grant['deadline']; ?></p>

<hr>

<form method="POST" enctype="multipart/form-data">

<label>Research Proposal (PDF)</label>

<input type="file" name="proposal" required>

<br><br>

<input type="submit" name="apply" value="Submit Application">

</form>

</div>



</body>

</html>