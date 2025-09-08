<!DOCTYPE html>
<html>
<head>
    <title>Enroll Form</title>
</head>
<body>
    <h2>Course Enrollment</h2>
    <form action="save_enrollment.php" method="POST">
        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Mobile:</label><br>
        <input type="text" name="mobile" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Gender:</label><br>
        <select name="gender" required>
            <option value="">Select</option>
            <option>Male</option>
            <option>Female</option>
            <option>Other</option>
        </select><br><br>

        <label>Highest Education:</label><br>
        <input type="text" name="education" required><br><br>

        <label>Fees Paid:</label><br>
        <select name="paid" required>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select><br><br>

        <label>Course Purchased:</label><br>
        <input type="text" name="course" required><br><br>

        <input type="submit" value="Enroll">
    </form>
</body>
</html>
