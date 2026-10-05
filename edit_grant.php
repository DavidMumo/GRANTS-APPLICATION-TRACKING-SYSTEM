<?php
session_start();
include("db_connect.php");
include("navbar.php");

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != "Administrator")
{
    die("Access Denied!");
}

if(!isset($_GET['id']))
{
    die("Invalid Grant.");
}

$grant_id = $_GET['id'];

$result = mysqli_query($conn,"SELECT * FROM grants WHERE grant_id='$grant_id'");
$grant = mysqli_fetch_assoc($result);

if(!$grant)
{
    die("Grant not found.");
}

$message="";

if(isset($_POST['update']))
{

$title=mysqli_real_escape_string($conn,$_POST['title']);
$organization=mysqli_real_escape_string($conn,$_POST['organization']);
$description=mysqli_real_escape_string($conn,$_POST['description']);
$funding_amount=mysqli_real_escape_string($conn,$_POST['funding_amount']);
$deadline=$_POST['deadline'];
$eligibility=mysqli_real_escape_string($conn,$_POST['eligibility']);
$status=$_POST['status'];

$sql="UPDATE grants SET

title='$title',
organization='$organization',
description='$description',
funding_amount='$funding_amount',
deadline='$deadline',
eligibility='$eligibility',
status='$status'

WHERE grant_id='$grant_id'";

if(mysqli_query($conn,$sql))
{
$message="<div class='alert alert-success'>
Grant updated successfully.
</div>";

$result=mysqli_query($conn,"SELECT * FROM grants WHERE grant_id='$grant_id'");
$grant=mysqli_fetch_assoc($result);

}
else
{
$message=mysqli_error($conn);
}

}

?>

<!DOCTYPE html>

<html>

<head>

<title>Edit Grant</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<div class="card shadow">

<div class="card-header bg-warning">

<h3>Edit Grant</h3>

</div>

<div class="card-body">

<?php echo $message; ?>

<form method="POST">

<div class="mb-3">

<label>Grant Title</label>

<input
type="text"
name="title"
class="form-control"
value="<?php echo $grant['title']; ?>"
required>

</div>

<div class="mb-3">

<label>Organization</label>

<input
type="text"
name="organization"
class="form-control"
value="<?php echo $grant['organization']; ?>"
required>

</div>

<div class="mb-3">

<label>Description</label>

<textarea
name="description"
rows="5"
class="form-control"><?php echo $grant['description']; ?></textarea>

</div>

<div class="mb-3">

<label>Funding Amount</label>

<input
type="number"
name="funding_amount"
class="form-control"
value="<?php echo $grant['funding_amount']; ?>">

</div>

<div class="mb-3">

<label>Deadline</label>

<input
type="date"
name="deadline"
class="form-control"
value="<?php echo $grant['deadline']; ?>">

</div>

<div class="mb-3">

<label>Eligibility</label>

<textarea
name="eligibility"
rows="4"
class="form-control"><?php echo $grant['eligibility']; ?></textarea>

</div>

<div class="mb-3">

<label>Status</label>

<select
name="status"
class="form-control">

<option value="Open"
<?php if($grant['status']=="Open") echo "selected"; ?>>
Open
</option>

<option value="Closed"
<?php if($grant['status']=="Closed") echo "selected"; ?>>
Closed
</option>

</select>

</div>

<button
type="submit"
name="update"
class="btn btn-warning">

Update Grant

</button>

<a href="manage_grants.php"
class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>

</div>

</body>

</html>