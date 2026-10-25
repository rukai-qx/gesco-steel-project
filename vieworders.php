<?php
session_start();


// DB connection (adjust credentials as needed)
$servername = "localhost";
$username = "root";
$password = "";
$dbName = "gadgetsdb";

$conn = new mysqli($servername, $username, $password, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch orders for this user
/*Adjust table / column names as per your schema
$stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.total_price, o.status, o.items_description
    FROM orders o
    WHERE o.user_email = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Order History</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f5f5f5;
    }
    .table thead {
      background: linear-gradient(to right, #4f46e5, #9333ea);
      color: white;
    }
    .status-completed { color: green; font-weight: bold; }
    .status-pending   { color: orange; font-weight: bold; }
    .status-cancelled { color: red; font-weight: bold; }
    .navbar {
      background: linear-gradient(to right, #667eea, #764ba2);
    }
    .navbar a { color: white !important; }
    footer {
      background-color: #fff;
      border-top: 1px solid #ccc;
    }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg p-3">
  <div class="container-fluid">
    <a class="navbar-brand">Order History</a>
    <div class="navbar-nav ms-auto">
      <a class="nav-link" href="dashboard.php">Dashboard</a>
      <a class="nav-link" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<main class="container my-5">
  <h3>Order History for <?= htmlspecialchars($userEmail) ?></h3>
  <div class="table-responsive mt-4">
    <table class="table table-bordered text-center">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Date</th>
          <th>Items</th>
          <th>Total (LKR)</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($orders) === 0): ?>
          <tr>
            <td colspan="5" class="text-muted">No orders found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><?= htmlspecialchars($order['order_id']) ?></td>
              <td><?= htmlspecialchars($order['order_date']) ?></td>
              <td><?= htmlspecialchars($order['items_description']) ?></td>
              <td><?= number_format($order['total_price'], 2) ?></td>
              <?php
                $status = strtolower($order['status']);
                $cls = "status-" . $status;
              ?>
              <td class="<?= $cls ?>"><?= ucfirst($order['status']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<footer class="text-center py-3 mt-auto">
  <span class="text-muted">&copy; 2025 YourCompany</span>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
