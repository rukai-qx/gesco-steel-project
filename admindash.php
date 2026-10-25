<?php
// dashboard.php

// DB connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "gesco_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



$success = $error = "";


// Insert settings record
if (isset($_POST['add_deets'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $registration_no = $_POST['registration_no'];
    $email = $_POST['email'];


    $expected_email = strtolower(str_replace(' ', '', $name)) . '@gesco.com';
    if(strtolower($email) !== $expected_email){
        $error = "Error: manager email must be in the format 'name@gesco.com' (e.g., " . $expected_email . ").";
    }
    else{
   
    // 3. Use Prepared Statement for secure insertion (CRITICAL FIX)
    $stmt = $conn->prepare("INSERT INTO settings (name,address,phone,registration_no,email) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt === FALSE) {
        $error = "SQL Prepare failed: " . $conn->error;
    } else {
        // Bind parameters: (s=string for all inputs, including the role from the form)
        // If 'role' is an INT in your DB, change the first 's' to 'i'.
        $stmt->bind_param("sssis", $name, $address, $phone, $registration_no, $email); 
        
        if ($stmt->execute()) {
                $success = "company deets added successfully!";
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <div class="row" >
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar" >
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
                <div class="header d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h6 class="text-muted mb-1">Admin</h6>
                        <h2 class="mb-0">Dashboard</h2>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="profile-pic">
                            <img src="ladyj.jpeg" alt="Profile" class="rounded-circle" width="50">
                        </div>
                    </div>
                </div>

                <!-- Example content (you already had) -->
                <!-- Overview Data Cards -->
                <div class="section-title mb-3">
                    <h5>Overview</h5>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card stats-red">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="card-title">Total Revenue</p>
                                    <h4 class="card-value">Rs.700,000</h4>
                                   
                                </div>
                                <div class="stats-icon red">
                                <i class="fas fa-money-bill-wave"></i> 
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card stats-card stats-purple">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="card-title">Total Orders</p>
                                    <h4 class="card-value">600 items</h4>
                                    
                                </div>
                                <div class="stats-icon purple">
                                    <i class="fas fa-list"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card stats-card stats-green">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="card-title">Delivered Orders</p>
                                    <h4 class="card-value">490 orders</h4>
                                    <!--<small class="card-number">* * ** 8896</small>-->
                                </div>
                                <div class="stats-icon green">
                                   <i class="fas fa-truck"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card stats-card stats-blue">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="card-title">Pending Orders</p>
                                    <h4 class="card-value">110 orders</h4>
                                    
                                </div>
                                <div class="stats-icon blue">
                                    <i class="fas fa-hourglass-half"></i>      
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selling Items Data Table -->
                

                <div class="card">
                    <div class="card-body p-0">
                        <html>
    <head>
        <!-- Bootstrap CSS (The pre written style sheet with a lots of classes) -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body style="background-color: rgb(238, 239, 240);">

         <!-- The container class is being used with d flex meaning turning out the class into a
          flex box by using the justify content center the flex box is placed center in the screen and align-items-center min-vh-100 meaning brings
          the flex box to the mid of the screen with atleat 100 in height ) -->

        <div class="container d-flex justify-content-center align-items-center min-vh-100">

            <!-- the class card has been used a componenet similar to a container and used padding 4 with shadow larg
             and border radius 4 called rounded class and adjusted the width and height -->
            <div class="card p-4 shadow-lg rounded-4" style="width: 900px; height: 670px; background-color: #e0dfe1ff;">
                <form action="" method="post" onsubmit="return validateSettings()">
                    <h1 style="font-family: 'Times New Roman', Times, serif;"><b><center>Company Settings</center></b></h1>

                    <div class="mb-3"> <!-- Margin Bottom,space between the fields -->
                        <label class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter company name" required>
                    </div>

                    <div class="mb-3"> <!-- Margin Bottom,space between the fields -->
                        <label class="form-label">Company Address</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Enter company address" required>
                    </div>

                    <div class="mb-3"> <!-- Margin Bottom,space between the fields -->
                        <label class="form-label">Company Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter company phone" required>
                    </div>

                    <div class="mb-3"> <!-- Margin Bottom,space between the fields -->
                        <label class="form-label">Business Registration Number</label>
                        <input type="text" class="form-control" id="registration_no" name="registration_no" placeholder="Enter business registration number" required>
                    </div>

                    <div class="mb-3"> <!-- Margin Bottom,space between the fields -->
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required>
                    </div>

                    <!-- Save button -->
                    <input type="submit" class="btn btn-primary w-100 mb-3">

                </form>
            </div>
        </div>
        
   </body>
</html>
                       
                      
 
</body>
</html>
<script>
    function validateSettings() {
        // Get values from input fields
        let email = document.getElementById("email").value;
        let password = document.getElementById("password").value;

        // Validate whether the fields are empty
        if (!email || !password) {
            alert("Please fill in all fields.");
            return false; // Prevent form submission
        }

        // Validate whether the email is in the correct format
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; //email format
        if (!emailPattern.test(email)) {
            alert("Please enter a valid email address.");
            return false; // Prevent form submission
        }

        return true; // Allow form submission
        }
</script>