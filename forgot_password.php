<?php

session_start();
include("db_connect.php");

$message = "";
$message_type = "";

if (isset($_POST['send_reset'])) {

    $email = trim($_POST['email']);

    // Find the user
    $stmt = mysqli_prepare(
        $conn,
        "SELECT user_id, full_name, email FROM users WHERE email = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $email);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        // Generate secure reset token
        $token = bin2hex(random_bytes(32));

        // Token expires after 30 minutes
        $expires = date(
            "Y-m-d H:i:s",
            time() + (30 * 60)
        );


        // Store token
        $update = mysqli_prepare(
            $conn,
            "UPDATE users
             SET reset_token = ?, reset_expires = ?
             WHERE user_id = ?"
        );

        mysqli_stmt_bind_param(
            $update,
            "ssi",
            $token,
            $expires,
            $user['user_id']
        );

        mysqli_stmt_execute($update);

        mysqli_stmt_close($update);


        // Localhost reset link
        $reset_link =
            "http://localhost/Grants_Application_System/reset_password.php?token="
            . urlencode($token);


        $message = "

        <strong>Password reset link generated successfully.</strong>

        <br><br>

        For this local development version, use the link below:

        <br><br>

        <a href='$reset_link' class='btn btn-success btn-sm'>
            Reset Password
        </a>

        <br><br>

        <small>
        This reset link will expire in 30 minutes.
        </small>

        ";

        $message_type = "success";

    } else {

        $message =
            "No account was found with that email address.";

        $message_type = "danger";

    }

    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Forgot Password | Grant Application System</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        body {

            margin: 0;

            min-height: 100vh;

            font-family: Arial, Helvetica, sans-serif;

            background: linear-gradient(
                135deg,
                #0f172a,
                #1d4ed8,
                #7c3aed
            );

            display: flex;

            flex-direction: column;

        }


        .welcome-section {

            text-align: center;

            color: white;

            padding: 45px 20px 20px;

        }


        .welcome-section h1 {

            font-size: 30px;

            font-weight: 700;

            margin-bottom: 8px;

        }


        .welcome-section p {

            opacity: 0.9;

            margin: 0;

        }


        .main-container {

            flex: 1;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 20px;

        }


        .forgot-card {

            width: 100%;

            max-width: 450px;

            background: white;

            padding: 35px;

            border-radius: 20px;

            box-shadow: 0 20px 50px rgba(0,0,0,0.25);

        }


        .forgot-icon {

            width: 75px;

            height: 75px;

            border-radius: 50%;

            background: linear-gradient(
                135deg,
                #2563eb,
                #7c3aed
            );

            color: white;

            display: flex;

            justify-content: center;

            align-items: center;

            margin: 0 auto 20px;

            font-size: 32px;

        }


        .forgot-card h2 {

            text-align: center;

            font-weight: 700;

            color: #1e293b;

        }


        .subtitle {

            text-align: center;

            color: #64748b;

            margin-bottom: 25px;

        }


        .form-label {

            font-weight: 600;

        }


        .form-control {

            height: 48px;

            border-radius: 10px;

        }


        .reset-button {

            width: 100%;

            height: 50px;

            border: none;

            border-radius: 10px;

            background: linear-gradient(
                135deg,
                #2563eb,
                #7c3aed
            );

            color: white;

            font-weight: 600;

            font-size: 16px;

        }


        .reset-button:hover {

            box-shadow: 0 8px 20px rgba(37,99,235,0.3);

            transform: translateY(-2px);

        }


        .back-login {

            text-align: center;

            margin-top: 20px;

        }


        .back-login a {

            color: #2563eb;

            text-decoration: none;

            font-weight: 600;

        }


        .footer {

            text-align: center;

            color: rgba(255,255,255,0.8);

            padding: 15px;

            font-size: 13px;

        }

    </style>

</head>


<body>


<!-- Welcome -->

<div class="welcome-section">

    <h1>

        <i class="bi bi-award"></i>

        Welcome to the Grant Application System

    </h1>

    <p>

        Grant Application Tracking and Collaboration Management System

    </p>

</div>



<!-- Main -->

<div class="main-container">

    <div class="forgot-card">


        <div class="forgot-icon">

            <i class="bi bi-key"></i>

        </div>


        <h2>

            Forgot Password?

        </h2>


        <p class="subtitle">

            Enter your registered email address to reset your password.

        </p>


        <?php if ($message != "") { ?>

            <div class="alert alert-<?php echo $message_type; ?>">

                <?php echo $message; ?>

            </div>

        <?php } ?>


        <form method="POST">


            <div class="mb-4">

                <label class="form-label">

                    <i class="bi bi-envelope"></i>

                    Email Address

                </label>


                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter your registered email"
                    required
                >

            </div>


            <button
                type="submit"
                name="send_reset"
                class="reset-button"
            >

                <i class="bi bi-send"></i>

                Generate Password Reset Link

            </button>


        </form>


        <div class="back-login">

            <a href="login.php">

                <i class="bi bi-arrow-left"></i>

                Back to Login

            </a>

        </div>


    </div>

</div>



<div class="footer">

    © <?php echo date("Y"); ?>

    Grant Application Tracking and Collaboration Management System

</div>


</body>

</html>