<?php
session_start();
include("db_connect.php");
include("navbar.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch collaboration calls together with the creator's name
$sql = "SELECT collaboration_calls.*, users.full_name
        FROM collaboration_calls
        INNER JOIN users
        ON collaboration_calls.created_by = users.user_id
        ORDER BY collaboration_calls.date_created DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Collaboration Calls</title>

    <style>

    body{
        font-family:Arial;
        background:#f2f2f2;
    }

    .container{
        width:90%;
        margin:auto;
    }

    .card{
        background:white;
        padding:20px;
        margin-top:20px;
        border-radius:10px;
        box-shadow:0px 0px 10px lightgray;
    }

    h1{
        text-align:center;
        color:#0066cc;
    }

    .btn{
        background:green;
        color:white;
        text-decoration:none;
        padding:10px 18px;
        border-radius:5px;
    }

    </style>

</head>

<body>

<div class="container">

<h1>Available Collaboration Calls</h1>

<?php

if(mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result))
    {

?>

<div class="card">

<h2><?php echo $row['project_title']; ?></h2>

<p><strong>Research Area:</strong> <?php echo $row['research_area']; ?></p>

<p><strong>Description:</strong><br>
<?php echo $row['description']; ?>
</p>

<p><strong>Required Skills:</strong><br>
<?php echo $row['required_skills']; ?>
</p>

<p><strong>Required Members:</strong>
<?php echo $row['required_members']; ?>
</p>

<p><strong>Deadline:</strong>
<?php echo $row['deadline']; ?>
</p>

<p><strong>Posted By:</strong>
<?php echo $row['full_name']; ?>
</p>

<a class="btn"
href="apply_call.php?call_id=<?php echo $row['call_id']; ?>">
Apply
</a>

<a class="btn"
href="view_applicants.php?call_id=<?php echo $row['call_id']; ?>">
View Applicants
</a>

</div>

<?php

    }

}
else{

    echo "<h3>No collaboration calls available.</h3>";

}

?>

<br><br>

<a href="dashboard.php">← Back to Dashboard</a>

</div>

</body>
</html>