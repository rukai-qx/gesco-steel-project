<?php
// Start session
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
// FIX: Reverting to an empty password. 
// This is the default setting for the 'root' user in many local environments (like XAMPP)
// If you have set a password for root, replace "" with your actual password.
$password = ""; 
$dbname = "gesco_db"; // Database name used in your running environment

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // We will not reach this if the password fix works, but it's good practice to log the specific error
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all returns from database using the columns visible in your provided schema. //viewReturns()
$sql = "SELECT 
            return_id, 
            order_id, 
            customer_name, 
            status, 
            created_at,
            quantity,
            item_condition,
            return_reason
        FROM returns 
        ORDER BY created_at DESC";


$result = $conn->query($sql);
$returns = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $returns[] = $row;
    }
} else if ($conn->error) {
     // If the 'returns' table doesn't exist in 'gesco steel', this will show an error.
     // die("Query failed: " . $conn->error); 
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Returns</title>
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
        
        /* Custom status classes for Returns */
        .status-requested {
            background-color: #fef3c7; /* light yellow */
            color: #b45309; /* dark orange */
        }

        .status-inspecting {
            background-color: #dbeafe; /* light blue */
            color: #1e40af; /* dark blue */
        }

        .status-refunded {
            background-color: #d1e7dd; /* light green */
            color: #0f5132; /* dark green */
        }

        .status-rejected {
            background-color: #fbd5d8; /* light red */
            color: #991b1b; /* dark red */
        }
        
        .status-completed {
            background-color: #cff4fc; /* light cyan */
            color: #055160; /* dark cyan */
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
            <div class="col-12 col-lg-11"> <!-- Increased column size for more data -->
                <!-- Back button -->
                <div class="mb-4">
                    <a href="admindash.php" class="back-btn">
                        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="header p-4 text-center">
                        <h2 class="mb-0 d-flex align-items-center justify-content-center">
                            <i class="bi bi-arrow-return-left me-3"></i>
                            View All Returns
                        </h2>
                    </div>

                    <div class="card-body p-4">
                        <?php if (empty($returns)): ?>
                            <div class="empty-state">
                                <i class="bi bi-box-seam text-muted"></i>
                                <h4>No Returns Filed</h4>
                                <p>There are currently no return requests in the system.</p>
                                <a href="admindash.php" class="btn btn-primary">
                                    <i class="bi bi-speedometer me-2"></i>Go to Dashboard
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th scope="col">Return ID</th>
                                            <th scope="col">Order ID</th>
                                            <th scope="col">Customer Name</th>
                                            <th scope="col">Qty</th> 
                                            <th scope="col">Condition</th> 
                                            <th scope="col">Status</th>
                                            <th scope="col">Request Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($returns as $return): ?>
                                        <tr>
                                            <td>
                                                <strong>R-<?php echo htmlspecialchars($return['return_id']); ?></strong>
                                            </td>
                                            <td>
                                                <!-- Assuming an order_details.php exists -->
                                                <a href="order_details.php?id=<?php echo htmlspecialchars($return['order_id']); ?>">
                                                    #<?php echo htmlspecialchars($return['order_id']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($return['customer_name']); ?></td>
                                            <td>
                                                <span class="fw-bold">
                                                    <?php echo htmlspecialchars($return['quantity']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge text-bg-info">
                                                    <?php echo htmlspecialchars(ucfirst($return['item_condition'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $status = strtolower($return['status']);
                                                // Map status to CSS class for styling
                                                $statusClass = "status-" . (in_array($status, ['requested', 'inspecting', 'refunded', 'rejected', 'completed']) ? $status : 'inspecting');
                                                ?>
                                                <span class="badge status-badge <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($return['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M j, Y g:i A', strtotime($return['created_at'])); ?>
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


