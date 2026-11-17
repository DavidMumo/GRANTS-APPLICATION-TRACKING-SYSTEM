
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

/* ===================================
   RESEARCHER DASHBOARD HERO
=================================== */

.research-hero {
    background: linear-gradient(120deg, #14213d, #1e497d, #2563eb);
    color: white;
    min-height: 300px;
    border-radius: 18px;
    padding: 35px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(37, 99, 235, 0.15);
}

.hero-text {
    max-width: 570px;
    position: relative;
    z-index: 3;
}

.hero-label {
    display: inline-block;
    background: rgba(255,255,255,0.16);
    border-radius: 30px;
    padding: 8px 14px;
    font-size: 11px;
    letter-spacing: 1.5px;
    margin-bottom: 18px;
}

.hero-text h2 {
    font-size: 30px;
    font-weight: 750;
    margin-bottom: 13px;
}

.hero-text p {
    color: #dbeafe;
    font-size: 14px;
    line-height: 1.8;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 20px;
}

.hero-btn {
    display: inline-block;
    background: white;
    color: #1d4ed8;
    text-decoration: none;
    padding: 11px 17px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    transition: 0.3s;
}

.hero-btn:hover {
    background: #dbeafe;
    color: #14213d;
    transform: translateY(-3px);
}

.secondary-btn {
    background: rgba(255,255,255,0.15);
    color: white;
    border: 1px solid rgba(255,255,255,0.5);
}

.secondary-btn:hover {
    background: white;
    color: #1d4ed8;
}

.role-label {
    display: block;
    margin-top: 18px;
    color: #dbeafe;
    font-size: 12px;
}

/* GRAPHIC AREA */

.research-art {
    width: 290px;
    height: 245px;
    position: relative;
    flex-shrink: 0;
    margin-right: 25px;
}

.art-center {
    position: absolute;
    left: 105px;
    top: 80px;
    width: 85px;
    height: 85px;
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.5);
    border-radius: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 40px;
    z-index: 2;
    animation: researchPulse 3s infinite ease-in-out;
}

.art-orbit {
    position: absolute;
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 50%;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.orbit-a {
    width: 175px;
    height: 175px;
}

.orbit-b {
    width: 255px;
    height: 235px;
}

.art-icon {
    position: absolute;
    height: 53px;
    width: 53px;
    background: white;
    border-radius: 15px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 25px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.18);
    z-index: 3;
    animation: researchFloat 3s infinite ease-in-out;
}

.art-one {
    color: #2563eb;
    left: 115px;
    top: 8px;
}

.art-two {
    color: #d69b32;
    right: 0;
    top: 95px;
    animation-delay: 0.6s;
}

.art-three {
    color: #16a36a;
    left: 55px;
    bottom: 0;
    animation-delay: 1.2s;
}

.art-four {
    color: #865ac7;
    left: 0;
    top: 35px;
    animation-delay: 1.8s;
}

@keyframes researchFloat {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-13px);
    }
}

@keyframes researchPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.09);
        box-shadow: 0 0 25px rgba(255,255,255,0.3);
    }
}

/* STATISTICS ANIMATION */

.stat-card {
    animation: statisticAppear 0.7s ease both;
}

.stat-card:nth-child(2) { animation-delay: 0.1s; }
.stat-card:nth-child(3) { animation-delay: 0.2s; }
.stat-card:nth-child(4) { animation-delay: 0.3s; }
.stat-card:nth-child(5) { animation-delay: 0.4s; }
.stat-card:nth-child(6) { animation-delay: 0.5s; }

@keyframes statisticAppear {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* QUICK ACTION HOVER EFFECTS */

.quick-actions {
    transition: 0.3s ease;
}

.quick-actions .btn {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.quick-actions .btn:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}

/* MOBILE RESPONSIVENESS */

@media (max-width: 768px) {
    .research-hero {
        padding: 25px;
        min-height: 250px;
    }

    .research-art {
        display: none;
    }

    .hero-text h2 {
        font-size: 24px;
    }
}

/* ACCESSIBILITY */

@media (prefers-reduced-motion: reduce) {
    .research-hero *,
    .stat-card,
    .quick-actions .btn {
        animation: none !important;
        transition: none !important;
    }
}


    </style>

</head>

<body>

<!-- Navigation Bar -->
<?php include("navbar.php"); ?>


<div class="container-fluid px-4 py-4">

    <!-- Welcome Section -->
    
<!-- ANIMATED RESEARCHER HERO -->
<div class="research-hero mb-4">

    <div class="hero-text">

        <span class="hero-label">
            <i class="bi bi-stars"></i>
            RESEARCHER WORKSPACE
        </span>

        <h2>Research & Funding Opportunities</h2>

        <p>
            Discover funding opportunities, manage your grant applications
            and connect with researchers for meaningful collaborations.
        </p>

        <div class="hero-actions">
            <a href="view_grants.php" class="hero-btn">
                <i class="bi bi-search"></i>
                Explore Grants
            </a>

            <a href="view_calls.php" class="hero-btn secondary-btn">
                <i class="bi bi-people"></i>
                Find Collaborations
            </a>
        </div>

        <span class="role-label">
            <i class="bi bi-person-circle"></i>
            <?php echo htmlspecialchars($role); ?>
        </span>

    </div>

    <div class="research-art">

        <div class="art-orbit orbit-a"></div>
        <div class="art-orbit orbit-b"></div>

        <div class="art-center">
            <i class="bi bi-lightbulb-fill"></i>
        </div>

        <div class="art-icon art-one">
            <i class="bi bi-file-earmark-text-fill"></i>
        </div>

        <div class="art-icon art-two">
            <i class="bi bi-award-fill"></i>
        </div>

        <div class="art-icon art-three">
            <i class="bi bi-people-fill"></i>
        </div>

        <div class="art-icon art-four">
            <i class="bi bi-graph-up-arrow"></i>
        </div>

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
