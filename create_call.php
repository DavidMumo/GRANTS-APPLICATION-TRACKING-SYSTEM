<?php
session_start();
include("db_connect.php");


// Check if user is logged in
if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

$message = "";

if(isset($_POST['create_call']))
{
    $created_by = $_SESSION['user_id'];

    $project_title = mysqli_real_escape_string($conn,$_POST['project_title']);
    $research_area = mysqli_real_escape_string($conn,$_POST['research_area']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $required_skills = mysqli_real_escape_string($conn,$_POST['required_skills']);
    $required_members = $_POST['required_members'];
    $deadline = $_POST['deadline'];

    $sql = "INSERT INTO collaboration_calls
    (created_by, project_title, research_area, description, required_skills, required_members, deadline)

    VALUES

    ('$created_by',
    '$project_title',
    '$research_area',
    '$description',
    '$required_skills',
    '$required_members',
    '$deadline')";

    if(mysqli_query($conn,$sql))
    {
        $message = "Collaboration Call Created Successfully!";
    }
    else
    {
        $message = mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Create Collaboration Call</title>



<style>

body{
    font-family:Arial;
    background:#f2f2f2;
}

.container{
    width:700px;
    margin:40px auto;
    background:white;
    padding:20px;
    border-radius:10px;
}

input,textarea{
    width:100%;
    padding:10px;
    margin-bottom:15px;
}

input[type=submit]{
    background:green;
    color:white;
    border:none;
    cursor:pointer;
    padding:10px;
}

</style>

</head>

<body>


<div class="container">

<h2>Create Collaboration Call</h2>



<?php
if($message!="")
{
    echo "<h3 style='color:green;'>$message</h3>";
}
?>

<form method="POST">

<label>Project Title</label>

<input type="text" name="project_title" required>

<label>Research Area</label>

<input type="text" name="research_area" required>

<label>Description</label>

<textarea name="description" required></textarea>

<label>Required Skills</label>

<textarea name="required_skills" required></textarea>

<label>Required Members</label>

<input type="number" name="required_members" required>

<label>Deadline</label>

<input type="date" name="deadline" required>

<input type="submit" name="create_call" value="Create Collaboration Call">

</form>

</div>



</body>

</html>