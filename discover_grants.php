
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
$results = [];
$keyword = "";
$searchPerformed = false;

// =============================================
// HELPER: FORMAT GRANTS.GOV DATE
// =============================================

function convertGrantDate(?string $date): ?string
{
    if (empty($date)) {
        return null;
    }

    $dateObject = DateTime::createFromFormat('m/d/Y', $date);

    if (!$dateObject) {
        return null;
    }

    return $dateObject->format('Y-m-d');
}

// =============================================
// SEARCH GRANTS.GOV
// =============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['search_grants'])) {

    if (!hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )) {
        die("Invalid request. Please refresh the page.");
    }

    $keyword = trim($_POST['keyword'] ?? '');

    if ($keyword === '') {

        $message = "Please enter a keyword to search.";

    } elseif (strlen($keyword) > 150) {

        $message = "Your search keyword is too long.";

    } else {

        $apiUrl = "https://api.grants.gov/v1/api/search2";

        $requestData = [
            "rows" => 20,
            "startRecordNum" => 0,
            "keyword" => $keyword,
            "oppStatuses" => "forecasted|posted",
            "eligibilities" => "",
            "agencies" => "",
            "aln" => "",
            "fundingCategories" => ""
        ];

        if (!function_exists('curl_init')) {

            $message = "PHP cURL is not enabled. Please enable the cURL extension in XAMPP.";

        } else {

            $ch = curl_init($apiUrl);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($requestData),
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Accept: application/json"
                ],
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2
            ]);

            $response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

$curlError = curl_error($ch);

// Release the cURL handle.
// PHP automatically closes it when the handle is released.
unset($ch);

            if ($response === false) {

                $message = "Unable to connect to Grants.gov: "
                    . htmlspecialchars($curlError);

            } elseif ($httpCode !== 200) {

                $message = "Grants.gov returned HTTP error "
                    . (int)$httpCode . ". Please try again later.";

            } else {

                $apiData = json_decode($response, true);

                if (
                    !is_array($apiData) ||
                    ($apiData['errorcode'] ?? -1) != 0
                ) {

                    $message = "The Grants.gov search service returned an error.";

                } else {

                    $hits = $apiData['data']['oppHits'] ?? [];

                    $searchPerformed = true;

                    $_SESSION['grant_search_results'] = [];

                    foreach ($hits as $hit) {

                        if (
                            empty($hit['id']) ||
                            empty($hit['title'])
                        ) {
                            continue;
                        }

                        $grantId = (string)$hit['id'];

                        $sourceUrl =
                            "https://www.grants.gov/search-results-detail/"
                            . rawurlencode($grantId);

                        $grant = [
                            'id' => $grantId,
                            'number' => $hit['number'] ?? '',
                            'title' => $hit['title'] ?? '',
                            'organization' => $hit['agencyName'] ?? 'Grants.gov',
                            'openDate' => $hit['openDate'] ?? '',
                            'closeDate' => $hit['closeDate'] ?? '',
                            'status' => $hit['oppStatus'] ?? '',
                            'source_url' => $sourceUrl
                        ];

                        $results[] = $grant;

                        // Keep trusted search results in the session
                        $_SESSION['grant_search_results'][$grantId] = $grant;
                    }

                    if (empty($results)) {

                        $message = "No matching grant opportunities were found.";

                    } else {

                        $message = "Search completed successfully. "
                            . count($results)
                            . " opportunities found.";
                    }
                }
            }
        }
    }
}

// =============================================
// IMPORT SELECTED GRANT INTO DATABASE
// =============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['import_grant'])) {

    if (!hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )) {
        die("Invalid request. Please refresh the page.");
    }

    $selectedId = trim($_POST['opportunity_id'] ?? '');

    $savedGrant = $_SESSION['grant_search_results'][$selectedId] ?? null;

    if (!$savedGrant) {

        $message = "Grant information was not found. Please search again.";

    } else {

        $title = trim($savedGrant['title']);
        $organization = trim($savedGrant['organization']);

        $deadline = convertGrantDate($savedGrant['closeDate']);

        $sourceName = "Grants.gov";
        $sourceUrl = $savedGrant['source_url'];

        // Map external status to the database enum
        $grantStatus = strtolower($savedGrant['status']) === 'closed'
            || strtolower($savedGrant['status']) === 'archived'
            ? 'Closed'
            : 'Open';

        // Check for an existing source URL
        $check = mysqli_prepare(
            $conn,
            "SELECT grant_id FROM grants WHERE source_url = ? LIMIT 1"
        );

        if (!$check) {

            $message = "Database error: Unable to check existing grants.";

        } else {

            mysqli_stmt_bind_param($check, "s", $sourceUrl);
            mysqli_stmt_execute($check);

            $existingResult = mysqli_stmt_get_result($check);

            if ($existingResult && mysqli_num_rows($existingResult) > 0) {

                $message = "This grant has already been imported.";

            } else {

                // Description and eligibility can be completed
                // after retrieving the full opportunity details.
                $description =
                    "Imported from Grants.gov. Opportunity number: "
                    . $savedGrant['number']
                    . ". Please review the original source for full details.";

                $eligibility =
                    "Please review the original Grants.gov opportunity "
                    . "for eligibility requirements.";

                $fundingAmount = null;
                $fundingCurrency = "USD";

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

                    $message = "Unable to prepare the grant import.";

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
                        $grantStatus
                    );

                    if (mysqli_stmt_execute($insert)) {

                        $newGrantId = mysqli_insert_id($conn);

                        unset(
                            $_SESSION['grant_search_results'][$selectedId]
                        );

                        $message =
                            "Grant imported successfully! Grant ID: "
                            . $newGrantId;

                    } else {

                        if (mysqli_errno($conn) == 1062) {

                            $message = "This grant has already been imported.";

                        } else {

                            $message = "Database error while importing the grant.";
                        }
                    }

                    mysqli_stmt_close($insert);
                }
            }

            mysqli_stmt_close($check);
        }
    }

    $searchPerformed = true;
    $results = array_values(
        $_SESSION['grant_search_results'] ?? []
    );

    $keyword = trim($_POST['keyword'] ?? '');
}

// =============================================
// LOAD NAVIGATION AFTER PROCESSING
// =============================================

include("navbar.php");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Online Grant Discovery | GrantConnect</title>

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
            padding: 35px;
            border-radius: 15px;
            margin-bottom: 25px;
        }

        .search-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .search-button {
            background: #2563eb;
            color: white;
            border: none;
        }

        .search-button:hover {
            background: #174bb5;
            color: white;
        }

        .grant-card {
            border: none;
            border-radius: 13px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: 0.2s;
        }

        .grant-card:hover {
            transform: translateY(-3px);
        }

        .grant-title {
            color: #173b80;
            font-weight: 700;
        }

        .source-label {
            font-size: 12px;
            color: #64748b;
        }

    </style>

</head>

<body>

<div class="container py-4">

    <!-- PAGE HEADER -->

    <div class="page-header">

        <h2>
            <i class="bi bi-globe me-2"></i>
            Online Grant Discovery
        </h2>

        <p class="mb-0">

            Discover funding opportunities from external grant
            websites and import selected opportunities into GrantConnect.

        </p>

    </div>


    <!-- INFORMATION -->

    <div class="alert alert-info">

        <i class="bi bi-info-circle me-2"></i>

        Automatic search is currently connected to Grants.gov.
        Imported opportunities should be reviewed before being
        published to researchers.

    </div>


    <!-- SEARCH FORM -->

    <div class="card search-card mb-4">

        <div class="card-body p-4">

            <h5 class="mb-3">

                <i class="bi bi-search me-2"></i>

                Search External Grant Opportunities

            </h5>

            <form method="POST" action="discover_grants.php">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($csrfToken); ?>"
                >

                <div class="row g-3">

                    <div class="col-md-9">

                        <input
                            type="text"
                            name="keyword"
                            class="form-control form-control-lg"
                            placeholder="Search research, technology, health, climate..."
                            value="<?php echo htmlspecialchars($keyword); ?>"
                            required
                        >

                    </div>

                    <div class="col-md-3">

                        <button
                            type="submit"
                            name="search_grants"
                            class="btn search-button btn-lg w-100"
                        >

                            <i class="bi bi-search me-2"></i>

                            Search Grants

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- NOTIFICATIONS -->

    <?php if ($message !== ""): ?>

        <div class="alert alert-info alert-dismissible fade show">

            <?php echo htmlspecialchars($message); ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <!-- SEARCH RESULTS -->

    <?php if ($searchPerformed && !empty($results)): ?>

        <h4 class="mb-3">

            <i class="bi bi-list-check me-2"></i>

            Search Results

        </h4>


        <div class="row g-3">

            <?php foreach ($results as $grant): ?>

                <div class="col-12">

                    <div class="card grant-card">

                        <div class="card-body p-4">

                            <div class="d-flex justify-content-between flex-wrap gap-2">

                                <div>

                                    <h5 class="grant-title">

                                        <?php echo htmlspecialchars($grant['title']); ?>

                                    </h5>

                                    <p class="text-muted mb-2">

                                        <i class="bi bi-building me-1"></i>

                                        <?php echo htmlspecialchars($grant['organization']); ?>

                                    </p>

                                </div>

                                <span class="badge bg-primary align-self-start">

                                    <?php echo htmlspecialchars($grant['status']); ?>

                                </span>

                            </div>


                            <div class="row mt-3">

                                <div class="col-md-4">

                                    <small class="text-muted">
                                        Opportunity Number
                                    </small>

                                    <p>
                                        <?php echo htmlspecialchars($grant['number']); ?>
                                    </p>

                                </div>


                                <div class="col-md-4">

                                    <small class="text-muted">
                                        Opening Date
                                    </small>

                                    <p>
                                        <?php echo htmlspecialchars($grant['openDate'] ?: 'Not specified'); ?>
                                    </p>

                                </div>


                                <div class="col-md-4">

                                    <small class="text-muted">
                                        Closing Date
                                    </small>

                                    <p>
                                        <?php echo htmlspecialchars($grant['closeDate'] ?: 'Not specified'); ?>
                                    </p>

                                </div>

                            </div>


                            <div class="d-flex flex-wrap gap-2">

                                <a
                                    href="<?php echo htmlspecialchars($grant['source_url']); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-outline-primary btn-sm"
                                >

                                    <i class="bi bi-box-arrow-up-right me-1"></i>

                                    View Original Grant

                                </a>


                                <form
                                    method="POST"
                                    action="discover_grants.php"
                                    onsubmit="return confirm('Import this grant into GrantConnect?');"
                                >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php echo htmlspecialchars($csrfToken); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="opportunity_id"
                                        value="<?php echo htmlspecialchars($grant['id']); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="keyword"
                                        value="<?php echo htmlspecialchars($keyword); ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="import_grant"
                                        class="btn btn-success btn-sm"
                                    >

                                        <i class="bi bi-download me-1"></i>

                                        Import Grant

                                    </button>

                                </form>

                            </div>


                            <div class="source-label mt-3">

                                <i class="bi bi-globe me-1"></i>

                                Source: Grants.gov

                            </div>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <!-- MANUAL IMPORT PLACEHOLDER -->

   <div class="card search-card mt-4">

    <div class="card-body p-4">

        <h5>
            <i class="bi bi-link-45deg me-2"></i>
            Manual Grant Website Import
        </h5>

        <p class="text-muted">
            Add grant opportunities from other funding websites
            by entering their details and official website URLs.
        </p>

        <a href="manual_import_grant.php" class="btn btn-primary">

            <i class="bi bi-plus-circle me-2"></i>
            Add Grant Manually

        </a>

    </div>

</div>


    <!-- BACK BUTTON -->

    <div class="mt-4">

        <a
            href="manage_grants.php"
            class="btn btn-secondary"
        >

            <i class="bi bi-arrow-left me-2"></i>

            Back to Manage Grants

        </a>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
