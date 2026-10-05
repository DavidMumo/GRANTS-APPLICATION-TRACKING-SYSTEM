<?php
session_start();
include("db_connect.php");
include("navbar.php");

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != "Administrator")
{
    die("Access Denied!");
}

$sql = "SELECT
grant_applications.*,
users.full_name,
users.email,
grants.title

FROM grant_applications

INNER JOIN users
ON grant_applications.user_id = users.user_id

INNER JOIN grants
ON grant_applications.grant_id = grants.grant_id

ORDER BY application_date DESC";

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Grant Applications</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h2 class="mb-4">Manage Grant Applications</h2>

<table class="table table-bordered table-hover">

<tr class="table-dark">

<th>Applicant</th>
<th>Email</th>
<th>Grant</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>

</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['full_name']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['title']; ?></td>

<td>

<?php

if($row['status']=="Approved")
{
echo "<span class='badge bg-success'>Approved</span>";
}
elseif($row['status']=="Rejected")
{
echo "<span class='badge bg-danger'>Rejected</span>";
}
else
{
echo "<span class='badge bg-warning'>Pending</span>";
}

?>

</td>

<td><?php echo $row['application_date']; ?></td>

<td>

<a href="approve_application.php?id=<?php echo $row['application_id']; ?>"
class="btn btn-success btn-sm">

Approve

</a>

<a href="reject_application.php?id=<?php echo $row['application_id']; ?>"
class="btn btn-danger btn-sm">

Reject

</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</body>

</html>