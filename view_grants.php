<?php
session_start();
include("db_connect.php");
include("navbar.php");

// Prevent access if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Available Grants</title>
    <style>
        body{
            font-family: Arial;
            margin:40px;
            background:#f4f4f4;
        }

        table{
            width:100%;
            border-collapse:collapse;
            background:white;
        }

        th,td{
            border:1px solid #ccc;
            padding:12px;
            text-align:left;
        }

        th{
            background:#007BFF;
            color:white;
        }

        a{
            text-decoration:none;
        }

        .btn{
            background:green;
            color:white;
            padding:8px 15px;
            border-radius:5px;
        }

        .btn:hover{
            background:darkgreen;
        }
    </style>
</head>

<body>

<h2>Available Grants</h2>

<table>
    


<tr>

<th>Grant Title</th>

<th>Organization</th>

<th>Description</th>

<th>Funding</th>

<th>Deadline</th>

<th>Action</th>

</tr>

<?php

$sql = "SELECT * FROM grants";
$result = mysqli_query($conn, $sql);

while($row = mysqli_fetch_assoc($result))
{
?>

<tr>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['organization']; ?></td>

<td><?php echo $row['description']; ?></td>

<td>Ksh <?php echo number_format($row['funding_amount']); ?></td>

<td><?php echo $row['deadline']; ?></td>

<td>

<a class="btn"
href="apply_grant.php?grant_id=<?php echo $row['grant_id']; ?>">
Apply
</a>

</td>

</tr>

<?php
}
?>
</table>

<br><br>

<a href="dashboard.php">← Back to Dashboard</a>

</body>

</html>