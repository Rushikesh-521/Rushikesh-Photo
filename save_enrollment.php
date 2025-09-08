<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $education = $_POST['education'];
    $paid = $_POST['paid'];
    $course = $_POST['course'];

    $sql = "INSERT INTO users (name, mobile, email, gender, education, paid, course)
            VALUES ('$name', '$mobile', '$email', '$gender', '$education', '$paid', '$course')";

    if ($conn->query($sql) === TRUE) {
        echo "Enrollment successful!";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>
