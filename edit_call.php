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
    die("Invalid Collaboration Call.");
}

$call_id = intval($_GET['id']);

$result = mysqli_query($conn,"SELECT * FROM collaboration_calls WHERE call_id='$call_id'");

if(mysqli_num_rows($result)==0)
{
    die("Collaboration Call not found.");
}

$call = mysqli_fetch_assoc($result);

$message="";

if(isset($_POST['update']))
{
    $project_title = mysqli_real_escape_string($conn,$_POST['project_title']);
    $research_area = mysqli_real_escape_string($conn,$_POST['research_area']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $required_skills = mysqli_real_escape_string($conn,$_POST['required_skills']);
    $required_members = mysqli_real_escape_string($conn,$_POST['required_members']);
    $deadline = $_POST['deadline'];

    $sql = "UPDATE collaboration_calls SET

    project_title='$project_title',
    research_area='$research_area',
    description='$description',
    required_skills='$required_skills',
    required_members='$required_members',
    deadline='$deadline'

    WHERE call_id='$call_id'";

    if(mysqli_query($conn,$sql))
    {
        $message="<div class='alert alert-success'>
        Collaboration Call Updated Successfully.
        </div>";

        $result=mysqli_query($conn,"SELECT * FROM collaboration_calls WHERE call_id='$call_id'");
        $call=mysqli_fetch_assoc($result);
    }
    else
    {
        $message="<div class='alert alert-danger'>".mysqli_error($conn)."</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Edit Collaboration Call</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<div class="card shadow">

<div class="card-header bg-warning">

<h3>Edit Collaboration Call</h3>

</div>

<div class="card-body">

<?php echo $message; ?>

<form method="POST">

<div class="mb-3">
<label>Project Title</label>
<input type="text" name="project_title" class="form-control"
value="<?php echo htmlspecialchars($call['project_title']); ?>" required>
</div>

<div class="mb-3">
<label>Research Area</label>
<input type="text" name="research_area" class="form-control"
value="<?php echo htmlspecialchars($call['research_area']); ?>" required>
</div>

<div class="mb-3">
<label>Description</label>
<textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($call['description']); ?></textarea>
</div>

<div class="mb-3">
<label>Required Skills</label>
<textarea name="required_skills" class="form-control" rows="4"><?php echo htmlspecialchars($call['required_skills']); ?></textarea>
</div>

<div class="mb-3">
<label>Required Members</label>
<input type="number" name="required_members" class="form-control"
value="<?php echo htmlspecialchars($call['required_members']); ?>" required>
</div>

<div class="mb-3">
<label>Deadline</label>
<input type="date" name="deadline" class="form-control"
value="<?php echo $call['deadline']; ?>" required>
</div>

<button type="submit" name="update" class="btn btn-warning">
Update Collaboration Call
</button>

<a href="manage_calls.php" class="btn btn-secondary">
Back
</a>

</form>

</div>

</div>

</div>

</body>
</html>