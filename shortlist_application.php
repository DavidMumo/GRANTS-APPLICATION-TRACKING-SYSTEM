
<?php
session_start();
include("db_connect.php");

// Check administrator login
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] != "Administrator"
) {
    die("Access Denied!");
}

// Validate application ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid application ID.");
}

$id = intval($_GET['id']);

// Start transaction
mysqli_begin_transaction($conn);

try {

    // Get application information
    $stmt = mysqli_prepare($conn, "
        SELECT call_id, applicant_id, motivation, status
        FROM collaboration_applications
        WHERE application_id = ?
    ");

    if (!$stmt) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $call_id,
        $applicant_id,
        $motivation,
        $status
    );

    $found = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$found) {
        throw new Exception("Application not found.");
    }

    if ($status == "Rejected") {
        throw new Exception("A rejected application cannot be shortlisted.");
    }

    // Check whether applicant is already shortlisted
    $check = mysqli_prepare($conn, "
        SELECT shortlist_id
        FROM shortlisted_members
        WHERE call_id = ? AND applicant_id = ?
        LIMIT 1
    ");

    if (!$check) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param($check, "ii", $call_id, $applicant_id);
    mysqli_stmt_execute($check);
    mysqli_stmt_bind_result($check, $existing_id);

    $exists = mysqli_stmt_fetch($check);

    mysqli_stmt_close($check);

    // Insert into shortlisted_members if not already present
    if (!$exists) {

        $insert = mysqli_prepare($conn, "
            INSERT INTO shortlisted_members
            (call_id, applicant_id, shortlisted_date, remarks)
            VALUES (?, ?, NOW(), ?)
        ");

        if (!$insert) {
            throw new Exception(mysqli_error($conn));
        }

        mysqli_stmt_bind_param(
            $insert,
            "iis",
            $call_id,
            $applicant_id,
            $motivation
        );

        if (!mysqli_stmt_execute($insert)) {
            throw new Exception(mysqli_stmt_error($insert));
        }

        mysqli_stmt_close($insert);
    }

    // Update application status
    $update = mysqli_prepare($conn, "
        UPDATE collaboration_applications
        SET status = 'Shortlisted'
        WHERE application_id = ?
    ");

    if (!$update) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param($update, "i", $id);

    if (!mysqli_stmt_execute($update)) {
        throw new Exception(mysqli_stmt_error($update));
    }

    mysqli_stmt_close($update);

    // Save both operations
    mysqli_commit($conn);

    header("Location: manage_collaboration_applications.php?success=shortlisted");
    exit();

} catch (Exception $e) {

    mysqli_rollback($conn);

    die("Shortlisting Error: " .
        htmlspecialchars($e->getMessage()));
}
?>
