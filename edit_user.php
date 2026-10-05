<?php
session_start();
include("db_connect.php");
include("navbar.php");

// Allow only administrators
if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != "Administrator")
{
    die("Access Denied!");
}

// Check user ID
if(!isset($_GET['id']))
{
    die("User not found.");
}

$id = intval($_GET['id']);

// Fetch user details
$sql = "SELECT * FROM users WHERE user_id='$id'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) == 0)
{
    die("User not found.");
}

$user = mysqli_fetch_assoc($result);

// Update details
if(isset($_POST['update']))
{
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $institution = mysqli_real_escape_string($conn, $_POST['institution']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $update = "UPDATE users SET
                full_name='$full_name',
                email='$email',
                institution='$institution',
                phone_number='$phone_number',
                role='$role'
                WHERE user_id='$id'";

    if(mysqli_query($conn, $update))
    {
        echo "<script>
                alert('User updated successfully.');
                window.location='manage_users.php';
              </script>";
        exit();
    }
    else
    {
        echo "<div class='alert alert-danger'>".mysqli_error($conn)."</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Edit User</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-warning">

<h3>Edit User</h3>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label>Full Name</label>

<input
type="text"
name="full_name"
class="form-control"
value="<?php echo htmlspecialchars($user['full_name']); ?>"
required>

</div>

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
value="<?php echo htmlspecialchars($user['email']); ?>"
required>

</div>

<div class="mb-3">

<label>Institution</label>

<input
type="text"
name="institution"
class="form-control"
value="<?php echo htmlspecialchars($user['institution']); ?>">

</div>

<div class="mb-3">

<label>Phone Number</label>

<input
type="text"
name="phone_number"
class="form-control"
value="<?php echo htmlspecialchars($user['phone_number']); ?>">

</div>

<div class="mb-3">

<label>Role</label>

<select name="role" class="form-control">

<option value="Researcher"
<?php if($user['role']=="Researcher") echo "selected"; ?>>
Researcher
</option>

<option value="Administrator"
<?php if($user['role']=="Administrator") echo "selected"; ?>>
Administrator
</option>

</select>

</div>

<button
type="submit"
name="update"
class="btn btn-success">

Update User

</button>

<a href="manage_users.php" class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

</div>

</body>
</html>