<?php
session_start();
include("db_connect.php");
include("navbar.php");


if(isset($_GET['success']))
{
    if($_GET['success']=="deleted")
    {
        echo "<div class='alert alert-success'>
        Grant deleted successfully.
        </div>";
    }
}


if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != "Administrator")
{
    die("Access Denied!");
}

// Search
$search = "";

if(isset($_GET['search']))
{
    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $sql = "SELECT * FROM grants
            WHERE title LIKE '%$search%'
            OR organization LIKE '%$search%'
            OR description LIKE '%$search%'
            ORDER BY deadline ASC";
}
else
{
    $sql = "SELECT * FROM grants
            ORDER BY deadline ASC";
}

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Grants</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h2 class="text-primary">Manage Grants</h2>

<p class="text-muted">
Manage all grant opportunities in the system.
</p>

<form method="GET">

<div class="input-group mb-3">

<input
type="text"
name="search"
class="form-control"
placeholder="Search grants..."
value="<?php echo htmlspecialchars($search); ?>">

<button class="btn btn-primary">

Search

</button>

</div>

</form>

<a href="add_grant.php" class="btn btn-success mb-3">

+ Add New Grant

</a>

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Title</th>
<th> Organization</th>
<th>description</th>
<th>Deadline</th>
<th>Status</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['grant_id']; ?></td>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['organization']; ?></td>

<td><?php echo $row['description']; ?></td>

<td><?php echo $row['deadline']; ?></td>

<td>

<?php

if($row['status']=="Open")
{
    echo "<span class='badge bg-success'>Open</span>";
}
else
{
    echo "<span class='badge bg-danger'>Closed</span>";
}

?>

</td>

<td>

<a href="edit_grant.php?id=<?php echo $row['grant_id']; ?>" class="btn btn-warning btn-sm">
Edit
</a>

<a href="delete_grant.php?id=<?php echo $row['grant_id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Are you sure you want to delete this grant?');">

Delete

</a>


</td>

</tr>

<?php } ?>

</tbody>

</table>

<a href="admin_dashboard.php" class="btn btn-secondary">

Back to Dashboard

</a>

</div>

</body>

</html>