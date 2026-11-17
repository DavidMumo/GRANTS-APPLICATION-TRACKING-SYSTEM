
<?php
session_start();
include("db_connect.php");

$message = "";
$email = "";

// Process login form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $selected_role = $_POST['role'] ?? '';

    if ($email === '' || $password === '' || $selected_role === '') {

        $message = "Please complete all login fields.";

    } else {

        // Find user using email
        $stmt = mysqli_prepare(
            $conn,
            "SELECT * FROM users WHERE email = ? LIMIT 1"
        );

        if (!$stmt) {

            $message = "Unable to process your login. Please try again.";

        } else {

            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) === 1) {

                $user = mysqli_fetch_assoc($result);

                // Verify password
                if (password_verify($password, $user['password'])) {

                    // Verify selected role
                    if (
                        strcasecmp(
                            $selected_role,
                            $user['role']
                        ) !== 0
                    ) {

                        $message = "The selected role does not match your registered account role.";

                    } else {

                        // Regenerate session ID
                        session_regenerate_id(true);

                        // Store session information
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['fullname'] = $user['full_name'];
                        $_SESSION['role'] = $user['role'];

                        mysqli_stmt_close($stmt);

                        // Redirect according to role
                        if (
                            strcasecmp(
                                $user['role'],
                                "Administrator"
                            ) === 0
                        ) {

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
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | GrantConnect</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f3f6fc;
            color: #1e293b;
        }

        /* =================================
           MAIN BACKGROUND
        ================================= */

        .page-wrapper {
            min-height: 100vh;

            background:
                radial-gradient(
                    circle at 10% 10%,
                    rgba(96,165,250,0.15),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 90% 90%,
                    rgba(139,92,246,0.12),
                    transparent 30%
                ),
                #f3f6fc;

            display: flex;
            flex-direction: column;
        }

        /* =================================
           NAVIGATION
        ================================= */

        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 7%;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 23px;
            font-weight: 800;
            color: #173b80;
        }

        .brand-icon {
            width: 45px;
            height: 45px;

            display: flex;
            justify-content: center;
            align-items: center;

            background: linear-gradient(
                135deg,
                #2563eb,
                #7c3aed
            );

            color: white;
            border-radius: 13px;
            font-size: 23px;
        }

        .nav-text {
            font-size: 13px;
            color: #64748b;
        }

        /* =================================
           MAIN CONTENT
        ================================= */

        .main-content {
            flex: 1;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 35px 7%;
        }

        .content-grid {
            width: 100%;
            max-width: 1200px;

            display: grid;

            grid-template-columns: 1.1fr 0.9fr;

            gap: 70px;

            align-items: center;
        }

        /* =================================
           WELCOME SECTION
        ================================= */

        .welcome-section {
            padding: 20px 0;
        }

        .welcome-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            padding: 9px 16px;

            background: #e7efff;
            color: #2454ae;

            border-radius: 30px;

            font-size: 13px;
            font-weight: 700;

            margin-bottom: 22px;
        }

        .welcome-section h1 {
            font-size: clamp(34px, 4vw, 52px);
            line-height: 1.15;
            font-weight: 800;
            color: #142b50;
            margin-bottom: 20px;
        }

        .welcome-section h1 span {
            color: #3569d4;
        }

        .welcome-description {
            color: #64748b;
            font-size: 16px;
            line-height: 1.8;
            max-width: 530px;
            margin-bottom: 30px;
        }

        /* =================================
           INTERACTIVE FEATURE CARDS
        ================================= */

        .feature-list {
            display: grid;

            grid-template-columns: repeat(
                2,
                minmax(0, 1fr)
            );

            gap: 14px;

            max-width: 600px;
        }

        .feature-item {
            background: white;

            border: 1px solid #e6ebf5;

            border-radius: 13px;

            padding: 15px;

            box-shadow: 0 5px 15px rgba(15,23,42,0.035);

            transition: all 0.3s ease;
        }

        .feature-item:hover {
            border-color: #9bb9f5;

            box-shadow: 0 8px 20px rgba(37,99,235,0.09);

            transform: translateY(-3px);
        }

        .feature-toggle {
            width: 100%;

            display: flex;
            align-items: center;
            gap: 12px;

            border: none;
            background: transparent;

            padding: 0;

            text-align: left;

            cursor: pointer;
        }

        .feature-toggle:focus-visible {
            outline: 2px solid #3569d4;
            outline-offset: 4px;
            border-radius: 5px;
        }

        .feature-icon {
            width: 43px;
            height: 43px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 12px;

            background: #edf3ff;
            color: #3569d4;

            font-size: 21px;
        }

        .feature-title {
            flex: 1;

            font-size: 13px;
            font-weight: 750;

            color: #243b60;
        }

        .toggle-arrow {
            color: #3569d4;
            font-size: 13px;

            transition: transform 0.3s ease;
        }

        .feature-toggle[aria-expanded="true"] .toggle-arrow {
            transform: rotate(180deg);
        }

        .feature-description {
            padding: 13px 5px 3px 55px;

            color: #718096;

            font-size: 12px;
            line-height: 1.7;
        }

        /* =================================
           LOGIN CARD
        ================================= */

        .login-card {
            background: white;

            width: 100%;
            max-width: 440px;

            margin: auto;

            padding: 38px;

            border-radius: 23px;

            border: 1px solid #e8edf5;

            box-shadow: 0 22px 65px rgba(30,58,110,0.11);
        }

        .login-icon {
            width: 72px;
            height: 72px;

            margin: 0 auto 18px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 21px;

            background: linear-gradient(
                135deg,
                #2563eb,
                #7c3aed
            );

            color: white;

            font-size: 32px;

            box-shadow: 0 9px 22px rgba(37,99,235,0.25);
        }

        .login-card h2 {
            text-align: center;

            font-weight: 800;
            font-size: 27px;

            color: #172b4d;

            margin-bottom: 7px;
        }

        .login-subtitle {
            text-align: center;

            font-size: 14px;
            color: #8490a3;

            margin-bottom: 28px;
        }

        /* =================================
           LOGIN FORM
        ================================= */

        .form-label {
            color: #344563;
            font-weight: 650;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            min-height: 49px;

            border: 1px solid #dce3ef;

            border-radius: 10px;

            font-size: 14px;

            background-color: #fbfcff;
        }

        .form-control {
            padding: 12px 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #4779e6;

            background: white;

            box-shadow: 0 0 0 4px rgba(71,121,230,0.10);
        }

        .input-group .form-control {
            border-radius: 10px 0 0 10px;
        }

        .input-group .btn {
            border-radius: 0 10px 10px 0;

            border-color: #dce3ef;

            background: #fbfcff;
        }

        /* =================================
           FORGOT PASSWORD
        ================================= */

        .forgot-password {
            text-align: right;
            margin-top: 9px;
        }

        .forgot-password a {
            color: #3569d4;

            font-size: 12px;
            font-weight: 650;

            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        /* =================================
           LOGIN BUTTON
        ================================= */

        .login-button {
            width: 100%;
            min-height: 51px;

            border: none;
            border-radius: 11px;

            color: white;

            font-weight: 700;
            font-size: 15px;

            background: linear-gradient(
                110deg,
                #2563eb,
                #7045d8
            );

            box-shadow: 0 8px 18px rgba(37,99,235,0.20);

            transition: 0.3s ease;
        }

        .login-button:hover {
            transform: translateY(-2px);

            box-shadow: 0 12px 25px rgba(37,99,235,0.28);
        }

        /* =================================
           REGISTER SECTION
        ================================= */

        .register-section {
            text-align: center;

            margin-top: 24px;

            font-size: 13px;

            color: #7c8799;
        }

        .register-section a {
            color: #3569d4;

            font-weight: 750;

            text-decoration: none;
        }

        .register-section a:hover {
            text-decoration: underline;
        }

        .security-note {
            text-align: center;

            font-size: 11px;

            color: #94a0b1;

            margin-top: 22px;
        }

        /* =================================
           FOOTER
        ================================= */

        .footer {
            text-align: center;

            padding: 18px;

            color: #8994a6;

            font-size: 12px;
        }

        /* =================================
           TABLET RESPONSIVENESS
        ================================= */

        @media (max-width: 900px) {

            .content-grid {
                grid-template-columns: 1fr;
                gap: 35px;
            }

            .welcome-section {
                text-align: center;
            }

            .welcome-description,
            .feature-list {
                margin-left: auto;
                margin-right: auto;
            }

            .login-card {
                max-width: 500px;
            }

        }

        /* =================================
           MOBILE RESPONSIVENESS
        ================================= */

        @media (max-width: 576px) {

            .top-navbar {
                padding: 16px 5%;
            }

            .brand {
                font-size: 19px;
            }

            .nav-text {
                display: none;
            }

            .main-content {
                padding: 20px 5%;
            }

            .welcome-section h1 {
                font-size: 34px;
            }

            .welcome-description {
                font-size: 14px;
            }

            .feature-list {
                grid-template-columns: 1fr;
            }

            .login-card {
                padding: 27px 22px;
                border-radius: 18px;
            }

        }

    </style>

</head>

<body>

<div class="page-wrapper">

    <!-- =================================
         NAVIGATION
    ================================== -->

    <nav class="top-navbar">

        <div class="brand">

            <div class="brand-icon">
                <i class="bi bi-award-fill"></i>
            </div>

            GrantConnect

        </div>

        <div class="nav-text">
            Research Funding & Collaboration Platform
        </div>

    </nav>


    <!-- =================================
         MAIN CONTENT
    ================================== -->

    <main class="main-content">

        <div class="content-grid">


            <!-- =================================
                 WELCOME SECTION
            ================================== -->

            <section class="welcome-section">

                <div class="welcome-label">

                    <i class="bi bi-stars"></i>

                    Empowering Research Through Collaboration

                </div>


                <h1>

                    Discover Grants.<br>

                    Build Research.<br>

                    <span>Make an Impact.</span>

                </h1>


                <p class="welcome-description">

                    Welcome to the Grant Application Tracking and
                    Collaboration Management System. A central platform
                    designed to help researchers discover funding
                    opportunities, manage grant applications and connect
                    with potential research collaborators.

                </p>


                <!-- =================================
                     INTERACTIVE FEATURES
                ================================== -->

                <div class="feature-list" id="grantFeatures">


                    <!-- DISCOVER GRANTS -->

                    <div class="feature-item">

                        <button
                            class="feature-toggle"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#grantsInfo"
                            aria-expanded="false"
                            aria-controls="grantsInfo"
                        >

                            <span class="feature-icon">

                                <i class="bi bi-search"></i>

                            </span>

                            <span class="feature-title">
                                Discover Grants
                            </span>

                            <i class="bi bi-chevron-down toggle-arrow"></i>

                        </button>


                        <div
                            class="collapse feature-description"
                            id="grantsInfo"
                            data-bs-parent="#grantFeatures"
                        >

                            Explore research funding opportunities,
                            review eligibility requirements and find
                            grants that match your research interests.

                        </div>

                    </div>


                    <!-- TRACK APPLICATIONS -->

                    <div class="feature-item">

                        <button
                            class="feature-toggle"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#trackingInfo"
                            aria-expanded="false"
                            aria-controls="trackingInfo"
                        >

                            <span class="feature-icon">

                                <i class="bi bi-clipboard-data"></i>

                            </span>

                            <span class="feature-title">
                                Track Applications
                            </span>

                            <i class="bi bi-chevron-down toggle-arrow"></i>

                        </button>


                        <div
                            class="collapse feature-description"
                            id="trackingInfo"
                            data-bs-parent="#grantFeatures"
                        >

                            Monitor submitted grant applications,
                            follow their progress and keep track
                            of pending, approved and rejected outcomes.

                        </div>

                    </div>


                    <!-- RESEARCH COLLABORATION -->

                    <div class="feature-item">

                        <button
                            class="feature-toggle"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collaborationInfo"
                            aria-expanded="false"
                            aria-controls="collaborationInfo"
                        >

                            <span class="feature-icon">

                                <i class="bi bi-people"></i>

                            </span>

                            <span class="feature-title">
                                Research Collaboration
                            </span>

                            <i class="bi bi-chevron-down toggle-arrow"></i>

                        </button>


                        <div
                            class="collapse feature-description"
                            id="collaborationInfo"
                            data-bs-parent="#grantFeatures"
                        >

                            Connect with fellow researchers, create
                            collaboration calls and build teams
                            for joint research opportunities.

                        </div>

                    </div>


                    <!-- MEMBER SHORTLISTING -->

                    <div class="feature-item">

                        <button
                            class="feature-toggle"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#shortlistingInfo"
                            aria-expanded="false"
                            aria-controls="shortlistingInfo"
                        >

                            <span class="feature-icon">

                                <i class="bi bi-person-check"></i>

                            </span>

                            <span class="feature-title">
                                Member Shortlisting
                            </span>

                            <i class="bi bi-chevron-down toggle-arrow"></i>

                        </button>


                        <div
                            class="collapse feature-description"
                            id="shortlistingInfo"
                            data-bs-parent="#grantFeatures"
                        >

                            Review collaboration applicants, identify
                            suitable research partners and shortlist
                            members for collaborative projects.

                        </div>

                    </div>


                </div>

            </section>


            <!-- =================================
                 LOGIN CARD
            ================================== -->

            <section class="login-card">


                <div class="login-icon">

                    <i class="bi bi-person-lock"></i>

                </div>


                <h2>Welcome Back</h2>


                <p class="login-subtitle">

                    Sign in to continue to your workspace

                </p>


                <!-- ERROR MESSAGE -->

                <?php if ($message !== "") { ?>

                    <div class="alert alert-danger">

                        <i class="bi bi-exclamation-circle me-2"></i>

                        <?php echo htmlspecialchars($message); ?>

                    </div>

                <?php } ?>


                <!-- =================================
                     LOGIN FORM
                ================================== -->

                <form
                    method="POST"
                    action="login.php"
                    autocomplete="off"
                >


                    <!-- ACCOUNT ROLE -->

                    <div class="mb-3">

                        <label
                            class="form-label"
                            for="role"
                        >

                            <i class="bi bi-person-badge me-1"></i>

                            Account Role

                        </label>


                        <select
                            name="role"
                            id="role"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select your role
                            </option>


                            <option
                                value="Researcher"
                                <?php
                                echo (
                                    ($_POST['role'] ?? '') === 'Researcher'
                                ) ? 'selected' : '';
                                ?>
                            >

                                Researcher

                            </option>


                            <option
                                value="Administrator"
                                <?php
                                echo (
                                    ($_POST['role'] ?? '') === 'Administrator'
                                ) ? 'selected' : '';
                                ?>
                            >

                                Administrator

                            </option>

                        </select>

                    </div>


                    <!-- EMAIL ADDRESS -->

                    <div class="mb-3">

                        <label
                            class="form-label"
                            for="email"
                        >

                            <i class="bi bi-envelope me-1"></i>

                            Email Address

                        </label>


                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            placeholder="Enter your email address"
                            autocomplete="off"
                            value="<?php echo htmlspecialchars($email); ?>"
                            required
                        >

                    </div>


                    <!-- PASSWORD -->

                    <div class="mb-3">

                        <label
                            class="form-label"
                            for="password"
                        >

                            <i class="bi bi-lock me-1"></i>

                            Password

                        </label>


                        <div class="input-group">

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Enter your password"
                                autocomplete="new-password"
                                required
                            >


                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                onclick="togglePassword()"
                                aria-label="Show or hide password"
                            >

                                <i
                                    class="bi bi-eye"
                                    id="eyeIcon"
                                ></i>

                            </button>

                        </div>


                        <!-- FORGOT PASSWORD -->

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

                        <i class="bi bi-box-arrow-in-right me-2"></i>

                        Sign In to Your Account

                    </button>


                </form>


                <!-- REGISTER -->

                <div class="register-section">

                    New to GrantConnect?

                    <a href="register.php">

                        Create an Account

                    </a>

                </div>


                <!-- SECURITY NOTE -->

                <div class="security-note">

                    <i class="bi bi-shield-lock me-1"></i>

                    Secure access to your research workspace

                </div>


            </section>


        </div>

    </main>


    <!-- =================================
         FOOTER
    ================================== -->

    <footer class="footer">

        © <?php echo date("Y"); ?>

        Grant Application Tracking and Collaboration Management System.

        All Rights Reserved.

    </footer>


</div>


<!-- =================================
     BOOTSTRAP JAVASCRIPT
================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- =================================
     PASSWORD VISIBILITY
================================== -->

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
