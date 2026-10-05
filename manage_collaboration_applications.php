<?php
session_start();
include("db_connect.php");
include("navbar.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != "Administrator") {
    die("Access Denied!");
}

$search = "";

if(isset($_GET['search']))
{
    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $sql = "SELECT
            collaboration_applications.*,
            users.full_name,
            users.email,
            collaboration_calls.project_title

            FROM collaboration_applications

            INNER JOIN users
            ON collaboration_applications.applicant_id = users.user_id

            INNER JOIN collaboration_calls
            ON collaboration_applications.call_id = collaboration_calls.call_id

            WHERE users.full_name LIKE '%$search%'
            OR collaboration_calls.project_title LIKE '%$search%'

            ORDER BY application_date DESC";
}
else
{
    $sql = "SELECT
            collaboration_applications.*,
            users.full_name,
            users.email,
            collaboration_calls.project_title

            FROM collaboration_applications

            INNER JOIN users
            ON collaboration_applications.applicant_id = users.user_id

            INNER JOIN collaboration_calls
            ON collaboration_applications.call_id = collaboration_calls.call_id

            ORDER BY application_date DESC";
}

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Collaboration Applications</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h2 class="text-primary mb-4">

Manage Collaboration Applications

</h2>

<form method="GET" class="mb-4">

<div class="row">

<div class="col-md-10">

<input
type="text"
name="search"
class="form-control"
placeholder="Search applicant or project..."
value="<?php echo htmlspecialchars($search); ?>">

</div>

<div class="col-md-2">

<button class="btn btn-primary w-100">

Search

</button>

</div>

</div>

</form>

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>Applicant</th>

<th>Email</th>

<th>Project</th>

<th>Motivation</th>

<th>Status</th>

<th>Date</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo htmlspecialchars($row['full_name']); ?></td>

<td><?php echo htmlspecialchars($row['email']); ?></td>

<td><?php echo htmlspecialchars($row['project_title']); ?></td>

<td><?php echo htmlspecialchars($row['motivation']); ?></td>

<td>

<?php

if($row['status']=="Shortlisted")
{
    echo "<span class='badge bg-success'>Shortlisted</span>";
}
elseif($row['status']=="Rejected")
{
    echo "<span class='badge bg-danger'>Rejected</span>";
}
else
{
    echo "<span class='badge bg-warning text-dark'>Pending</span>";
}

?>

</td>

<td><?php echo $row['application_date']; ?></td>

<td>

<a href="view_collaboration_application.php?id=<?php echo $row['application_id']; ?>"
class="btn btn-primary btn-sm">
View
</a>

<a href="shortlist_application.php?id=<?php echo $row['application_id']; ?>"
class="btn btn-success btn-sm">

Shortlist

</a>

<a href="reject_collaboration.php?id=<?php echo $row['application_id']; ?>"
class="btn btn-danger btn-sm">

Reject

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

<a href="admin_dashboard.php" class="btn btn-secondary">

← Back to Dashboard

</a>

</div>

</body>

</html>