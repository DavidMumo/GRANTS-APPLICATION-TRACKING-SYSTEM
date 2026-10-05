<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE user_id='$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>My Profile</h3>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4 text-center">

<?php

$image = "uploads/" . $user['profile_picture'];

if(!file_exists($image))
{
    $image = "uploads/default.png";
}

?>

<img src="<?php echo $image; ?>"
class="rounded-circle border"
width="180"
height="180">

</div>

<div class="col-md-8">

<table class="table">

<tr>
<th>Full Name</th>
<td><?php echo $user['full_name']; ?></td>
</tr>

<tr>
<th>Email</th>
<td><?php echo $user['email']; ?></td>
</tr>

<tr>
<th>Institution</th>
<td><?php echo $user['institution']; ?></td>
</tr>

<tr>
<th>Phone</th>
<td><?php echo $user['phone_number']; ?></td>
</tr>

<tr>
<th>Role</th>
<td><?php echo $user['role']; ?></td>
</tr>

</table>

<a href="edit_profile.php"
class="btn btn-primary">
Edit Profile
</a>

<a href="upload_profile_picture.php"
class="btn btn-info">

Upload Profile Picture

</a>

<a href="change_password.php" class="btn btn-warning">
    Change Password
</a>


</div>

</div>

</div>

</div>

</div>

</body>
</html>