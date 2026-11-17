
<?php
session_start();
include("db_connect.php");

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Administrator access only
if (!isset($_SESSION['role']) ||
    $_SESSION['role'] != "Administrator") {
    die("Access Denied!");
}

// Search functionality
$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if ($search != "") {

    $sql = "SELECT
                collaboration_calls.*,
                users.full_name
            FROM collaboration_calls
            INNER JOIN users
                ON collaboration_calls.created_by = users.user_id
            WHERE collaboration_calls.project_title LIKE ?
               OR collaboration_calls.research_area LIKE ?
            ORDER BY collaboration_calls.date_created DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Database Error: " . mysqli_error($conn));
    }

    $searchTerm = "%" . $search . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $searchTerm,
        $searchTerm
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    $sql = "SELECT
                collaboration_calls.*,
                users.full_name
            FROM collaboration_calls
            INNER JOIN users
                ON collaboration_calls.created_by = users.user_id
            ORDER BY collaboration_calls.date_created DESC";

    $result = mysqli_query($conn, $sql);
}

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Collaboration Calls</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body>

<?php include("navbar.php"); ?>

<div class="container mt-4">

    <h2 class="text-primary mb-4">
        Manage Collaboration Calls
    </h2>

    <?php if (isset($_GET['success']) &&
              $_GET['success'] == "deleted") { ?>

        <div class="alert alert-success alert-dismissible fade show">
            Collaboration Call deleted successfully.

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>
        </div>

    <?php } ?>

    <!-- Search Form -->
    <form method="GET" class="mb-4">

        <div class="input-group">

            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Search project title or research area"
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit" class="btn btn-primary">
                Search
            </button>

            <a href="manage_calls.php" class="btn btn-secondary">
                Reset
            </a>

        </div>

    </form>

    <!-- Collaboration Calls Table -->
    <div class="table-responsive">

        <table class="table table-bordered table-hover">

            <thead class="table-dark">

                <tr>
                    <th>ID</th>
                    <th>Project</th>
                    <th>Research Area</th>
                    <th>Members</th>
                    <th>Deadline</th>
                    <th>Created By</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                <?php if (mysqli_num_rows($result) > 0) { ?>

                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($row['call_id']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['project_title']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['research_area']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['required_members']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['deadline']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['full_name']); ?>
                            </td>

                            <td>

                                <a
                                    href="edit_call.php?id=<?php echo $row['call_id']; ?>"
                                    class="btn btn-warning btn-sm mb-1">
                                    Edit
                                </a>

                                <a
                                    href="delete_call.php?id=<?php echo $row['call_id']; ?>"
                                    class="btn btn-danger btn-sm mb-1"
                                    onclick="return confirm('Delete this collaboration call?')">
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            No collaboration calls found.
                        </td>
                    </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>

    <a href="admin_dashboard.php" class="btn btn-secondary">
        Back to Dashboard
    </a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
?>
