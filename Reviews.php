<?php
// Database connection
$host = "localhost";
$user = "root";   // your DB username
$pass = "";       // your DB password
$db   = "gesco_db";   // your database name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}
// Fetch reviews
$sql = "SELECT * FROM reviews ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reviews</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
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
                    <h2 class="mb-0">Reviews</h2>
                </div>
                <div class="profile-pic">
                    <img src="ladyj.jpeg" alt="Profile" class="rounded-circle" width="50">
                </div>
            </div>
            <div class="reviews-container">
  <h2>All Reviews</h2>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Review ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Comment</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td>#<?php echo htmlspecialchars(str_pad($row['id'], 4, '0', STR_PAD_LEFT)); ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['comments']); ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="4" class="text-center text-muted">No reviews found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>


           
    </div>
</div>
<script>
    function signout() {
        if (confirm("Are you sure you want to sign out?")) {
            window.location.href = "login111.html"; // Redirect to login page
        }
    }
</script>


</body>
</html>
