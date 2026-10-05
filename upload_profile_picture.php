<?php
session_start();
include("db_connect.php");

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if(isset($_POST['upload']))
{
    if(isset($_FILES['picture']) && $_FILES['picture']['error'] == 0)
    {
        $filename = time() . "_" . basename($_FILES["picture"]["name"]);

        $target = "uploads/" . $filename;

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $allowed = array("jpg","jpeg","png","gif");

        if(in_array($extension,$allowed))
        {
            if(move_uploaded_file($_FILES["picture"]["tmp_name"],$target))
            {
                mysqli_query($conn,"
                UPDATE users
                SET profile_picture='$filename'
                WHERE user_id='$user_id'
                ");

                $message = "<div class='alert alert-success'>
                Profile picture uploaded successfully.
                </div>";
            }
            else
            {
                $message = "<div class='alert alert-danger'>
                Upload failed.
                </div>";
            }
        }
        else
        {
            $message = "<div class='alert alert-warning'>
            Only JPG, JPEG, PNG and GIF files are allowed.
            </div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Upload Profile Picture</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-info text-white">

<h3>Upload Profile Picture</h3>

</div>

<div class="card-body">

<?php echo $message; ?>

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">

<label>Select Image</label>

<input
type="file"
name="picture"
class="form-control"
accept=".jpg,.jpeg,.png,.gif"
required>

</div>

<button
type="submit"
name="upload"
class="btn btn-primary">

Upload Picture

</button>

<a href="profile.php"
class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>

</div>

</body>

</html>