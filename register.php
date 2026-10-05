<?php
include("db_connect.php");
include("navbar.php");

$message = "";
?>
<?php
include("db_connect.php");

$message = "";

if(isset($_POST['register']))
{
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $institution = $_POST['institution'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Encrypt password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check whether email already exists
    $check_email = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $check_email);

    if(mysqli_num_rows($result) > 0)
    {
        $message = "Email already exists!";
    }
    else
    {
        $sql = "INSERT INTO users(full_name,email,phone_number,institution,password,role)
                VALUES('$full_name','$email','$phone_number','$institution','$hashed_password','$role')";

        if(mysqli_query($conn,$sql))
        {
            $message = "Registration Successful!";
        }
        else
        {
            $message = "Registration Failed!";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>

<title>User Registration</title>

</head>

<body>

<h2>User Registration</h2>

<?php
if($message!="")
{
    echo "<p style='color:green; font-weight:bold;'>$message</p>";
}
?>

<form method="POST">

<label>Full Name</label><br>

<input type="text" name="full_name" required>

<br><br>

<label>Email</label><br>

<input type="email" name="email" required>

<br><br>

<label>Phone Number</label><br>

<input type="text" name="phone_number" required>

<br><br>

<label>Institution</label><br>

<input type="text" name="institution" required>

<br><br>

<label>Password</label><br>

<input type="password" name="password" required>

<br><br>

<label>Role</label>

<br>

<select name="role">

<option value="Researcher">Researcher</option>

<option value="Administrator">Administrator</option>

</select>

<br><br>

<input type="submit" name="register" value="Register">

</form>

</body>

</html>