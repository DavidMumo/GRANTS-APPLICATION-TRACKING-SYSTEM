<?php
session_start();
include("db_connect.php");
include("navbar.php");

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

if(!isset($_GET['call_id']))
{
    die("Invalid Collaboration Call.");
}

$call_id = $_GET['call_id'];
$user_id = $_SESSION['user_id'];

// Check that the logged-in user owns this collaboration call
$check = mysqli_query($conn,"
SELECT *
FROM collaboration_calls
WHERE call_id='$call_id'
AND created_by='$user_id'
");

if(mysqli_num_rows($check)==0)
{
    die("You are not allowed to view these applicants.");
}

// Get applicants
$sql = "
SELECT
collaboration_applications.*,
users.full_name,
users.email,
users.institution

FROM collaboration_applications

INNER JOIN users
ON collaboration_applications.applicant_id = users.user_id

WHERE call_id='$call_id'

ORDER BY application_date DESC
";

$result=mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>

<title>Applicants</title>

<style>

body{
font-family:Arial;
background:#f4f4f4;
}

.container{
width:90%;
margin:auto;
}

table{

width:100%;
background:white;
border-collapse:collapse;
margin-top:20px;

}

table th{

background:#0066cc;
color:white;
padding:10px;

}

table td{

padding:10px;
border:1px solid #ddd;

}

.btn{

background:green;
color:white;
padding:8px 15px;
text-decoration:none;
border-radius:5px;

}

</style>

</head>

<body>

<div class="container">

<h2>Applicants</h2>

<table>

<tr>

<th>Name</th>
<th>Email</th>
<th>Institution</th>
<th>Motivation</th>
<th>Status</th>
<th>Action</th>

</tr>

<?php

while($row=mysqli_fetch_assoc($result))
{

?>

<tr>

<td><?php echo $row['full_name']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['institution']; ?></td>

<td><?php echo $row['motivation']; ?></td>

<td><?php echo $row['status']; ?></td>

<td>

<a class="btn"
href="shortlist_members.php?id=<?php echo $row['application_id']; ?>">
Shortlist
</a>

</td>

</tr>

<?php

}

?>

</table>

<br>

<a href="view_calls.php">Back</a>

</div>

</body>

</html>