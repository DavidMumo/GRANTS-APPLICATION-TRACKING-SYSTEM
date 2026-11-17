
<?php
session_start();
include("db_connect.php");

// Check whether the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check administrator privileges
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Administrator") {
    die("Access Denied! Administrators only.");
}

// Get dashboard statistics
function getCount(mysqli $conn, string $sql): int {
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Dashboard database error: " . mysqli_error($conn));
    }

    $row = mysqli_fetch_assoc($result);
    return (int) $row['total'];
}

$totalUsers = getCount($conn, "SELECT COUNT(*) AS total FROM users");

$totalGrants = getCount($conn, "SELECT COUNT(*) AS total FROM grants");

$totalGrantApplications = getCount($conn, "SELECT COUNT(*) AS total FROM grant_applications");

$totalCalls = getCount($conn, "SELECT COUNT(*) AS total FROM collaboration_calls");

$totalCollaborationApplications = getCount($conn, "SELECT COUNT(*) AS total FROM collaboration_applications");

$totalShortlisted = getCount($conn, "SELECT COUNT(*) AS total FROM collaboration_applications WHERE status = 'Shortlisted'");

// Administrator name
$adminName = $_SESSION['full_name'] ?? $_SESSION['fullname'] ?? 'Administrator';

/*
|--------------------------------------------------------------------------
| DATABASE-DRIVEN APPLICATION ANALYTICS
|--------------------------------------------------------------------------
*/

// Find the status column in the specified table
function getStatusColumn(mysqli $conn, string $table): string {

    $allowedTables = [
        'grant_applications',
        'collaboration_applications'
    ];

    if (!in_array($table, $allowedTables, true)) {
        die("Invalid table name.");
    }

    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");

    if (!$result) {
        die("Unable to inspect $table: " . mysqli_error($conn));
    }

    $columns = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $columns[] = $row['Field'];
    }

    if (in_array('status', $columns, true)) {
        return 'status';
    }

    if (in_array('application_status', $columns, true)) {
        return 'application_status';
    }

    die("No status or application_status column found in $table.");
}

// Get application totals grouped by status
function getApplicationStatusCounts(mysqli $conn, string $table): array {

    $statusColumn = getStatusColumn($conn, $table);

    $sql = "SELECT `$statusColumn` AS application_status,
                   COUNT(*) AS total
            FROM `$table`
            GROUP BY `$statusColumn`";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Analytics query failed: " . mysqli_error($conn));
    }

    $counts = [
        'Submitted' => 0,
        'Approved' => 0,
        'Pending' => 0,
        'Rejected' => 0,
        'Shortlisted' => 0,
        'Other' => 0
    ];

    while ($row = mysqli_fetch_assoc($result)) {

        $status = strtolower(trim((string) $row['application_status']));
        $total = (int) $row['total'];

        if (in_array($status, ['submitted', 'applied', 'received'], true)) {

            $counts['Submitted'] += $total;

        } elseif ($status === 'approved') {

            $counts['Approved'] += $total;

        } elseif (in_array($status, ['pending', 'under review', 'in review', 'processing'], true)) {

            $counts['Pending'] += $total;

        } elseif (in_array($status, ['rejected', 'declined'], true)) {

            $counts['Rejected'] += $total;

        } elseif ($status === 'shortlisted') {

            $counts['Shortlisted'] += $total;

        } else {

            $counts['Other'] += $total;
        }
    }

    return $counts;
}

// Retrieve actual database statistics
$grantStatusCounts = getApplicationStatusCounts($conn, 'grant_applications');

$collaborationStatusCounts = getApplicationStatusCounts($conn, 'collaboration_applications');

// Grant chart data
$grantChartLabels = [
    'Submitted',
    'Approved',
    'Pending',
    'Rejected',
    'Other'
];

$grantChartValues = [
    $grantStatusCounts['Submitted'],
    $grantStatusCounts['Approved'],
    $grantStatusCounts['Pending'],
    $grantStatusCounts['Rejected'],
    $grantStatusCounts['Other']
];

// Collaboration chart data
$collaborationChartLabels = [
    'Shortlisted',
    'Pending',
    'Rejected',
    'Other'
];

$collaborationChartValues = [
    $collaborationStatusCounts['Shortlisted'],
    $collaborationStatusCounts['Pending'],
    $collaborationStatusCounts['Rejected'],
    $collaborationStatusCounts['Other']
];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Grants Management System</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>

        :root {
            --navy: #14213d;
            --blue: #2563eb;
            --background: #f4f7fb;
            --text: #263449;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--background);
            font-family: 'Segoe UI', Arial, sans-serif;
            color: var(--text);
        }

        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {
            width: 255px;
            background: var(--navy);
            color: white;
            padding: 25px 16px;
            flex-shrink: 0;
        }

        .brand {
            padding: 8px 12px 28px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            margin-bottom: 25px;
        }

        .brand-icon {
            font-size: 30px;
            color: #60a5fa;
            margin-right: 9px;
        }

        .brand h4 {
            display: inline;
            font-size: 18px;
            font-weight: 700;
        }

        .sidebar-label {
            color: #9caec8;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            padding: 10px 13px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 13px;
            color: #d7e1ef;
            text-decoration: none;
            padding: 13px 14px;
            border-radius: 9px;
            margin-bottom: 7px;
            font-size: 14px;
            transition: 0.2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #263b60;
            color: white;
        }

        .sidebar a.active {
            border-left: 4px solid #60a5fa;
        }

        .sidebar a i {
            font-size: 18px;
        }

        .sidebar .logout-link {
            margin-top: 35px;
            color: #ffb4b4;
        }

        /* MAIN CONTENT */

        .main-content {
            flex: 1;
            min-width: 0;
            padding: 28px 35px;
        }

        .topbar {
            background: white;
            padding: 18px 24px;
            border-radius: 13px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 3px 12px rgba(0,0,0,0.04);
            margin-bottom: 30px;
        }

        .topbar h5 {
            margin: 0;
            font-weight: 700;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #e7efff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* ANIMATED HERO BANNER */

        .hero-banner {
            background: linear-gradient(120deg, #14213d, #234c83, #2563eb);
            border-radius: 18px;
            padding: 35px 40px;
            min-height: 270px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            overflow: hidden;
            position: relative;
            color: white;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.16);
        }

        .hero-content {
            position: relative;
            z-index: 3;
            max-width: 520px;
        }

        .hero-tag {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            padding: 7px 13px;
            border-radius: 30px;
            font-size: 11px;
            letter-spacing: 1.5px;
            margin-bottom: 17px;
        }

        .hero-content h2 {
            font-size: 30px;
            font-weight: 750;
            margin-bottom: 12px;
        }

        .hero-content p {
            color: #dbeafe;
            line-height: 1.7;
            font-size: 14px;
        }

        .hero-button {
            display: inline-block;
            background: white;
            color: #1d4ed8;
            text-decoration: none;
            padding: 11px 19px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            margin-top: 10px;
            transition: 0.3s;
        }

        .hero-button:hover {
            background: #dbeafe;
            transform: translateY(-3px);
            color: #14213d;
        }

        /* ANIMATED GRAPHICS */

        .hero-graphics {
            position: relative;
            width: 280px;
            height: 230px;
            flex-shrink: 0;
            margin-right: 35px;
        }

        .center-icon {
            position: absolute;
            width: 85px;
            height: 85px;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            left: 98px;
            top: 70px;
            animation: centerPulse 3s ease-in-out infinite;
            z-index: 2;
        }

        .orbit {
            position: absolute;
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 50%;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
        }

        .orbit-one {
            width: 170px;
            height: 170px;
        }

        .orbit-two {
            width: 240px;
            height: 220px;
        }

        .floating-icon {
            position: absolute;
            width: 52px;
            height: 52px;
            border-radius: 15px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            box-shadow: 0 7px 20px rgba(0,0,0,0.15);
            animation: floating 3s ease-in-out infinite;
            z-index: 3;
        }

        .icon-one {
            color: #2563eb;
            top: 12px;
            left: 105px;
        }

        .icon-two {
            color: #d69b32;
            right: 5px;
            top: 90px;
            animation-delay: 0.5s;
        }

        .icon-three {
            color: #16a36a;
            bottom: 5px;
            left: 55px;
            animation-delay: 1s;
        }

        .icon-four {
            color: #865ac7;
            top: 35px;
            left: 5px;
            animation-delay: 1.5s;
        }

        @keyframes floating {
            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-13px);
            }
        }

        @keyframes centerPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 rgba(255,255,255,0);
            }

            50% {
                transform: scale(1.08);
                box-shadow: 0 0 25px rgba(255,255,255,0.25);
            }
        }

        /* STATISTIC CARDS */

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: white;
            border-radius: 13px;
            padding: 23px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.04);
            border: 1px solid #edf0f5;
            transition: 0.2s;
            animation: cardAppear 0.7s ease both;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .stat-card:nth-child(2) { animation-delay: 0.1s; }
        .stat-card:nth-child(3) { animation-delay: 0.2s; }
        .stat-card:nth-child(4) { animation-delay: 0.3s; }
        .stat-card:nth-child(5) { animation-delay: 0.4s; }
        .stat-card:nth-child(6) { animation-delay: 0.5s; }

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 23px;
        }

        .blue-icon { background: #e8f0ff; color: #2563eb; }
        .green-icon { background: #e4f8ee; color: #16a36a; }
        .orange-icon { background: #fff2df; color: #df8a16; }
        .purple-icon { background: #f0eaff; color: #8054d8; }
        .cyan-icon { background: #e1f7fb; color: #1596aa; }
        .red-icon { background: #ffe8e8; color: #dc4545; }

        .stat-card h3 {
            font-size: 30px;
            font-weight: 750;
            margin: 0 0 5px;
            color: #172b4d;
        }

        .stat-card p {
            color: #718096;
            margin: 0;
            font-size: 14px;
        }

        /* ANALYTICS SECTION */

        .analytics-section {
            margin-top: 35px;
            margin-bottom: 35px;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .analytics-card {
            background: white;
            padding: 24px;
            border-radius: 14px;
            border: 1px solid #edf0f5;
            box-shadow: 0 3px 12px rgba(0,0,0,0.04);
            min-width: 0;
        }

        .analytics-card h5 {
            color: #172b4d;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .analytics-card p {
            color: #718096;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
        }

        /* MANAGEMENT SECTION */

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .section-heading h4 {
            font-weight: 700;
            color: #172b4d;
            margin: 0;
        }

        .management-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .management-card {
            background: white;
            border-radius: 13px;
            padding: 24px;
            border: 1px solid #edf0f5;
            box-shadow: 0 3px 12px rgba(0,0,0,0.04);
            transition: 0.2s;
        }

        .management-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .management-icon {
            font-size: 27px;
            color: #2563eb;
            margin-bottom: 15px;
        }

        .management-card h5 {
            font-weight: 700;
            margin-bottom: 9px;
            color: #172b4d;
        }

        .management-card p {
            color: #718096;
            font-size: 13px;
            min-height: 40px;
        }

        .management-card a {
            display: inline-block;
            background: #edf3ff;
            color: #2563eb;
            text-decoration: none;
            padding: 9px 17px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
        }

        .management-card a:hover {
            background: #2563eb;
            color: white;
        }

        .footer {
            text-align: center;
            color: #8995a7;
            font-size: 12px;
            padding: 30px 0 10px;
        }

        /* RESPONSIVE DESIGN */

        @media (max-width: 1050px) {
            .stats-grid,
            .management-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .main-content {
                padding: 22px;
            }
        }

        @media (max-width: 900px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {

            .dashboard-wrapper {
                display: block;
            }

            .sidebar {
                width: 100%;
                padding: 12px;
            }

            .brand {
                padding-bottom: 12px;
                margin-bottom: 10px;
            }

            .sidebar-label {
                display: none;
            }

            .sidebar a {
                display: inline-flex;
                padding: 9px 10px;
                margin: 3px;
                font-size: 12px;
            }

            .sidebar .logout-link {
                margin-top: 5px;
            }

            .main-content {
                padding: 16px;
            }

            .topbar {
                padding: 15px;
            }

            .stats-grid,
            .management-grid {
                grid-template-columns: 1fr;
                gap: 13px;
            }

            .hero-banner {
                padding: 25px;
            }

            .hero-graphics {
                display: none;
            }

            .hero-content h2 {
                font-size: 24px;
            }

            .chart-container {
                height: 260px;
            }
        }

        /* ACCESSIBILITY */

        @media (prefers-reduced-motion: reduce) {
            .hero-banner *,
            .stat-card,
            .management-card {
                animation: none !important;
                transition: none !important;
            }
        }

    </style>

</head>

<body>

<div class="dashboard-wrapper">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="brand">
            <i class="bi bi-buildings brand-icon"></i>
            <h4>GrantConnect</h4>
        </div>

        <div class="sidebar-label">MAIN MENU</div>

        <a href="admin_dashboard.php" class="active">
            <i class="bi bi-grid"></i>
            Dashboard
        </a>

        <a href="manage_users.php">
            <i class="bi bi-people"></i>
            Manage Users
        </a>

        <a href="manage_grants.php">
            <i class="bi bi-file-earmark-text"></i>
            Manage Grants
        </a>

        <a href="manage_grant_applications.php">
            <i class="bi bi-clipboard-check"></i>
            Grant Applications
        </a>

        <a href="manage_calls.php">
            <i class="bi bi-megaphone"></i>
            Collaboration Calls
        </a>

        <a href="manage_collaboration_applications.php">
            <i class="bi bi-people-fill"></i>
            Collaboration Applications
        </a>

        <div class="sidebar-label mt-4">ACCOUNT</div>

        <a href="logout.php" class="logout-link">
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>

    </aside>


    <!-- MAIN DASHBOARD -->

    <main class="main-content">

        <!-- TOP HEADER -->

        <div class="topbar">

            <div>
                <h5>Administrator Panel</h5>
                <small class="text-secondary">Grants Management System</small>
            </div>

            <div class="admin-profile">

                <div class="profile-icon">
                    <i class="bi bi-person-fill"></i>
                </div>

                <div>
                    <strong><?php echo htmlspecialchars($adminName); ?></strong>
                    <div class="text-secondary small">Administrator</div>
                </div>

            </div>

        </div>


        <!-- ANIMATED DASHBOARD HERO -->

        <section class="hero-banner">

            <div class="hero-content">

                <span class="hero-tag">
                    <i class="bi bi-stars"></i>
                    GRANTS MANAGEMENT SYSTEM
                </span>

                <h2>Administrator Dashboard</h2>

                <p>
                    Manage grant opportunities, monitor applications
                    and connect researchers through collaboration.
                </p>

                <a href="manage_grants.php" class="hero-button">
                    Explore Grants
                    <i class="bi bi-arrow-right"></i>
                </a>

            </div>

            <div class="hero-graphics">

                <div class="orbit orbit-one"></div>
                <div class="orbit orbit-two"></div>

                <div class="floating-icon icon-one">
                    <i class="bi bi-file-earmark-text"></i>
                </div>

                <div class="floating-icon icon-two">
                    <i class="bi bi-award"></i>
                </div>

                <div class="floating-icon icon-three">
                    <i class="bi bi-people"></i>
                </div>

                <div class="floating-icon icon-four">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>

                <div class="center-icon">
                    <i class="bi bi-lightbulb"></i>
                </div>

            </div>

        </section>


        <!-- DASHBOARD STATISTICS -->

        <section class="stats-grid">

            <div class="stat-card">

                <div class="stat-top">
                    <p>Total registered users</p>

                    <div class="stat-icon blue-icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>

                <h3><?php echo $totalUsers; ?></h3>

                <p>Registered system users</p>

            </div>


            <div class="stat-card">

                <div class="stat-top">
                    <p>Available grants</p>

                    <div class="stat-icon green-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                </div>

                <h3><?php echo $totalGrants; ?></h3>

                <p>Grant opportunities in the system</p>

            </div>


            <div class="stat-card">

                <div class="stat-top">
                    <p>Grant applications</p>

                    <div class="stat-icon orange-icon">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                </div>

                <h3><?php echo $totalGrantApplications; ?></h3>

                <p>Submitted grant applications</p>

            </div>


            <div class="stat-card">

                <div class="stat-top">
                    <p>Collaboration calls</p>

                    <div class="stat-icon purple-icon">
                        <i class="bi bi-megaphone"></i>
                    </div>
                </div>

                <h3><?php echo $totalCalls; ?></h3>

                <p>Research collaboration opportunities</p>

            </div>


            <div class="stat-card">

                <div class="stat-top">
                    <p>Collaboration applications</p>

                    <div class="stat-icon cyan-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>
                </div>

                <h3><?php echo $totalCollaborationApplications; ?></h3>

                <p>Applications received</p>

            </div>


            <div class="stat-card">

                <div class="stat-top">
                    <p>Shortlisted members</p>

                    <div class="stat-icon red-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>

                <h3><?php echo $totalShortlisted; ?></h3>

                <p>Successful collaboration shortlists</p>

            </div>

        </section>


        <!-- APPLICATION ANALYTICS -->

        <section class="analytics-section">

            <div class="section-heading">

                <h4>Application Analytics</h4>

                <span class="text-secondary small">
                    Live database statistics
                </span>

            </div>

            <div class="analytics-grid">

                <!-- GRANT APPLICATION CHART -->

                <div class="analytics-card">

                    <h5>
                        <i class="bi bi-file-earmark-bar-graph text-primary"></i>
                        Grant Application Status
                    </h5>

                    <p>
                        Overview of grant applications and their outcomes.
                    </p>

                    <div class="chart-container">
                        <canvas id="grantApplicationsChart"></canvas>
                    </div>

                </div>


                <!-- COLLABORATION APPLICATION CHART -->

                <div class="analytics-card">

                    <h5>
                        <i class="bi bi-people-fill text-primary"></i>
                        Collaboration Application Status
                    </h5>

                    <p>
                        Overview of collaboration applicants and shortlisting.
                    </p>

                    <div class="chart-container">
                        <canvas id="collaborationApplicationsChart"></canvas>
                    </div>

                </div>

            </div>

        </section>


        <!-- SYSTEM MANAGEMENT -->

        <section>

            <div class="section-heading">

                <h4>System Management</h4>

                <span class="text-secondary small">
                    Quick access
                </span>

            </div>

            <div class="management-grid">

                <div class="management-card">

                    <div class="management-icon">
                        <i class="bi bi-people"></i>
                    </div>

                    <h5>Manage Users</h5>

                    <p>
                        View, edit, delete and manage registered user accounts and roles.
                    </p>

                    <a href="manage_users.php">
                        Open <i class="bi bi-arrow-right"></i>
                    </a>

                </div>


                <div class="management-card">

                    <div class="management-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <h5>Manage Grants</h5>

                    <p>
                        Add new grant opportunities, update existing grants and manage records.
                    </p>

                    <a href="manage_grants.php">
                        Open <i class="bi bi-arrow-right"></i>
                    </a>

                </div>


                <div class="management-card">

                    <div class="management-icon">
                        <i class="bi bi-clipboard-check"></i>
                    </div>

                    <h5>Grant Applications</h5>

                    <p>
                        Review submitted grant applications and monitor their progress.
                    </p>

                    <a href="manage_grant_applications.php">
                        Open <i class="bi bi-arrow-right"></i>
                    </a>

                </div>


                <div class="management-card">

                    <div class="management-icon">
                        <i class="bi bi-megaphone"></i>
                    </div>

                    <h5>Collaboration Calls</h5>

                    <p>
                        Create, update and manage research collaboration opportunities.
                    </p>

                    <a href="manage_calls.php">
                        Open <i class="bi bi-arrow-right"></i>
                    </a>

                </div>


                <div class="management-card">

                    <div class="management-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>

                    <h5>Collaboration Applications</h5>

                    <p>
                        Review applicants, manage application statuses and shortlist members.
                    </p>

                    <a href="manage_collaboration_applications.php">
                        Open <i class="bi bi-arrow-right"></i>
                    </a>

                </div>

            </div>

        </section>


        <!-- FOOTER -->

        <div class="footer">

            Grants Application Tracking and Collaboration Management System

            <br>

            Administrator Dashboard

        </div>

    </main>

</div>


<!-- CHART.JS FUNCTIONALITY -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    // Grant application data from MySQL database

    const grantLabels = <?php
        echo json_encode($grantChartLabels);
    ?>;

    const grantValues = <?php
        echo json_encode($grantChartValues);
    ?>;


    // Collaboration application data from MySQL database

    const collaborationLabels = <?php
        echo json_encode($collaborationChartLabels);
    ?>;

    const collaborationValues = <?php
        echo json_encode($collaborationChartValues);
    ?>;


    // GRANT APPLICATION DOUGHNUT CHART

    new Chart(document.getElementById("grantApplicationsChart"), {

        type: "doughnut",

        data: {

            labels: grantLabels,

            datasets: [{

                label: "Grant Applications",

                data: grantValues,

                backgroundColor: [
                    "#2563eb",
                    "#16a36a",
                    "#f59e0b",
                    "#dc4545",
                    "#94a3b8"
                ],

                borderColor: "#ffffff",

                borderWidth: 3

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {
                    position: "bottom"
                },

                tooltip: {
                    enabled: true
                }

            }

        }

    });


    // COLLABORATION APPLICATION BAR CHART

    new Chart(document.getElementById("collaborationApplicationsChart"), {

        type: "bar",

        data: {

            labels: collaborationLabels,

            datasets: [{

                label: "Number of Applications",

                data: collaborationValues,

                backgroundColor: [
                    "#16a36a",
                    "#f59e0b",
                    "#dc4545",
                    "#94a3b8"
                ],

                borderRadius: 7

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            scales: {

                y: {

                    beginAtZero: true,

                    ticks: {
                        precision: 0
                    }

                }

            },

            plugins: {

                legend: {
                    display: false
                }

            }

        }

    });

});

</script>

</body>
</html>
