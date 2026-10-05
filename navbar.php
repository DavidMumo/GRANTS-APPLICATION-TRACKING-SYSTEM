<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid px-4">

        <a class="navbar-brand fw-bold" href="dashboard.php">
            Grant Application System
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="view_grants.php">
                        Grants
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="my_applications.php">
                        My Applications
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="create_call.php">
                        Create Call
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="view_calls.php">
                        Collaboration Calls
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        My Profile
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-warning fw-bold" href="logout.php">
                        Logout
                    </a>
                </li>

            </ul>

        </div>

    </div>
</nav>