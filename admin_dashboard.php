<?php
session_start();
include("db_connect.php");
include("navbar.php");



// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user is an administrator
if ($_SESSION['role'] != "Administrator") {
    die("Access Denied! Administrators only.");
}

// Dashboard Statistics
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM users"))['total'];

$totalGrants = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM grants"))['total'];

$totalGrantApplications = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM grant_applications"))['total'];

$totalCalls = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM collaboration_calls"))['total'];

$totalCollaborationApplications = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM collaboration_applications"))['total'];

$totalShortlisted = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS total FROM collaboration_applications WHERE status='Shortlisted'"))['total'];
?>

<!DOCTYPE html>
<html>
<head>

<title>Administrator Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h2 class="text-primary">
Administrator Dashboard
</h2>

<p>
Welcome
<strong><?php echo $_SESSION['full_name']; ?></strong>
</p>

<div class="row">

<div class="col-md-4 mb-3">

<div class="card bg-primary text-white">

<div class="card-body">

<h5>Total Users</h5>

<h2><?php echo $totalUsers; ?></h2>

</div>

</div>

</div>

<div class="col-md-4 mb-3">

<div class="card bg-success text-white">

<div class="card-body">

<h5>Total Grants</h5>

<h2><?php echo $totalGrants; ?></h2>

</div>

</div>

</div>

<div class="col-md-4 mb-3">

<div class="card bg-warning text-dark">

<div class="card-body">

<h5>Grant Applications</h5>

<h2><?php echo $totalGrantApplications; ?></h2>

</div>

</div>

</div>

<div class="col-md-4 mb-3">

<div class="card bg-info text-white">

<div class="card-body">

<h5>Collaboration Calls</h5>

<h2><?php echo $totalCalls; ?></h2>

</div>

</div>

</div>

<div class="col-md-4 mb-3">

<div class="card bg-secondary text-white">

<div class="card-body">

<h5>Collaboration Applications</h5>

<h2><?php echo $totalCollaborationApplications; ?></h2>

</div>

</div>

</div>

<div class="col-md-4 mb-3">

<div class="card bg-danger text-white">

<div class="card-body">

<h5>Shortlisted Members</h5>

<h2><?php echo $totalShortlisted; ?></h2>

</div>

</div>

</div>

</div>

<hr>



<hr>


<div class="row">

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <h4>👥</h4>
                <h5>Manage Users</h5>
                <p>View, edit, delete and manage user roles.</p>
                <a href="manage_users.php" class="btn btn-primary">Open</a>
                
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <h4>📑</h4>
                <h5>Manage Grants</h5>
                <p>Add, edit and delete grant opportunities.</p>
                <a href="manage_grants.php" class="btn btn-success">Open</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <h4>📝</h4>
                <h5>Grant Applications</h5>
                <p>Review all submitted grant applications.</p>
                <a href="manage_grant_applications.php" class="btn btn-warning">Open</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <h4>🤝</h4>
                <h5>Collaboration Calls</h5>
                <p>Manage all collaboration calls.</p>
                <a href="manage_calls.php" class="btn btn-info">Open</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <h4>📬</h4>
                <h5>Collaboration Applications</h5>
                <p>View and manage collaboration applications.</p>
                <a href="manage_collaboration_applications.php" class="btn btn-secondary">Open</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <h4>📊</h4>
                <h5>Reports</h5>
                <p>Generate reports and system statistics.</p>
                <a href="reports.php" class="btn btn-dark">Open</a>
            </div>
        </div>
    </div>

</div>