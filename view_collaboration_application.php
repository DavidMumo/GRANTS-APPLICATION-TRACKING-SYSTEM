<?php
session_start();
include("db_connect.php");
include("navbar.php");

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

if($_SESSION['role']!="Administrator")
{
    die("Access Denied!");
}

if(!isset($_GET['id']))
{
    die("Invalid Application");
}

$id = intval($_GET['id']);

$sql = "SELECT
collaboration_applications.*,
users.full_name,
users.email,
users.institution,
collaboration_calls.project_title,
collaboration_calls.research_area

FROM collaboration_applications

INNER JOIN users
ON collaboration_applications.applicant_id = users.user_id

INNER JOIN collaboration_calls
ON collaboration_applications.call_id = collaboration_calls.call_id

WHERE application_id='$id'";

$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result)==0)
{
    die("Application not found.");
}

$app = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>

<html>

<head>

<title>View Collaboration Application</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>Collaboration Application Details</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th width="30%">Applicant</th>
<td><?php echo $app['full_name']; ?></td>
</tr>

<tr>
<th>Email</th>
<td><?php echo $app['email']; ?></td>
</tr>

<tr>
<th>Institution</th>
<td><?php echo $app['institution']; ?></td>
</tr>

<tr>
<th>Project</th>
<td><?php echo $app['project_title']; ?></td>
</tr>

<tr>
<th>Research Area</th>
<td><?php echo $app['research_area']; ?></td>
</tr>

<tr>
<th>Motivation Statement</th>
<td><?php echo nl2br(htmlspecialchars($app['motivation'])); ?></td>
</tr>

<tr>
<th>Status</th>
<td><?php echo $app['status']; ?></td>
</tr>

<tr>
<th>Application Date</th>
<td><?php echo $app['application_date']; ?></td>
</tr>

</table>

<a href="manage_collaboration_applications.php" class="btn btn-secondary">

Back

</a>

</div>

</div>

</div>

</body>

</html>