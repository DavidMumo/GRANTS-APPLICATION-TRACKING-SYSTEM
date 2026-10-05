<?php
session_start();
include("db_connect.php");
include("navbar.php");


if(isset($_GET['success']))
{
    if($_GET['success']=="deleted")
    {
        echo "<div class='alert alert-success alert-dismissible fade show'>
                Collaboration Call deleted successfully.
                <button class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}
?>

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

if($_SESSION['role']!="Administrator")
{
    die("Access Denied!");
}

$search="";

if(isset($_GET['search']))
{
    $search=mysqli_real_escape_string($conn,$_GET['search']);

    $sql="SELECT collaboration_calls.*, users.full_name
          FROM collaboration_calls
          INNER JOIN users
          ON collaboration_calls.created_by=users.user_id
          WHERE project_title LIKE '%$search%'
          OR research_area LIKE '%$search%'
          ORDER BY date_created DESC";
}
else
{
    $sql="SELECT collaboration_calls.*, users.full_name
          FROM collaboration_calls
          INNER JOIN users
          ON collaboration_calls.created_by=users.user_id
          ORDER BY date_created DESC";
}

$result=mysqli_query($conn,$sql);
?>

<!DOCTYPE html>

<html>

<head>

<title>Manage Collaboration Calls</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h2 class="text-primary">

Manage Collaboration Calls

</h2>

<form method="GET" class="mb-3">

<div class="input-group">

<input
type="text"
name="search"
class="form-control"
placeholder="Search project title or research area">

<button class="btn btn-primary">

Search

</button>

</div>

</form>

<table class="table table-bordered table-hover">

<tr class="table-dark">

<th>ID</th>

<th>Project</th>

<th>Research Area</th>

<th>Members</th>

<th>Deadline</th>

<th>Created By</th>

<th>Action</th>

</tr>

<?php

while($row=mysqli_fetch_assoc($result))
{

?>

<tr>

<td><?php echo $row['call_id']; ?></td>

<td><?php echo $row['project_title']; ?></td>

<td><?php echo $row['research_area']; ?></td>

<td><?php echo $row['required_members']; ?></td>

<td><?php echo $row['deadline']; ?></td>

<td><?php echo $row['full_name']; ?></td>

<td>

<a
href="edit_call.php?id=<?php echo $row['call_id']; ?>"
class="btn btn-warning btn-sm">

Edit

</a>

<a
href="delete_call.php?id=<?php echo $row['call_id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this collaboration call?')">

Delete

</a>

</td>

</tr>

<?php

}

?>

</table>

<a href="admin_dashboard.php"
class="btn btn-secondary">

Back

</a>

</div>

</body>

</html>