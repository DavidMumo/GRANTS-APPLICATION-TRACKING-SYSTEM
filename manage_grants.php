
<?php
session_start();
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
// CSRF TOKEN
// =============================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

// =============================================
// SUCCESS AND ERROR MESSAGES
// =============================================

$message = "";
$messageType = "";

if (isset($_GET['success']) && $_GET['success'] === "deleted") {

    $message = "Grant deleted successfully.";
    $messageType = "success";

} elseif (isset($_GET['error'])) {

    switch ($_GET['error']) {

        case "linked":
            $message = "This grant cannot be deleted because it has related applications. Keep the grant record to preserve application history.";
            $messageType = "warning";
            break;

        case "invalid":
            $message = "Invalid grant ID.";
            $messageType = "danger";
            break;

        case "notfound":
            $message = "The selected grant was not found.";
            $messageType = "warning";
            break;

        default:
            $message = "An error occurred while deleting the grant.";
            $messageType = "danger";
    }
}

// =============================================
// SEARCH GRANTS
// =============================================

$search = trim($_GET['search'] ?? '');

if ($search !== '') {

    $searchTerm = "%" . $search . "%";

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM grants
         WHERE title LIKE ?
         OR organization LIKE ?
         OR description LIKE ?
         ORDER BY deadline ASC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

} else {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM grants ORDER BY deadline ASC"
    );
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Include navbar after access checks and processing
include("navbar.php");
?>


<!DOCTYPE html>
<html>

<head>

<title>Manage Grants</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<?php if ($message !== ""): ?>

    <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show">

        <?php echo htmlspecialchars($message); ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

<?php endif; ?>

<h2 class="text-primary">Manage Grants</h2>

<p class="text-muted">
Manage all grant opportunities in the system.
</p>

<form method="GET">

<div class="input-group mb-3">

<input
type="text"
name="search"
class="form-control"
placeholder="Search grants..."
value="<?php echo htmlspecialchars($search); ?>">

<button class="btn btn-primary">

Search

</button>

</div>

</form>

<a href="add_grant.php" class="btn btn-success mb-3">

+ Add New Grant

</a>

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Title</th>
<th> Organization</th>
<th>description</th>
<th>Deadline</th>
<th>Status</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['grant_id']; ?></td>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['organization']; ?></td>

<td><?php echo $row['description']; ?></td>

<td><?php echo $row['deadline']; ?></td>

<td>

<?php

if($row['status']=="Open")
{
    echo "<span class='badge bg-success'>Open</span>";
}
else
{
    echo "<span class='badge bg-danger'>Closed</span>";
}

?>

</td>

<td>

<a href="edit_grant.php?id=<?php echo $row['grant_id']; ?>" class="btn btn-warning btn-sm">
Edit
</a>


<form
    method="POST"
    action="delete_grant.php"
    style="display:inline;"
    onsubmit="return confirm('Are you sure you want to delete this grant? This action cannot be undone.');">

    <input
        type="hidden"
        name="grant_id"
        value="<?php echo (int)$row['grant_id']; ?>"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?php echo htmlspecialchars($csrfToken); ?>"
    >

    <button type="submit" class="btn btn-danger btn-sm">
        <i class="bi bi-trash"></i> Delete
    </button>

</form>



</td>

</tr>

<?php } ?>

</tbody>

</table>

<a href="admin_dashboard.php" class="btn btn-secondary">

Back to Dashboard

</a>

</div>

</body>

</html>