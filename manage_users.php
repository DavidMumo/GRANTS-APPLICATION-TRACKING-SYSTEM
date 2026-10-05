<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("db_connect.php");
include("navbar.php");

// Only administrators can access this page
if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != "Administrator")
{
    die("Access Denied!");
}

// Fetch all users
$sql = "SELECT * FROM users ORDER BY user_id ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>

<title>Manage Users</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h2 class="text-primary">Manage Users</h2>

<p class="text-muted">
View, edit and manage all registered users.
</p>

<table class="table table-bordered table-striped table-hover">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Institution</th>
<th>Role</th>
<th>Registered</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($result)) { ?>

<tr>

<td><?php echo $row['user_id']; ?></td>

<td><?php echo $row['full_name']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['phone_number']; ?></td>

<td><?php echo $row['institution']; ?></td>

<td>

<?php

if($row['role']=="Administrator")
{
    echo "<span class='badge bg-danger'>Administrator</span>";
}
else
{
    echo "<span class='badge bg-success'>Researcher</span>";
}

?>

</td>

<td><?php echo $row['date_created']; ?></td>

<td>

<a href="edit_user.php?id=<?php echo $row['user_id']; ?>"
class="btn btn-warning btn-sm">

Edit

</a>

<a href="delete_user.php?id=<?php echo $row['user_id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this user?');">

Delete

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

<a href="admin_dashboard.php" class="btn btn-primary">

← Back to Dashboard

</a>

</div>

</body>

</html>