
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
// CSRF PROTECTION
// =============================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];
$message = "";
$messageType = "info";

// =============================================
// FORM SUBMISSION
// =============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['save_grant'])) {

    if (!hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )) {
        die("Invalid request. Please refresh the page.");
    }

    $title = trim($_POST['title'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $fundingAmountInput = trim($_POST['funding_amount'] ?? '');
    $fundingCurrency = strtoupper(trim($_POST['funding_currency'] ?? 'KES'));
    $deadlineInput = trim($_POST['deadline'] ?? '');
    $eligibility = trim($_POST['eligibility'] ?? '');
    $sourceName = trim($_POST['source_name'] ?? '');
    $sourceUrl = trim($_POST['source_url'] ?? '');
    $status = trim($_POST['status'] ?? 'Open');

    // =============================================
    // VALIDATION
    // =============================================

    if (
        $title === '' ||
        $organization === '' ||
        $sourceName === '' ||
        $sourceUrl === ''
    ) {
        $message = "Please complete all required fields.";
        $messageType = "danger";

    } elseif (strlen($title) > 200 || strlen($organization) > 150) {

        $message = "The grant title or organization name is too long.";
        $messageType = "danger";

    } elseif (strlen($sourceName) > 150 || strlen($sourceUrl) > 500) {

        $message = "The source name or URL is too long.";
        $messageType = "danger";

    } elseif (
        !filter_var($sourceUrl, FILTER_VALIDATE_URL) ||
        strtolower((string)parse_url($sourceUrl, PHP_URL_SCHEME)) !== 'https'
    ) {

        $message = "Please provide a valid HTTPS grant website URL.";
        $messageType = "danger";

    } elseif (!preg_match('/^[A-Z]{3,10}$/', $fundingCurrency)) {

        $message = "Enter a valid currency code, for example KES, USD or AUD.";
        $messageType = "danger";

    } elseif (
        $fundingAmountInput !== '' &&
        (!is_numeric($fundingAmountInput) || (float)$fundingAmountInput < 0)
    ) {

        $message = "Enter a valid non-negative funding amount.";
        $messageType = "danger";

    } elseif (
        !in_array($status, ['Open', 'Closed'], true)
    ) {

        $message = "Invalid grant status selected.";
        $messageType = "danger";

    } else {

        // Validate optional deadline
        $deadline = null;

        if ($deadlineInput !== '') {

            $dateObject = DateTime::createFromFormat('Y-m-d', $deadlineInput);

            if (!$dateObject || $dateObject->format('Y-m-d') !== $deadlineInput) {

                $message = "Please enter a valid deadline.";
                $messageType = "danger";

            } else {

                $deadline = $deadlineInput;
            }
        }

        if ($message === '') {

            $fundingAmount = $fundingAmountInput === ''
                ? null
                : $fundingAmountInput;

            // =============================================
            // CHECK FOR DUPLICATE SOURCE URL
            // =============================================

            $check = mysqli_prepare(
                $conn,
                "SELECT grant_id FROM grants WHERE source_url = ? LIMIT 1"
            );

            if (!$check) {

                $message = "Unable to check existing grants.";
                $messageType = "danger";

            } else {

                mysqli_stmt_bind_param($check, "s", $sourceUrl);
                mysqli_stmt_execute($check);

                $existing = mysqli_stmt_get_result($check);

                if ($existing && mysqli_num_rows($existing) > 0) {

                    $message = "This grant website URL has already been imported.";
                    $messageType = "warning";

                } else {

                    // =============================================
                    // SAVE GRANT
                    // =============================================

                    $insert = mysqli_prepare(
                        $conn,
                        "INSERT INTO grants
                        (
                            title,
                            organization,
                            description,
                            funding_amount,
                            funding_currency,
                            deadline,
                            eligibility,
                            source_name,
                            source_url,
                            status
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );

                    if (!$insert) {

                        $message = "Unable to prepare the database insertion.";
                        $messageType = "danger";

                    } else {

                        mysqli_stmt_bind_param(
                            $insert,
                            "ssssssssss",
                            $title,
                            $organization,
                            $description,
                            $fundingAmount,
                            $fundingCurrency,
                            $deadline,
                            $eligibility,
                            $sourceName,
                            $sourceUrl,
                            $status
                        );

                        if (mysqli_stmt_execute($insert)) {

                            $newGrantId = mysqli_insert_id($conn);

                            $message = "Grant added successfully! Grant ID: "
                                . $newGrantId;

                            $messageType = "success";

                        } else {

                            if (mysqli_errno($conn) == 1062) {

                                $message = "This grant URL already exists.";

                            } else {

                                $message = "Database error while saving the grant.";
                            }

                            $messageType = "danger";
                        }

                        mysqli_stmt_close($insert);
                    }
                }

                mysqli_stmt_close($check);
            }
        }
    }
}

// Load navigation after processing
include("navbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manual Grant Import | GrantConnect</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f4f7fc;
            font-family: Arial, sans-serif;
        }

        .page-header {
            background: linear-gradient(120deg, #173b80, #3569d4);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
        }

        .form-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .form-label {
            font-weight: 600;
        }

        .required {
            color: red;
        }
    </style>
</head>

<body>

<div class="container py-4">

    <div class="page-header">
        <h2>
            <i class="bi bi-link-45deg me-2"></i>
            Manual Grant Website Import
        </h2>

        <p class="mb-0">
            Add grant opportunities from external funding websites
            by entering their details and official source URL.
        </p>
    </div>

    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Enter accurate information from the original grant website.
        Review the opportunity before making it available to researchers.
    </div>

    <?php if ($message !== ''): ?>

        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>

    <div class="card form-card">

        <div class="card-body p-4">

            <h5 class="mb-4">Grant Opportunity Details</h5>

            <form method="POST" action="manual_import_grant.php">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($csrfToken); ?>"
                >

                <div class="mb-3">

                    <label class="form-label">
                        Grant Title <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        maxlength="200"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Funding Organization <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="organization"
                        class="form-control"
                        maxlength="150"
                        placeholder="e.g. World Bank, NRF, European Commission"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">Grant Description</label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                        placeholder="Describe the purpose and objectives of this grant..."
                    ></textarea>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">Funding Amount</label>

                        <input
                            type="number"
                            name="funding_amount"
                            class="form-control"
                            min="0"
                            step="0.01"
                            placeholder="e.g. 500000"
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">Funding Currency</label>

                        <select
                            name="funding_currency"
                            class="form-select"
                        >
                            <option value="KES">KES - Kenyan Shilling</option>
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                            <option value="AUD">AUD - Australian Dollar</option>
                            <option value="CAD">CAD - Canadian Dollar</option>
                        </select>

                    </div>

                </div>

                <div class="mb-3">

                    <label class="form-label">Application Deadline</label>

                    <input
                        type="date"
                        name="deadline"
                        class="form-control"
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">Eligibility Requirements</label>

                    <textarea
                        name="eligibility"
                        class="form-control"
                        rows="3"
                        placeholder="Who is eligible to apply?"
                    ></textarea>

                </div>

                <hr>

                <h5 class="mb-3">Original Grant Website</h5>

                <div class="mb-3">

                    <label class="form-label">
                        Source Website Name <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="source_name"
                        class="form-control"
                        maxlength="150"
                        placeholder="e.g. World Bank"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Official Grant URL <span class="required">*</span>
                    </label>

                    <input
                        type="url"
                        name="source_url"
                        class="form-control"
                        maxlength="500"
                        placeholder="https://example.org/grants/opportunity"
                        required
                    >

                    <small class="text-muted">
                        Use the direct HTTPS link to the grant opportunity.
                    </small>

                </div>

                <div class="mb-4">

                    <label class="form-label">Grant Status</label>

                    <select name="status" class="form-select">

                        <option value="Open">Open</option>
                        <option value="Closed">Closed</option>

                    </select>

                </div>

                <button
                    type="submit"
                    name="save_grant"
                    class="btn btn-success btn-lg"
                >

                    <i class="bi bi-save me-2"></i>
                    Save Grant

                </button>

                <a
                    href="discover_grants.php"
                    class="btn btn-secondary btn-lg"
                >
                    Cancel
                </a>

            </form>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
