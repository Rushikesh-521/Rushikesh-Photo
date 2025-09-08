<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit();
}

$user = $_SESSION['user'];

// Database connection - change these as per your setup
$host = 'localhost';
$dbname = 'photography';
$dbuser = 'root';
$dbpass = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $dbuser, $dbpass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("DB connection failed: " . $e->getMessage());
}

$message = "";

// Map courses to weekday number (1=Monday, 2=Tuesday, ..., 5=Friday)
$courseDays = [
  'Candid' => 1,
  'Outdoor' => 2,
  'Wedding' => 3,
  'Child Photography' => 4,
];

// Handle enrollment form submission
if (isset($_POST['submit_form'])) {
  $name = $_POST['name'];
  $mobile = $_POST['mobile'];
  $email = $_POST['email'];
  $gender = $_POST['gender'];
  $education = $_POST['education'];
  $fees = $_POST['fees'];
  $course = $_POST['course'];

  // Validate course is one of the allowed options
  if (!array_key_exists($course, $courseDays)) {
    $message = "Invalid course selected.";
  } else {
    // Insert enrollment into DB
    $stmt = $pdo->prepare("INSERT INTO enrollments (username, name, mobile, email, gender, education, fees, course) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user, $name, $mobile, $email, $gender, $education, $fees, $course]);
    $message = "Thanks for enrolling, $name! We will contact you shortly.";
  }
}

// Fetch enrolled classes for this user
// Replace with this:
$stmt = $pdo->prepare("
  SELECT e1.*
  FROM enrollments e1
  INNER JOIN (
    SELECT course, MIN(enrolled_at) AS first_enroll_date
    FROM enrollments
    WHERE username = ?
    GROUP BY course
  ) e2 ON e1.course = e2.course AND e1.enrolled_at = e2.first_enroll_date
  WHERE e1.username = ?
");
$stmt->execute([$user, $user]);
$enrolledClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard | RK Photography</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .tab-content {
      display: none;
      padding: 20px;
      background: #fff;
      border-radius: 8px;
      margin-top: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .tab-content.active {
      display: block;
    }

    .nav-links li a.active-tab {
      background: #ff6600;
      border-radius: 4px;
      color: white;
    }

    .gallery-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      justify-content: center;
      margin-top: 20px;
    }

    .gallery-grid img {
      width: 200px;
      height: 150px;
      object-fit: cover;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      transition: transform 0.3s;
      cursor: pointer;
    }

    .gallery-grid img:hover {
      transform: scale(1.05);
    }

    /* Lightbox styles */
    .lightbox {
      display: none;
      position: fixed;
      z-index: 9999;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.85);
      justify-content: center;
      align-items: center;
    }

    .lightbox img {
      max-width: 90%;
      max-height: 90%;
      border-radius: 8px;
    }

    .lightbox:target {
      display: flex;
    }

    .lightbox-close {
      position: absolute;
      top: 20px;
      right: 30px;
      font-size: 30px;
      color: white;
      cursor: pointer;
    }

    /* Enrollment form styles */
    .dashboard-box {
      max-width: 500px;
      margin: 0 auto;
      background: #fafafa;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .dashboard-box form label {
      display: block;
      margin-top: 15px;
      font-weight: 600;
    }

    .dashboard-box form input[type="text"],
    .dashboard-box form input[type="email"],
    .dashboard-box form select {
      width: 100%;
      padding: 8px 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .dashboard-box form input[type="submit"] {
      margin-top: 20px;
      background-color: #ff6600;
      color: white;
      border: none;
      padding: 10px 20px;
      font-weight: 700;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .dashboard-box form input[type="submit"]:hover {
      background-color: #e65c00;
    }

    /* Message style */
    .message {
      color: green;
      font-weight: bold;
      text-align: center;
      margin-bottom: 20px;
    }

    /* Classes list */
    .classes-list {
      max-width: 600px;
      margin: 0 auto 30px auto;
      background: #fafafa;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .classes-list ul {
      list-style: none;
      padding-left: 0;
    }

    .classes-list li {
      padding: 15px;
      border-bottom: 1px solid #ddd;
    }

    .classes-list li:last-child {
      border-bottom: none;
    }

    /* Weekly calendar styles */
    .calendar-container {
      max-width: 700px;
      margin: 20px auto;
      font-family: Arial, sans-serif;
    }

    .calendar-container table {
      width: 100%;
      border-collapse: collapse;
      text-align: center;
    }

    .calendar-container th,
    .calendar-container td {
      border: 1px solid #ccc;
      padding: 12px;
      vertical-align: top;
      min-height: 80px;
    }

    .calendar-container th {
      background: #ff6600;
      color: white;
    }

    .calendar-container td.off-day {
      background: #eee;
      color: #999;
      font-weight: bold;
    }

    .calendar-container td.class-day {
      background: #def6de;
      font-weight: 600;
      color: #2e7d32;
    }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="nav-logo">📸 RK Photography</div>
  <ul class="nav-links">
    <li><a href="#" class="tab-btn active-tab" data-tab="home">Dashboard</a></li>
    <li><a href="#" class="tab-btn" data-tab="classes">My Classes</a></li>
    <li><a href="#" class="tab-btn" data-tab="photos">My Photos</a></li>
    <li><a href="#" class="tab-btn" data-tab="profile">Profile</a></li>
    <li><a href="logout.php" class="logout-btn">Logout</a></li>
  </ul>
</nav>

<div class="dashboard-container">
  <h2>Welcome, <?php echo htmlspecialchars($user); ?>!</h2>

  <?php if (!empty($message)): ?>
    <p class="message"><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>

  <!-- Dashboard Tab -->
  <div id="home" class="tab-content active">
    <h3>📊 Dashboard</h3>
    <p>Overview of your student dashboard.</p>

    <div class="dashboard-box">
      <h2>Enroll in a Course</h2>
      <form action="dashboard.php" method="POST">
        <label for="name">Full Name:</label>
        <input type="text" name="name" required />

        <label for="mobile">Mobile Number:</label>
        <input type="text" name="mobile" required />

        <label for="email">Email:</label>
        <input type="email" name="email" required />

        <label for="gender">Gender:</label>
        <select name="gender" required>
          <option value="">Select</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
        </select>

        <label for="education">Highest Education:</label>
        <input type="text" name="education" required />

        <label for="fees">Fees Paid:</label>
        <select name="fees" required>
          <option value="">Select</option>
          <option value="Yes">Yes</option>
          <option value="No">No</option>
        </select>

        <label for="course">Course Purchased:</label>
        <select name="course" required>
          <option value="">Select Course</option>
          <option value="Candid">Candid</option>
          <option value="Outdoor">Outdoor</option>
          <option value="Wedding">Wedding</option>
          <option value="Child Photography">Child Photography</option>
        </select>

        <input type="submit" name="submit_form" value="Enroll" />
      </form>
    </div>
  </div>

  <!-- My Classes Tab -->
  <div id="classes" class="tab-content">
    <h3>📅 My Classes</h3>

    <div class="classes-list">
      <?php if ($enrolledClasses): ?>
        <ul>
          <?php foreach ($enrolledClasses as $class): ?>
            <li>
              <strong>Course:</strong> <?php echo htmlspecialchars($class['course']); ?><br />
              <strong>Name:</strong> <?php echo htmlspecialchars($class['name']); ?><br />
              <strong>Email:</strong> <?php echo htmlspecialchars($class['email']); ?><br />
              <strong>Fees Paid:</strong> <?php echo htmlspecialchars($class['fees']); ?><br />
              <strong>Enrollment Date:</strong> <?php echo htmlspecialchars($class['enrolled_at']); ?><br />
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>You have no classes scheduled yet.</p>
      <?php endif; ?>
    </div>

    <!-- Weekly Calendar -->
    <div class="calendar-container">
      <table>
        <thead>
          <tr>
            <th>Monday</th>
            <th>Tuesday</th>
            <th>Wednesday</th>
            <th>Thursday</th>
            <th>Friday</th>
            <th class="off-day">Saturday</th>
            <th class="off-day">Sunday</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <?php
            for ($day = 1; $day <= 7; $day++) {
              if ($day >= 6) {
                // Saturday and Sunday are off
                echo '<td class="off-day">Off</td>';
              } else {
                // Show all courses that match this day
                $coursesToday = [];
                foreach ($enrolledClasses as $enroll) {
                  $courseName = $enroll['course'];
                  if (isset($courseDays[$courseName]) && $courseDays[$courseName] == $day) {
                    $coursesToday[] = htmlspecialchars($courseName);
                  }
                }
                if ($coursesToday) {
                  echo '<td class="class-day">' . implode("<br>", $coursesToday) . '</td>';
                } else {
                  echo '<td style="color:#aaa;">No Classes</td>';
                }
              }
            }
            ?>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- My Photos Tab -->
  <div id="photos" class="tab-content">
    <h3>📷 My Photos</h3>
    <div class="gallery-grid">
      <?php
      $dir = "photos/";
      $images = glob($dir . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
      if ($images) {
        foreach ($images as $index => $image) {
          echo '<img src="' . htmlspecialchars($image) . '" alt="Photo" onclick="openLightbox(this.src)">';
        }
      } else {
        echo "<p>No photos uploaded yet.</p>";
      }
      ?>
    </div>
  </div>

  <!-- Profile Tab -->
  <div id="profile" class="tab-content">
    <h3>👤 My Profile</h3>
    <p>Profile editing will be available soon.</p>
  </div>
</div>

<!-- Lightbox for fullscreen image -->
<div class="lightbox" id="lightbox">
  <span class="lightbox-close" onclick="closeLightbox()">×</span>
  <img id="lightbox-img" src="" alt="Full Image" />
</div>

<script>
  const tabButtons = document.querySelectorAll('.tab-btn');
  const contents = document.querySelectorAll('.tab-content');

  tabButtons.forEach(button => {
    button.addEventListener('click', e => {
      e.preventDefault();
      tabButtons.forEach(btn => btn.classList.remove('active-tab'));
      button.classList.add('active-tab');
      const target = button.getAttribute('data-tab');
      contents.forEach(content => content.classList.remove('active'));
      document.getElementById(target).classList.add('active');
    });
  });

  function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').style.display = 'flex';
  }

  function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
  }

  // Close lightbox on ESC key
  window.addEventListener('keydown', function(e) {
    if (e.key === "Escape") {
      closeLightbox();
    }
  });
</script>

</body>
</html>
