<?php

session_start();
include("db_connect.php");

$message = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $selected_role = $_POST['role'];

    // Find user by email
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");

    mysqli_stmt_bind_param($stmt, "s", $email);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    // Check if account exists
    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        // Verify password
        if (password_verify($password, $user['password'])) {

            // Verify selected role
            if (strcasecmp($selected_role, $user['role']) != 0) {

                $message = "The selected role does not match your registered account role.";

            } else {

                // Create session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                // Redirect according to role
                if ($user['role'] == "Administrator") {

                    header("Location: admin_dashboard.php");
                    exit();

                } else {

                    header("Location: dashboard.php");
                    exit();
                }
            }

        } else {

            $message = "Incorrect password. Please try again.";

        }

    } else {

        $message = "No account was found with that email address.";

    }

    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Grant Application System</title>

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

        * {
            box-sizing: border-box;
        }

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


        /* ==========================
           WELCOME SECTION
        ========================== */

        .welcome-section {

            text-align: center;

            color: white;

            padding: 40px 20px 15px;

        }

        .welcome-section h1 {

            font-size: 32px;

            font-weight: 700;

            margin-bottom: 10px;

        }

        .welcome-section p {

            font-size: 16px;

            margin: 0;

            opacity: 0.9;

        }


        /* ==========================
           LOGIN AREA
        ========================== */

        .login-container {

            flex: 1;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 20px;

        }


        /* ==========================
           LOGIN CARD
        ========================== */

        .login-card {

            width: 100%;

            max-width: 450px;

            background: white;

            border-radius: 20px;

            padding: 35px;

            box-shadow: 0 20px 50px rgba(0,0,0,0.25);

        }


        /* ==========================
           ICON
        ========================== */

        .login-icon {

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

            align-items: center;

            justify-content: center;

            margin: 0 auto 15px;

            font-size: 32px;

        }


        .login-card h2 {

            text-align: center;

            color: #1e293b;

            font-weight: 700;

            margin-bottom: 5px;

        }


        .login-subtitle {

            text-align: center;

            color: #64748b;

            margin-bottom: 25px;

        }


        /* ==========================
           FORM
        ========================== */

        .form-label {

            font-weight: 600;

            color: #334155;

        }


        .form-control,
        .form-select {

            height: 48px;

            border-radius: 10px;

            border: 1px solid #cbd5e1;

        }


        .form-control:focus,
        .form-select:focus {

            border-color: #2563eb;

            box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.15);

        }


        /* ==========================
           LOGIN BUTTON
        ========================== */

        .login-button {

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

            font-size: 16px;

            font-weight: 600;

            transition: 0.3s;

        }


        .login-button:hover {

            transform: translateY(-2px);

            box-shadow: 0 8px 20px rgba(37,99,235,0.3);

        }


        /* ==========================
           FORGOT PASSWORD
        ========================== */

        .forgot-password {

            text-align: right;

            margin-top: 8px;

        }


        .forgot-password a {

            color: #2563eb;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;

        }


        .forgot-password a:hover {

            text-decoration: underline;

        }


        /* ==========================
           REGISTER
        ========================== */

        .register-section {

            text-align: center;

            margin-top: 22px;

            color: #64748b;

        }


        .register-section a {

            color: #2563eb;

            font-weight: 600;

            text-decoration: none;

        }


        .register-section a:hover {

            text-decoration: underline;

        }


        /* ==========================
           ROLE INFORMATION
        ========================== */

        .role-info {

            background: #f1f5f9;

            border-radius: 10px;

            padding: 12px;

            margin-bottom: 20px;

            font-size: 13px;

            color: #475569;

        }


        /* ==========================
           FOOTER
        ========================== */

        .footer {

            text-align: center;

            color: rgba(255,255,255,0.8);

            padding: 15px;

            font-size: 13px;

        }


        /* ==========================
           MOBILE
        ========================== */

        @media (max-width: 576px) {

            .welcome-section h1 {

                font-size: 24px;

            }

            .login-card {

                padding: 25px;

            }

        }

    </style>

</head>


<body>


<!-- ==============================
     WELCOME MESSAGE
============================== -->

<div class="welcome-section">

    <h1>

        <i class="bi bi-award"></i>

        Welcome to the Grant Application System

    </h1>

    <p>

        Grant Application Tracking and Collaboration Management System

    </p>

</div>



<!-- ==============================
     LOGIN SECTION
============================== -->

<div class="login-container">

    <div class="login-card">


        <!-- Login Icon -->

        <div class="login-icon">

            <i class="bi bi-person-lock"></i>

        </div>


        <h2>User Login</h2>

        <p class="login-subtitle">

            Sign in to access your account

        </p>


        <!-- Error Message -->

        <?php if ($message != "") { ?>

            <div class="alert alert-danger">

                <i class="bi bi-exclamation-circle"></i>

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php } ?>


        <!-- Role Information -->

        <div class="role-info">

            <i class="bi bi-info-circle"></i>

            Select the role associated with your registered account.

        </div>



        <!-- ==========================
             LOGIN FORM
        =========================== -->

        <form method="POST">


            <!-- ROLE -->

            <div class="mb-3">

                <label class="form-label">

                    <i class="bi bi-person-badge"></i>

                    Select Role

                </label>


                <select
                    name="role"
                    class="form-select"
                    required
                >

                    <option value="">

                        -- Select your role --

                    </option>


                    <option value="Researcher">

                        Researcher

                    </option>


                    <option value="Administrator">

                        Administrator

                    </option>

                </select>

            </div>



            <!-- EMAIL -->

            <div class="mb-3">

                <label class="form-label">

                    <i class="bi bi-envelope"></i>

                    Email Address

                </label>


                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter your email address"
                    required
                >

            </div>



            <!-- PASSWORD -->

            <div class="mb-3">

                <label class="form-label">

                    <i class="bi bi-lock"></i>

                    Password

                </label>


                <div class="input-group">

                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                    >


                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        onclick="togglePassword()"
                    >

                        <i
                            class="bi bi-eye"
                            id="eyeIcon"
                        ></i>

                    </button>

                </div>


                <!-- Forgot Password -->

                <div class="forgot-password">

                    <a href="forgot_password.php">

                        Forgot Password?

                    </a>

                </div>

            </div>



            <!-- LOGIN BUTTON -->

            <button
                type="submit"
                name="login"
                class="login-button"
            >

                <i class="bi bi-box-arrow-in-right"></i>

                Login

            </button>


        </form>



        <!-- REGISTER -->

        <div class="register-section">

            Don't have an account?

            <a href="register.php">

                Create Account

            </a>

        </div>


    </div>

</div>



<!-- ==============================
     FOOTER
============================== -->

<div class="footer">

    © <?php echo date("Y"); ?>

    Grant Application Tracking and Collaboration Management System

</div>



<!-- ==============================
     PASSWORD TOGGLE
============================== -->

<script>

function togglePassword() {

    const password = document.getElementById("password");

    const eyeIcon = document.getElementById("eyeIcon");


    if (password.type === "password") {

        password.type = "text";

        eyeIcon.classList.remove("bi-eye");

        eyeIcon.classList.add("bi-eye-slash");

    } else {

        password.type = "password";

        eyeIcon.classList.remove("bi-eye-slash");

        eyeIcon.classList.add("bi-eye");

    }

}

</script>


</body>

</html>