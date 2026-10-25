<?php
// DB connection settings
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "gesco_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Fetch$users
//$sql = "SELECT fullName, email, Address FROM custbl ORDER BY fullName ASC";
$sql = "SELECT user_id,role,name,email,phone,address,password FROM users WHERE role = 'customer' ORDER BY user_id ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Customer Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="styles.css" rel="stylesheet">

  <style>
    body {
      background: #f5f7fb;
    }
    .main-content {
      padding: 2rem;
    }
    .card {
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(31, 41, 55, 0.06);
    }
    .table thead th {
      background: linear-gradient(90deg, #4f46e5, #9333ea);
      color: #fff;
    }
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: #6b7280;
    }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
     <div class="col-md-3 col-lg-2 sidebar">
                <div class="sidebar-header">
                    <h3 class="text-blue" style="font-style: italic; margin: 0px; font-weight: bold;">Welcome Back Emi!</h3>
                    <!-- Scrollable admin section -->
                    <div style="max-height: 60px; overflow-y: auto;">
                    <h6 class="text-white" style="margin: 10px; padding: 0px;">(The Admin)</h6>
                    
                </div>
                <div class="sidebar-nav-container">
                    <nav class="nav flex-column">
                    <a class="nav-link active" href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a class="nav-link" href="view_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                    <a class="nav-link" href="adminitem.php"><i class="fas fa-boxes"></i>  Items</a>
                    <a class="nav-link" href="customer.php"><i class="fas fa-users"></i>Customers</a>
                    <a class="nav-link" href="managers.php"><i class="fas fa-users"></i>Managers</a>
                    <a class="nav-link" href="staff.php"><i class="fas fa-users"></i>   Staff</a>
                    <a class="nav-link" href="Reviews.php"><i class="fas fa-comments"></i>  Reviews</a>
                    <a class="nav-link" href="view_returns.php"><i class="fas fa-undo"></i>  Returns</a>
                    <a class="nav-link" href="log.php"><i class="fas fa-bell"></i> Logs</a>
                    <a class="nav-link" href="report.php"><i class="fas fa-chart-bar"></i>Sales Report</a>
                    <a class="nav-link" href="#" onclick="signout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </nav>

                </div>
                </div>
            </div>

    <!-- Main Content -->
    <div class="col-md-9 col-lg-10 main-content">
      <!-- Header -->
      <div class="header d-flex justify-content-between align-items-center mb-4">
        <div>
          <h6 class="text-muted mb-1">Admin</h6>
          <h2 class="mb-0">Customer Management</h2>
        </div>
      </div>

      <div class="card">
        <div class="card-body p-3">
          <?php if (empty($users)): ?>
            <div class="empty-state">
              <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="#9ca3af" class="mb-3" viewBox="0 0 16 16">
                <path d="M8 0a5 5 0 0 0-4.546 2.916A1 1 0 0 1 3.3 5H12.7a1 1 0 0 1 .846-2.084A5 5 0 0 0 8 0z"/>
                <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-4a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/>
              </svg>
              <h5>No users found</h5>
              <p>There are currently no registered users.</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th>user_id</th>
                    <th>role</th>
                    <th>name</th>
                    <th>email</th>
                    <th>phone</th>
                    <th>address</th>
                    <th>password</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; foreach ($users as $c): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($c['user_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars($c['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars($c['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars($c['phone'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars($c['address'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo '********'; ?></td>
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
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/js/bootstrap.bundle.min.js"></script>
<script>
  function signout() {
    if (confirm("Are you sure you want to sign out?")) {
      window.location.href = "login111.html";
    }
  }
</script>
</body>
</html>
