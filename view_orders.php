<?php
// Start session
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gesco_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all orders from database
$sql = "SELECT order_id, name, status, total_amount, created_at FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);
$orders = [];

if ($result === false) {
    echo "Query error: " . $conn->error;
} else {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    } else {
        echo "No records found.";
    }
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Orders</title>
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

        .status-badge {
            font-size: 0.85em;
            font-weight: 600;
            padding: 0.5em 1em;
            border-radius: 50px;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #664d03;
        }

        .status-processing {
            background-color: #cff4fc;
            color: #055160;
        }

        .status-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-delivered {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
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

<body style="background-color:rgba(180, 192, 253, 1)">
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <!-- Back button -->
                <div class="mb-4">
                    <a href="admindash.php" class="back-btn">
                        <i class="bi bi-arrow-left me-2"></i>Back to Home
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="header p-4 text-center">
                        <h2 class="mb-0 d-flex align-items-center justify-content-center">
                            <i class="bi bi-list-ul me-3"></i>
                            View All Orders
                        </h2>
                    </div>

                    <div class="card-body p-4">
                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <i class="bi bi-inbox text-muted"></i>
                                <h4>No Orders Found</h4>
                                <p>There are currently no orders in the system.</p>
                                <a href="viewstock.php" class="btn btn-primary">
                                    <i class="bi bi-shop me-2"></i>Start Shopping
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th scope="col">Order ID</th>
                                            <th scope="col">Customer Name</th>
                                            <th scope="col">Total Amount</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Order Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo htmlspecialchars($order['order_id']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                                            <td>
                                                <span class="text-success fw-bold">
                                                    Rs.<?php echo number_format($order['total_amount']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $status = strtolower($order['status']);
                                                $statusClass = "status-" . $status;
                                                ?>
                                                <span class="badge status-badge <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?>
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
