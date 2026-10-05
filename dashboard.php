<?php
session_start();
include("db_connect.php");


if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id=$_SESSION['user_id'];

$result=mysqli_query($conn,"SELECT COUNT(*) AS total FROM users");
$users=mysqli_fetch_assoc($result);
$total_users=$users['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM grants");
$row = mysqli_fetch_assoc($result);
$total_grants = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM grant_applications WHERE user_id='$user_id'");
$row = mysqli_fetch_assoc($result);
$total_applications = $row['total'];

$result=mysqli_query($conn,"SELECT COUNT(*) AS total FROM collaboration_calls");
$call=mysqli_fetch_assoc($result);
$total_calls=$call['total'];

$result = mysqli_query($conn,"SELECT COUNT(*) AS total FROM collaboration_applications");
$row = mysqli_fetch_assoc($result);
$total_collaborations = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM collaboration_applications WHERE status='Shortlisted'");
$row = mysqli_fetch_assoc($result);
$total_shortlisted = $row['total'];



include("navbar.php");


?>

<!DOCTYPE html>

<html>

<head>

<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <div class="container mt-4">

<div class="card shadow-lg border-0">

<div class="card-body">

<h2 class="text-primary">
Welcome,
<?php
echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "User";
?>
</h2>

<p class="text-muted">
Grant Application Tracking and Collaboration Management System
</p>

<p>

<strong>Role:</strong>

<span class="badge bg-success">

<?php echo $_SESSION['role']; ?>

</span>

</p>

</div>

</div>

<br>

<div class="row">

<div class="col-md-4 mb-4">

<div class="card shadow border-start border-primary border-5">
<div class="card-body">

<h5>Total Users</h5>

<h2><?php echo $total_users; ?></h2>

</div>

</div>

</div>



<div class="col-md-4 mb-4">

<div class="card shadow border-start border-success border-5">

<div class="card-body">

<h5>Available Grants</h5>

<h2><?php echo $total_grants; ?></h2>

</div>

</div>

</div>

<div class="col-md-4 mb-4">

<div class="card shadow border-start border-warning border-5">

<div class="card-body">

<h5>My Applications</h5>

<h2><?php echo $total_applications; ?></h2>

</div>

</div>

</div>

<div class="col-md-4 mb-4">

<div class="card shadow border-start border-info border-5">

<div class="card-body">

<h5>Collaboration Calls</h5>

<h2><?php echo $total_calls; ?></h2>


</div>

</div>

</div>

<div class="col-md-4 mb-4">

<div class="card shadow border-start border-secondary border-5">

<div class="card-body">

<h5>My Collaboration Applications</h5>

<h2><?php echo $total_collaborations; ?></h2>

</div>

</div>

</div>

<div class="col-md-4 mb-4">

<div class="card shadow border-start border-danger border-5">

<div class="card-body">

<h5>Shortlisted</h5>

<h2><?php echo $total_shortlisted; ?></h2>

</div>

</div>

</div>

</div>

<div class="card shadow">

<div class="card-header bg-primary text-white">

Quick Actions

</div>

<div class="card-body">

<a href="view_grants.php" class="btn btn-success m-2">


View Grants

</a>

<a href="my_applications.php" class="btn btn-warning m-2">

My Applications

</a>

<a href="create collaboration_call.php" class="btn btn-info m-2">



Create Collaboration Call

</a>


<a href="view_calls.php" class="btn btn-secondary m-2">

View Calls

</a>

<a href="logout.php" class="btn btn-danger m-2">

Logout

</a>

</div>

</div>

    </div>




</h2>

<div class="row">

<div class="col-md-4 mb-4">

<?php

$sql = "SELECT grant_applications.*, grants.title
FROM grant_applications
INNER JOIN grants
ON grant_applications.grant_id = grants.grant_id
ORDER BY application_date DESC
LIMIT 5";

$recentApplications = mysqli_query($conn, $sql);

?>







<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>