<?php
session_start();

// DB Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gesco_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all login activities
$sql = "SELECT id, email, login_time FROM activitylog ORDER BY login_time DESC";
$result = $conn->query($sql);

$activities = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
  
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Activity Log</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
        background-color: #f8f9fa;
    }

    .header {
        background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
        color: white;
        border-radius: 12px 12px 0 0;
    }

    .card {
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        border-radius: 12px;
    }

    .back-btn {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border: none;
        color: white;
        transition: all 0.3s ease;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
    }

    .back-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
    }
    body {
            background-image: url('uploads/loginImg.jpg');
            /* Path to your image */
            background-size: cover;
            /* Make image cover entire page */
            background-repeat: no-repeat;
            /* Don’t repeat the image */
            background-position: center;
            /* Center the image */
            height: 100vh;
            /* Full height of viewport */
            margin: 0;
            font-family: Arial, sans-serif;
            color: white;
        }
  </style>
</head>

<body>
<main class="container my-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-10">
      
      <div class="mb-4">
        <a href="admindash.php" class="back-btn">
          <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
      </div>

      <div class="card">
        <div class="header p-4 text-center">
          <h2><i class="bi bi-clock-history me-2"></i>Login Activity Log</h2>
        </div>

        <div class="card-body p-4">
          <?php if (empty($activities)): ?>
            <div class="empty-state">
              <i class="bi bi-inbox"></i>
              <h4>No Login Activity</h4>
              <p>No users have logged in yet.</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-dark">
                  <tr>
                    <th scope="col">Login ID</th>
                    <th scope="col">User Email</th>
                    <th scope="col">Login Time</th>

                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($activities as $activity): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($activity['id']); ?></td>
                      <td><?php echo htmlspecialchars($activity['email']); ?></td>
                      <td>
                        <small class="text-muted">
                          <?php echo date('M j, Y g:i A', strtotime($activity['login_time'])); ?>
                        </small>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
