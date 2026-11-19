
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

// Check whether a user ID was provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid or missing user ID.");
}

$id = (int) $_GET['id'];

// Prevent an Administrator from deleting their own account
if ($id === (int) $_SESSION['user_id']) {
    echo "<script>
        alert('You cannot delete your own administrator account.');
        window.location='manage_users.php';
    </script>";
    exit();
}

try {

    // Check whether the user exists
    $check = $conn->prepare(
        "SELECT user_id FROM users WHERE user_id = ?"
    );

    $check->bind_param("i", $id);
    $check->execute();

    $result = $check->get_result();

    if ($result->num_rows === 0) {
        echo "<script>
            alert('User not found.');
            window.location='manage_users.php';
        </script>";
        exit();
    }

    $check->close();

    // Check whether the user has collaboration applications
    $applicationCheck = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM collaboration_applications
         WHERE applicant_id = ?"
    );

    $applicationCheck->bind_param("i", $id);
    $applicationCheck->execute();

    $applicationResult = $applicationCheck->get_result();
    $applicationData = $applicationResult->fetch_assoc();

    $applicationCheck->close();

    // Preserve the user's application history
    if ($applicationData['total'] > 0) {

        echo "<script>
            alert('This user cannot be deleted because they have submitted collaboration applications. Their application history must be preserved.');
            window.location='manage_users.php';
        </script>";

        exit();
    }

    // Delete the user if no collaboration applications exist
    $delete = $conn->prepare(
        "DELETE FROM users WHERE user_id = ?"
    );

    $delete->bind_param("i", $id);

    if ($delete->execute()) {

        if ($delete->affected_rows > 0) {
            echo "<script>
                alert('User deleted successfully.');
                window.location='manage_users.php';
            </script>";
        } else {
            echo "<script>
                alert('No user was deleted.');
                window.location='manage_users.php';
            </script>";
        }

    } else {
        echo "Unable to delete user: " .
             htmlspecialchars($delete->error);
    }

    $delete->close();

} catch (mysqli_sql_exception $e) {

    // Handle other foreign key restrictions
    echo "<script>
        alert('This user cannot be deleted because other records in the system depend on this account. Preserve the records and manage the account without deleting it.');
        window.location='manage_users.php';
    </script>";
}
?>
