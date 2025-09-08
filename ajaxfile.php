<?php
session_start();
include 'db.php';

// Restrict access to admin only
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all applications
$sql = "SELECT a.*, u.name AS student_name 
        FROM applications a
        JOIN users u ON a.student_id = u.id
        ORDER BY a.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Applications</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h2>📊 Student Applications</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Student</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Course</th>
        <th>Comments</th>
        <th>Submitted At</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['phone'] ?></td>
            <td><?= $row['course'] ?></td>
            <td><?= $row['comments'] ?></td>
            <td><?= $row['created_at'] ?></td>
        </tr>
    <?php } ?>
</table>

</body>
</html>
