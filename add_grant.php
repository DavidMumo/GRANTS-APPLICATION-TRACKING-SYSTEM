<?php
session_start();
include("db_connect.php");
include("navbar.php");

// Only Administrator
if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != "Administrator")
{
    die("Access Denied!");
}

$message="";

if(isset($_POST['save']))
{
    $title=mysqli_real_escape_string($conn,$_POST['title']);
    $organization=mysqli_real_escape_string($conn,$_POST['organization']);
    $description=mysqli_real_escape_string($conn,$_POST['description']);
    $funding_amount=mysqli_real_escape_string($conn,$_POST['funding_amount']);
    $deadline=$_POST['deadline'];
    $eligibility=mysqli_real_escape_string($conn,$_POST['eligibility']);
    $status=$_POST['status'];

    $sql="INSERT INTO grants
    (title,organization,description,funding_amount,deadline,eligibility,status)

    VALUES

    ('$title',
    '$organization',
    '$description',
    '$funding_amount',
    '$deadline',
    '$eligibility',
    '$status')";

    if(mysqli_query($conn,$sql))
    {
        $message="<div class='alert alert-success'>
        Grant added successfully.
        </div>";
    }
    else
    {
        $message="<div class='alert alert-danger'>
        ".mysqli_error($conn)."
        </div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Add Grant</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h3>Add New Grant</h3>

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
required>

</div>

<div class="mb-3">

<label>Organization</label>

<input
type="text"
name="organization"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Description</label>

<textarea
name="description"
rows="5"
class="form-control"
required></textarea>

</div>

<div class="mb-3">

<label>Funding Amount (KES)</label>

<input
type="number"
name="funding_amount"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Deadline</label>

<input
type="date"
name="deadline"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Eligibility</label>

<textarea
name="eligibility"
rows="4"
class="form-control"
required></textarea>

</div>

<div class="mb-3">

<label>Status</label>

<select
name="status"
class="form-control">

<option value="Open">Open</option>

<option value="Closed">Closed</option>

</select>

</div>

<button
type="submit"
name="save"
class="btn btn-success">

Save Grant

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