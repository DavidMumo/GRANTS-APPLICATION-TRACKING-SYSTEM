
<?php
session_start();
include("db_connect.php");

$message = "";
$message_type = "";

if (isset($_POST['register'])) {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? '');

    // Only Researchers can register publicly.
    // Administrator accounts must be created securely by an admin.

    // Validate required fields
    if (
        $full_name === "" ||
        $email === "" ||
        $phone_number === "" ||
        $institution === "" ||
        $password === "" ||
        $role === ""
    ) {
        $message = "Please complete all required fields.";
        $message_type = "error";
    }
    elseif ($role !== "Researcher") {
        $message = "Invalid role selected. Public registration is for Researchers only.";
        $message_type = "error";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";
    }
    elseif (!preg_match('/^[0-9]{10}$/', $phone_number)) {
        $message = "Phone number must contain exactly 10 digits.";
        $message_type = "error";
    }
    elseif (strlen($password) < 6) {
        $message = "Password must contain at least 6 characters.";
        $message_type = "error";
    }
    else {

        // Check whether email already exists
        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ? LIMIT 1"
        );

        if (!$check) {
            $message = "Unable to process registration. Please try again.";
            $message_type = "error";
        } else {

            $check->bind_param("s", $email);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {

                $message = "This email is already registered. Please log in.";
                $message_type = "error";

            } else {

                // Hash password
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Insert researcher into database
                $insert = $conn->prepare(
                    "INSERT INTO users
                    (full_name, email, phone_number, institution, password, role)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                if (!$insert) {

                    $message = "Unable to prepare registration. Please try again.";
                    $message_type = "error";

                } else {

                    $insert->bind_param(
                        "ssssss",
                        $full_name,
                        $email,
                        $phone_number,
                        $institution,
                        $hashed_password,
                        $role
                    );

                    if ($insert->execute()) {

                        $message = "Registration successful! You can now log in.";
                        $message_type = "success";

                        // Clear submitted form details
                        $_POST = [];

                    } else {

                        $message = "Registration failed. Please try again.";
                        $message_type = "error";
                    }

                    $insert->close();
                }
            }

            $check->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Join GrantConnect | Create Account</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f3f6fc;
    color: #26344a;
}

.page-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background:
        radial-gradient(circle at top left, #dceaff, transparent 40%),
        radial-gradient(circle at bottom right, #d9f0ff, transparent 40%),
        #f5f8fd;
}

.register-container {
    width: 100%;
    max-width: 1050px;
    display: grid;
    grid-template-columns: 0.9fr 1.1fr;
    background: white;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 18px 55px rgba(20, 50, 90, 0.12);
    animation: slideUp 0.8s ease;
}

/* LEFT INFORMATION PANEL */

.welcome-panel {
    background: linear-gradient(145deg, #092c5c, #1262a3);
    color: white;
    padding: 55px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.brand {
    font-size: 27px;
    font-weight: 800;
    margin-bottom: 35px;
}

.brand i {
    margin-right: 10px;
    color: #7dd3fc;
}

.welcome-panel h1 {
    font-size: 34px;
    font-weight: 750;
    line-height: 1.3;
    margin-bottom: 18px;
}

.welcome-panel p {
    color: #d9eaff;
    line-height: 1.8;
    font-size: 15px;
}

.benefits {
    margin-top: 30px;
}

.benefit {
    display: flex;
    align-items: center;
    gap: 13px;
    margin-bottom: 20px;
    color: #f1f7ff;
    font-size: 14px;
}

.benefit i {
    font-size: 21px;
    color: #7dd3fc;
}

/* RIGHT REGISTRATION PANEL */

.form-panel {
    padding: 45px 55px;
}

.form-heading {
    margin-bottom: 28px;
}

.form-heading h2 {
    font-size: 28px;
    font-weight: 750;
    color: #12345a;
}

.form-heading p {
    color: #758397;
    font-size: 14px;
    margin-top: 8px;
}

.form-label {
    font-weight: 650;
    font-size: 13px;
    color: #34465e;
    margin-bottom: 8px;
}

.form-control {
    height: 49px;
    border: 1px solid #dce4ef;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 14px;
    transition: 0.3s ease;
}

.form-control:focus {
    border-color: #1976d2;
    box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.10);
}

select.form-control {
    cursor: pointer;
    background-color: #fff;
}

.input-group .form-control {
    border-radius: 10px 0 0 10px;
}

.password-toggle {
    cursor: pointer;
    background: white;
    border: 1px solid #dce4ef;
    border-left: none;
    border-radius: 0 10px 10px 0;
}

.register-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(100deg, #1262a3, #0879ca);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 15px;
    margin-top: 10px;
    transition: 0.3s ease;
}

.register-btn:hover {
    background: linear-gradient(100deg, #092c5c, #1262a3);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(18, 98, 163, 0.2);
}

.login-link {
    text-align: center;
    margin-top: 24px;
    font-size: 14px;
    color: #758397;
}

.login-link a {
    color: #1262a3;
    font-weight: 700;
    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}

.alert {
    border-radius: 10px;
    font-size: 14px;
}

.footer-note {
    text-align: center;
    color: #9aa7b8;
    font-size: 12px;
    margin-top: 25px;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(25px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media(max-width: 850px) {

    .register-container {
        grid-template-columns: 1fr;
    }

    .welcome-panel {
        padding: 35px;
    }

    .welcome-panel h1 {
        font-size: 27px;
    }

    .form-panel {
        padding: 35px 25px;
    }
}

@media(max-width: 480px) {

    .page-wrapper {
        padding: 15px;
    }

    .welcome-panel,
    .form-panel {
        padding: 28px 22px;
    }
}

</style>

</head>

<body>

<div class="page-wrapper">

<div class="register-container">

    <!-- WELCOME PANEL -->

    <div class="welcome-panel">

        <div class="brand">
            <i class="bi bi-mortarboard-fill"></i>
            GrantConnect
        </div>

        <h1>Connect with Opportunities. Advance Your Research.</h1>

        <p>
            Join a collaborative research community where you can
            discover funding opportunities, track grant applications
            and connect with researchers.
        </p>

        <div class="benefits">

            <div class="benefit">
                <i class="bi bi-check-circle-fill"></i>
                <span>Discover available research grants</span>
            </div>

            <div class="benefit">
                <i class="bi bi-check-circle-fill"></i>
                <span>Track your grant applications</span>
            </div>

            <div class="benefit">
                <i class="bi bi-check-circle-fill"></i>
                <span>Collaborate with other researchers</span>
            </div>

            <div class="benefit">
                <i class="bi bi-check-circle-fill"></i>
                <span>Build stronger research partnerships</span>
            </div>

        </div>

    </div>


    <!-- REGISTRATION FORM -->

    <div class="form-panel">

        <div class="form-heading">

            <h2>Create Your Account</h2>

            <p>
                Register with GrantConnect and start exploring
                research opportunities.
            </p>

        </div>

        <?php if ($message != ""): ?>

            <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST" action="">

            <!-- FULL NAME -->

            <div class="mb-3">

                <label class="form-label">Full Name</label>

                <input
                    type="text"
                    name="full_name"
                    class="form-control"
                    placeholder="Enter your full name"
                    autocomplete="off"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="mb-3">

                <label class="form-label">Email Address</label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
            
                    autocomplete="off"
                    required
                >

            </div>


            <!-- PHONE NUMBER -->

            <div class="mb-3">

                <label class="form-label">Phone Number</label>

                <input
                    type="tel"
                    name="phone_number"
                    class="form-control"
                    placeholder="0712345678"
                    maxlength="10"
                    minlength="10"
                    pattern="[0-9]{10}"
                    inputmode="numeric"
                    autocomplete="off"
                    title="Enter exactly 10 digits"
                    required
                >

            </div>


            <!-- INSTITUTION -->

            <div class="mb-3">

                <label class="form-label">Institution</label>

                <input
                    type="text"
                    name="institution"
                    class="form-control"
                    placeholder="Enter your institution"
                    autocomplete="off"
                    required
                >

            </div>


            <!-- ROLE SELECTION -->

            <div class="mb-3">

                <label class="form-label">Select Your Role</label>

                <select
                    name="role"
                    class="form-control"
                    required
                >

                    <option value="Researcher" selected>
                        Researcher
                    </option>

                </select>

                <small class="text-muted">
                    
                </small>

            </div>


            <!-- PASSWORD -->

            <div class="mb-3">

                <label class="form-label">Password</label>

                <div class="input-group">

                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="Enter at least 6 characters"
                        minlength="6"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        class="btn password-toggle"
                        type="button"
                        onclick="togglePassword()"
                    >
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>

                </div>

            </div>


            <!-- REGISTER BUTTON -->

            <button
                type="submit"
                name="register"
                class="register-btn"
            >

                <i class="bi bi-person-plus-fill me-2"></i>
                Create Account

            </button>

        </form>


        <div class="login-link">

            Already have an account?
            <a href="login.php">Login here</a>

        </div>

        <div class="footer-note">
            © <?php echo date("Y"); ?> GrantConnect.
            Research opportunities made accessible.
        </div>

    </div>

</div>

</div>


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
