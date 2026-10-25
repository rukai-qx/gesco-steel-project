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

$success = $error = "";


// Handle Delete GET request
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        $success = "manager deleted successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']); // Reload the page
    exit;
}

// Insert manager record
if (isset($_POST['add_manager'])) {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = $_POST['password'];

    $expected_email = strtolower(str_replace(' ', '', $name)) . '@gesco.com';
    if(strtolower($email) !== $expected_email){
        $error = "Error: manager email must be in the format 'name@gesco.com' (e.g., " . $expected_email . ").";
    }
    else{

    // 2. Handle the Password Column: Set a dummy value
    // This value will fill the required password column but will never allow login.
    $dummy_password_hash = password_hash("NO_LOGIN_ACCESS_manager", PASSWORD_DEFAULT);
    ////
   
    // 3. Use Prepared Statement for secure insertion (CRITICAL FIX)
    $stmt = $conn->prepare("INSERT INTO users (role, name, email, phone, address, password) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt === FALSE) {
        $error = "SQL Prepare failed: " . $conn->error;
    } else {
        // Bind parameters: (s=string for all inputs, including the role from the form)
        // If 'role' is an INT in your DB, change the first 's' to 'i'.
        $stmt->bind_param("ssssss", $role, $name, $email, $phone, $address, $dummy_password_hash); 
        
        if ($stmt->execute()) {
                $success = "manager added successfully!";
            } else {
                // Check for duplicate entry errors (like duplicate email constraint)
                if ($conn->errno == 1062) {
                    $error = "Error: An account with this email already exists.";
                } else {
                    $error = "Error during insertion: " . $stmt->error;
                }
            }
        $stmt->close();
    }
    }

    /////
}

// Fetch manager //viewManagerDetails
$manager = $conn->query("SELECT * FROM users where role = 'manager'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>manager Management</title>
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
                    <h2 class="mb-0">Managers Management</h2>
                </div>  
            </div>

            <!-- Alerts -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php elseif (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Add manager Form -->
            <div class="card mb-4">
                <div class="card-header">Add Managers</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label>Role</label>
                            <input type="text" id="role" name="role" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Phone</label>
                            <input type="text" id="phone" name="phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Address</label>
                            <textarea id="address" name="address" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="text" id="password" name="password" class="form-control" >
                        </div>
                        
                        <button type="submit" name="add_manager" class="btn btn-primary">Add manager</button>
                    </form>
                </div>
            </div>

            

            <!-- manager Table -->
            <div class="card">
                <div class="card-header">Managers Table</div>
                <div class="card-body table-responsive">
                    <?php if ($manager && $manager->num_rows > 0): ?>
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <?php 
                                    if($manager){
                                        foreach($manager->fetch_fields() as $field);
                                    ?>
                                        
                                    <?php
                                        
                                    }
                                    ?>
                                    <th>User_id</th>
                                    <th>Role</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Action</th>

                                </tr>
                               
                            </thead>
                            <tbody>
                               <?php
                                 // Reset pointer for printing rows
                                if ($manager) $manager->data_seek(0);
                                while ($row = $manager->fetch_assoc()) { 
                                ?>
                                <tr>
                                <!-- Use explicit column names from the SELECT query in the order specified in the header -->
                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <!-- Hide the password data, as it's not useful to display to the admin -->
                                <td class="d-none"><?php echo htmlspecialchars($row['password']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger w-100" onclick="confirmDelete(<?php echo $row['user_id'];?>)">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No manager records found.</p>
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
    function confirmDelete(user_id) {
        if (confirm("Are you sure you want to delete this manager member?")) {
            window.location.href = "?delete=" + user_id;
        }
    }
</script>
</body>
</html>