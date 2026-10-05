<?php
session_start();
include("db_connect.php");

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}


error_reporting(E_ALL);
ini_set('display_errors', 1);


$user_id = $_SESSION['user_id'];
$message = "";

if(isset($_POST['change']))
{
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $sql = "SELECT password FROM users WHERE user_id='$user_id'";
    $result = mysqli_query($conn,$sql);
    $user = mysqli_fetch_assoc($result);

    if(password_verify($current_password,$user['password']))
    {
        if($new_password == $confirm_password)
        {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $update = "UPDATE users
                       SET password='$hashed_password'
                       WHERE user_id='$user_id'";

            if(mysqli_query($conn,$update))
            {
                $message = "<div class='alert alert-success'>
                Password changed successfully.
                </div>";
            }
            else
            {
                $message = "<div class='alert alert-danger'>
                ".mysqli_error($conn)."
                </div>";
            }
        }
        else
        {
            $message = "<div class='alert alert-warning'>
            New passwords do not match.
            </div>";
        }
    }
    else
    {
        $message = "<div class='alert alert-danger'>
        Current password is incorrect.
        </div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Change Password</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-warning">

<h3>Change Password</h3>

</div>

<div class="card-body">

<?php echo $message; ?>

<form method="POST">

<div class="mb-3">

<label>Current Password</label>

<input
type="password"
name="current_password"
class="form-control"
required>

</div>

<div class="mb-3">

<label>New Password</label>

<input
type="password"
name="new_password"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Confirm New Password</label>

<input
type="password"
name="confirm_password"
class="form-control"
required>

</div>

<button
type="submit"
name="change"
class="btn btn-success">

Change Password

</button>

<a href="profile.php" class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>

</div>

</body>
</html>