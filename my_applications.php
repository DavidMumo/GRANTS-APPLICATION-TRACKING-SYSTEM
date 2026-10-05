<?php
session_start();
include("db_connect.php");
include("navbar.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Retrieve all applications submitted by the logged-in user
$sql = "SELECT
            ga.application_id,
            g.title,
            g.organization,
            ga.application_date,
            ga.proposal_file,
            ga.status,
            ga.feedback
        FROM grant_applications ga
        INNER JOIN grants g
            ON ga.grant_id = g.grant_id
        WHERE ga.user_id = '$user_id'
        ORDER BY ga.application_date DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Grant Applications</title>

    <style>

    body{
        font-family:Arial;
        background:#f5f5f5;
        margin:40px;
    }

    table{
        width:100%;
        border-collapse:collapse;
        background:white;
    }

    th{
        background:#007BFF;
        color:white;
        padding:12px;
    }

    td{
        padding:10px;
        border:1px solid #ddd;
    }

    tr:nth-child(even){
        background:#f2f2f2;
    }

    .pending{
        color:orange;
        font-weight:bold;
    }

    .approved{
        color:green;
        font-weight:bold;
    }

    .rejected{
        color:red;
        font-weight:bold;
    }

    a{
        text-decoration:none;
        color:blue;
    }

    </style>

</head>

<body>

<h2>My Grant Applications</h2>

<table>

<tr>

<th>ID</th>
<th>Grant</th>
<th>Organization</th>
<th>Date Applied</th>
<th>Proposal</th>
<th>Status</th>
<th>Feedback</th>

</tr>

<?php

if(mysqli_num_rows($result)>0)
{
    while($row=mysqli_fetch_assoc($result))
    {

        $class="pending";

        if($row['status']=="Approved")
            $class="approved";

        if($row['status']=="Rejected")
            $class="rejected";

?>

<tr>

<td><?php echo $row['application_id']; ?></td>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['organization']; ?></td>

<td><?php echo $row['application_date']; ?></td>

<td>

<a href="uploads/<?php echo $row['proposal_file']; ?>" target="_blank">

View Proposal

</a>

</td>

<td class="<?php echo $class; ?>">

<?php echo $row['status']; ?>

</td>

<td>

<?php echo $row['feedback']; ?>

</td>

</tr>

<?php

    }

}
else
{
    echo "<tr><td colspan='7'>You have not applied for any grants yet.</td></tr>";
}

?>

</table>

<br><br>

<a href="dashboard.php">← Back to Dashboard</a>

</body>
</html>