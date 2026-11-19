
<?php
session_start();
include("db_connect.php");

// Allow only logged-in Administrators
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== "Administrator"
) {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verify CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $message = "Invalid request. Please refresh the page and try again.";
        $message_type = "danger";

    } else {

        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $institution = trim($_POST['institution'] ?? '');
        $password = $_POST['password'] ?? '';

        if (
            $full_name === '' ||
            $email === '' ||
            $phone_number === '' ||
            $institution === '' ||
            $password === ''
        ) {
            $message = "Please fill in all fields.";
            $message_type = "danger";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
            $message_type = "danger";

        } elseif (!preg_match('/^[0-9]{10}$/', $phone_number)) {
            $message = "Phone number must contain exactly 10 digits.";
            $message_type = "danger";

        } elseif (strlen($password) < 6) {
            $message = "Password must contain at least 6 characters.";
            $message_type = "danger";

        } else {

            // Check whether email already exists
            $check = $conn->prepare(
                "SELECT user_id FROM users WHERE email = ?"
            );
            $check->bind_param("s", $email);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                $message = "This email address is already registered.";
                $message_type = "danger";
            } else {

                // Create Administrator account
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $role = "Administrator";

                $stmt = $conn->prepare(
                    "INSERT INTO users
                    (full_name, email, phone_number, institution, password, role)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "ssssss",
                    $full_name,
                    $email,
                    $phone_number,
                    $institution,
                    $hashed_password,
                    $role
                );

                if ($stmt->execute()) {
                    $message = "Administrator account created successfully.";
                    $message_type = "success";
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $message = "Unable to create the account. Please try again.";
                    $message_type = "danger";
                }

                $stmt->close();
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
    <title>Create Administrator | GrantConnect</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f1f5f9;
            font-family: Arial, sans-serif;
        }

        .admin-card {
            max-width: 650px;
            margin: 45px auto;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        h2 {
            color: #12345a;
            font-weight: bold;
        }

        .form-control {
            padding: 11px;
            margin-bottom: 15px;
        }

        .btn-create {
            background: #12345a;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            border-radius: 7px;
        }

        .btn-create:hover {
            background: #0b2540;
            color: white;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="admin-card">

        <h2 class="text-center mb-2">Create Administrator</h2>

        <p class="text-center text-muted mb-4">
            GrantConnect Administrator Account Registration
        </p>

        <?php if ($message !== ""): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <input type="hidden" name="csrf_token"
                   value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <label>Full Name</label>
            <input type="text" name="full_name"
                   class="form-control" required>

            <label>Email Address</label>
            <input type="email" name="email"
                   class="form-control" required>

            <label>Phone Number</label>
            <input type="text" name="phone_number"
                   class="form-control"
                   pattern="[0-9]{10}"
                   maxlength="10" required>

            <label>Institution</label>
            <input type="text" name="institution"
                   class="form-control" required>

            <label>Password</label>
            <input type="password" name="password"
                   class="form-control"
                   minlength="6" required>

            <label>Account Role</label>
            <input type="text" class="form-control"
                   value="Administrator" readonly>

            <button type="submit" class="btn-create mt-3">
                Create Administrator Account
            </button>

        </form>

        <div class="text-center mt-4">
            <a href="admin_dashboard.php" class="text-decoration-none">
                Back to Admin Dashboard
            </a>
        </div>

    </div>

</div>

</body>
</html>
