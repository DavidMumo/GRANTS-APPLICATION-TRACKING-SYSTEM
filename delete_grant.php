
<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db_connect.php");

// =============================================
// ADMINISTRATOR ACCESS CONTROL
// =============================================

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (($_SESSION['role'] ?? '') !== "Administrator") {
    http_response_code(403);
    die("Access Denied!");
}

// =============================================
// ALLOW POST REQUESTS ONLY
// =============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_grants.php");
    exit();
}

// =============================================
// CSRF TOKEN VALIDATION
// =============================================

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )
) {
    die("Invalid request. Please refresh the page and try again.");
}

// =============================================
// VALIDATE GRANT ID
// =============================================

$grant_id = filter_input(
    INPUT_POST,
    'grant_id',
    FILTER_VALIDATE_INT
);

if (!$grant_id || $grant_id < 1) {
    header("Location: manage_grants.php?error=invalid");
    exit();
}

// =============================================
// CHECK WHETHER GRANT EXISTS
// =============================================

$check = mysqli_prepare(
    $conn,
    "SELECT grant_id FROM grants WHERE grant_id = ?"
);

if (!$check) {
    header("Location: manage_grants.php?error=failed");
    exit();
}

mysqli_stmt_bind_param($check, "i", $grant_id);
mysqli_stmt_execute($check);

$checkResult = mysqli_stmt_get_result($check);

if (!$checkResult || mysqli_num_rows($checkResult) === 0) {
    mysqli_stmt_close($check);
    header("Location: manage_grants.php?error=notfound");
    exit();
}

mysqli_stmt_close($check);

// =============================================
// CHECK FOR EXISTING GRANT APPLICATIONS
// =============================================

$applicationCheck = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM grant_applications
     WHERE grant_id = ?"
);

if (!$applicationCheck) {
    header("Location: manage_grants.php?error=failed");
    exit();
}

mysqli_stmt_bind_param($applicationCheck, "i", $grant_id);
mysqli_stmt_execute($applicationCheck);

$applicationResult = mysqli_stmt_get_result($applicationCheck);
$applicationRow = mysqli_fetch_assoc($applicationResult);

mysqli_stmt_close($applicationCheck);

// Preserve grants that have applications
if ((int)$applicationRow['total'] > 0) {
    header("Location: manage_grants.php?error=linked");
    exit();
}

// =============================================
// DELETE GRANT IF NO APPLICATIONS EXIST
// =============================================

$delete = mysqli_prepare(
    $conn,
    "DELETE FROM grants WHERE grant_id = ?"
);

if (!$delete) {
    header("Location: manage_grants.php?error=failed");
    exit();
}

mysqli_stmt_bind_param($delete, "i", $grant_id);

if (mysqli_stmt_execute($delete)) {

    if (mysqli_stmt_affected_rows($delete) > 0) {
        mysqli_stmt_close($delete);
        header("Location: manage_grants.php?success=deleted");
        exit();
    } else {
        mysqli_stmt_close($delete);
        header("Location: manage_grants.php?error=notfound");
        exit();
    }

} else {

    // Catch any other foreign key constraint
    $errorNumber = mysqli_errno($conn);

    mysqli_stmt_close($delete);

    if ($errorNumber == 1451) {
        header("Location: manage_grants.php?error=linked");
        exit();
    }

    header("Location: manage_grants.php?error=failed");
    exit();
}
?>
