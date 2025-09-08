<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $check->bind_param("s", $email);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    echo "<p style='color:red;'>❌ Email already registered!</p>";
  } else {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $pass);
    if ($stmt->execute()) {
      echo "<p style='color:green;'>✅ Signup successful! <a href='login.php'>Login here</a></p>";
    } else {
      echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
    }
  }
}
?>

<!-- Signup Form -->
<!DOCTYPE html>
<html>
<head>
  <title>Sign Up | RK Photography</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="form-container">
    <h2>Sign Up</h2>
    <form method="POST" action="">
      <input type="text" name="name" placeholder="Full Name" required><br><br>
      <input type="email" name="email" placeholder="Email" required><br><br>
      <input type="password" name="password" placeholder="Password" required><br><br>
      <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
  </div>
</body>
</html>
