<?php
session_start(); // Start session to store user data

$serverName = "localhost";
$userName = "root";
$password = "";
$dbname = "gesco_db";

// SQL connection
$conn = new mysqli($serverName, $userName, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user inputs from login form
$email = $_POST['email'] ?? '';
$passwordInput = $_POST['password'] ?? '';

// ====== ADMIN CHECK ======
$admin_email = "admin@gesco.com";
$admin_password = "admin@123";
$admin_name = "admin";

if ($email === $admin_email && $passwordInput === $admin_password) {
    // Admin login successful
    $_SESSION['user_name'] = $admin_name;
    $_SESSION['user_email'] = $admin_email;
    $_SESSION['logged_in'] = true;
    $_SESSION['is_admin'] = true;

    // Log activity
    $loginTime = date("Y-m-d H:i:s");
    $sql = "INSERT INTO activitylog (email, login_time) VALUES (?, ?)";
    $stmtLog = $conn->prepare($sql);
    $stmtLog->bind_param("ss", $email, $loginTime);
    $stmtLog->execute();
    $stmtLog->close();

    echo "<script>
            alert('Admin Login successful! Welcome " . $admin_name . "');
            window.location.href = 'admindash.php'; 
          </script>";
    exit();
}

// ====== CUSTOMER LOGIN ======

// Prepare statement to fetch customer
$stmt = $conn->prepare("SELECT role,name, email,phone, address,password FROM users WHERE email = ?");
if ($stmt === FALSE) {
    die('SQL Prepare failed: ' . $conn->error);
}
// If it fails, the script stops here and shows you the actual SQL error.

$stmt->bind_param("s", $email);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Log password
    // ⚠️ NOTE: You are using plain text. In production, use `password_hash()` and `password_verify()`
    if ($passwordInput === $user['password']) {
        // Set session
        $_SESSION['user_role']    = $user['role'];
        $_SESSION['user_name']    = $user['name'];
        $_SESSION['user_email']   = $user['email'];
        $_SESSION['user_phone']   = $user['phone'];
        $_SESSION['user_address'] = $user['address'];
        $_SESSION['logged_in']    = true;

        // Save login to activity table 
        // login()
        $sql = "INSERT INTO activity (email, password) VALUES (?, ?)";
        $stmtActivity = $conn->prepare($sql);
        if ($stmtActivity === FALSE) {
         // This will tell you exactly what is wrong with your SQL string or table/column names
            die('Activity Log SQL Prepare failed: ' . $conn->error); 
        }
        $stmtActivity->bind_param("ss", $email, $passwordInput);
        $stmtActivity->execute();
        $stmtActivity->close();

        // Save login time to activity log
        $loginTime = date("Y-m-d H:i:s");
        $sql = "INSERT INTO activitylog (email, login_time) VALUES (?, ?)";
        $stmtLog = $conn->prepare($sql);
        $stmtLog->bind_param("ss", $email, $loginTime);
        $stmtLog->execute();
        $stmtLog->close();

        echo "<script>
                alert('Login successful! Welcome " . $user['name'] . "');
                window.location.href = 'cus.php'; 
              </script>";
        exit();
    } else {
        // Wrong password
        echo "<script>
                alert('Invalid email or password!');
                window.location.href = 'login111.html';
              </script>";
        exit();
    }
} else {
    // No user found
    echo "<script>
            alert('No user found!');
            window.location.href = 'login111.html';
          </script>";
    exit();
}

// Close connections
$stmt->close();
$conn->close();
?>
