
<?php
session_start();

include("db_connect.php");

// Check whether the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Get total registered users
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$users = mysqli_fetch_assoc($result);
$total_users = $users['total'] ?? 0;

// Get total available grants
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM grants");
$row = mysqli_fetch_assoc($result);
$total_grants = $row['total'] ?? 0;

// Get applications submitted by the logged-in user
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM grant_applications
     WHERE user_id = $user_id"
);
$row = mysqli_fetch_assoc($result);
$total_applications = $row['total'] ?? 0;

// Get total collaboration calls
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM collaboration_calls"
);
$call = mysqli_fetch_assoc($result);
$total_calls = $call['total'] ?? 0;

// Get total collaboration applications
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM collaboration_applications"
);
$row = mysqli_fetch_assoc($result);
$total_collaborations = $row['total'] ?? 0;

// Get total shortlisted members
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM collaboration_applications
     WHERE status = 'Shortlisted'"
);
$row = mysqli_fetch_assoc($result);
$total_shortlisted = $row['total'] ?? 0;

// Get logged-in user's name
$fullname = $_SESSION['full_name']
    ?? $_SESSION['fullname']
    ?? "User";

// Get user role
$role = $_SESSION['role'] ?? "Researcher";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Grant Application System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        body {
            background-color: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .welcome-card {
            border: none;
            border-radius: 12px;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-icon {
            font-size: 30px;
            opacity: 0.85;
        }

        .quick-actions {
            border: none;
            border-radius: 12px;
        }

        .section-title {
            font-weight: 600;
        }

    </style>

</head>

<body>

<!-- Navigation Bar -->
<?php include("navbar.php"); ?>


<div class="container-fluid px-4 py-4">

    <!-- Welcome Section -->
    <div class="card shadow-sm welcome-card mb-4">

        <div class="card-body p-4">

            <h2 class="text-primary fw-bold">

                Welcome,
                <?php echo htmlspecialchars($fullname); ?>

            </h2>

            <p class="text-muted mb-3">

                Welcome to the Grant Application Tracking and
                Collaboration Management System.

            </p>

            <strong>Role:</strong>

            <span class="badge bg-success">

                <?php echo htmlspecialchars($role); ?>

            </span>

        </div>

    </div>


    <!-- Dashboard Statistics -->
    <h4 class="section-title mb-3">System Overview</h4>

    <div class="row">


        <!-- Total Users -->
        <div class="col-md-4 col-lg-4 mb-4">

            <div class="card stat-card shadow-sm border-start border-primary border-5">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h6 class="text-muted">Total Users</h6>

                            <h2 class="fw-bold text-primary">
                                <?php echo $total_users; ?>
                            </h2>

                        </div>

                        <i class="bi bi-people-fill stat-icon text-primary"></i>

                    </div>

                </div>

            </div>

        </div>


        <!-- Available Grants -->
        <div class="col-md-4 col-lg-4 mb-4">

            <div class="card stat-card shadow-sm border-start border-success border-5">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h6 class="text-muted">Available Grants</h6>

                            <h2 class="fw-bold text-success">
                                <?php echo $total_grants; ?>
                            </h2>

                        </div>

                        <i class="bi bi-award-fill stat-icon text-success"></i>

                    </div>

                </div>

            </div>

        </div>


        <!-- My Applications -->
        <div class="col-md-4 col-lg-4 mb-4">

            <div class="card stat-card shadow-sm border-start border-warning border-5">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h6 class="text-muted">My Applications</h6>

                            <h2 class="fw-bold text-warning">
                                <?php echo $total_applications; ?>
                            </h2>

                        </div>

                        <i class="bi bi-file-earmark-text-fill stat-icon text-warning"></i>

                    </div>

                </div>

            </div>

        </div>


        <!-- Collaboration Calls -->
        <div class="col-md-4 col-lg-4 mb-4">

            <div class="card stat-card shadow-sm border-start border-info border-5">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h6 class="text-muted">Collaboration Calls</h6>

                            <h2 class="fw-bold text-info">
                                <?php echo $total_calls; ?>
                            </h2>

                        </div>

                        <i class="bi bi-megaphone-fill stat-icon text-info"></i>

                    </div>

                </div>

            </div>

        </div>


        <!-- Collaboration Applications -->
        <div class="col-md-4 col-lg-4 mb-4">

            <div class="card stat-card shadow-sm border-start border-secondary border-5">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h6 class="text-muted">Collaboration Applications</h6>

                            <h2 class="fw-bold text-secondary">
                                <?php echo $total_collaborations; ?>
                            </h2>

                        </div>

                        <i class="bi bi-person-plus-fill stat-icon text-secondary"></i>

                    </div>

                </div>

            </div>

        </div>


        <!-- Shortlisted -->
        <div class="col-md-4 col-lg-4 mb-4">

            <div class="card stat-card shadow-sm border-start border-danger border-5">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h6 class="text-muted">Shortlisted Members</h6>

                            <h2 class="fw-bold text-danger">
                                <?php echo $total_shortlisted; ?>
                            </h2>

                        </div>

                        <i class="bi bi-check-circle-fill stat-icon text-danger"></i>

                    </div>

                </div>

            </div>

        </div>


    </div>


    <!-- Quick Actions -->
    <div class="card shadow-sm quick-actions mb-4">

        <div class="card-header bg-primary text-white py-3">

            <h5 class="mb-0">

                <i class="bi bi-lightning-fill"></i>
                Quick Actions

            </h5>

        </div>


        <div class="card-body p-4">

            <p class="text-muted">

                Quickly access the main features of the system.

            </p>


            <a href="view_grants.php" class="btn btn-success m-2">

                <i class="bi bi-search"></i>
                View Grants

            </a>


            <a href="my_applications.php" class="btn btn-warning m-2">

                <i class="bi bi-file-earmark-text"></i>
                My Applications

            </a>


            <a href="create_call.php" class="btn btn-info m-2 text-white">

                <i class="bi bi-plus-circle"></i>
                Create Collaboration Call

            </a>


            <a href="view_calls.php" class="btn btn-secondary m-2">

                <i class="bi bi-people"></i>
                View Collaboration Calls

            </a>


            <a href="profile.php" class="btn btn-dark m-2">

                <i class="bi bi-person-circle"></i>
                My Profile

            </a>

        </div>

    </div>


    <!-- System Information -->
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <h5 class="text-primary">

                <i class="bi bi-info-circle-fill"></i>
                About the System

            </h5>

            <p class="text-muted mb-0">

                The Grant Application Tracking and Collaboration
                Management System provides a centralized platform
                for researchers to discover grant opportunities,
                manage grant applications, create collaboration calls,
                apply for research collaborations and shortlist
                potential research group members.

            </p>

        </div>

    </div>


</div>


<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
