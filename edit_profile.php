<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch user details
$result = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'");
$user = mysqli_fetch_assoc($result);

// Update profile
if (isset($_POST['update'])) {

    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $institution = mysqli_real_escape_string($conn, $_POST['institution']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_number']);

    $sql = "UPDATE users SET
            full_name='$full_name',
            email='$email',
            institution='$institution',
            phone_number='$phone_number'
            WHERE user_id='$user_id'";

    if (mysqli_query($conn, $sql)) {

        $_SESSION['fullname'] = $full_name;

        $message = "Profile updated successfully.";

        // Reload updated data
        $result = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'");
        $user = mysqli_fetch_assoc($result);

    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Edit Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>Edit Profile</h3>

</div>

<div class="card-body">

<?php
if($message!=""){
    echo "<div class='alert alert-success'>$message</div>";
}
?>

<form method="POST">

<div class="mb-3">

<label class="form-label">Full Name</label>

<input
type="text"
name="full_name"
class="form-control"
value="<?php echo $user['full_name']; ?>"
required>

</div>

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
value="<?php echo $user['email']; ?>"
required>

</div>

<div class="mb-3">

<label>Institution</label>

<input
type="text"
name="institution"
class="form-control"
value="<?php echo $user['institution']; ?>">

</div>

<div class="mb-3">

<label>Phone_number</label>

<input
type="text"
name="phone_number"
class="form-control"
value="<?php echo $user['phone_number']; ?>">

</div>

<button
type="submit"
name="update"
class="btn btn-success">

Update Profile

</button>

<a href="profile.php" class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

</div>

</body>

</html>