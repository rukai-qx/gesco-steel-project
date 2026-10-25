<?php
$ServerName = "localhost";
$UserName = "root"; // SQL username
$Password = "";
$dbName = "gesco_db"; // database name

// SQL connection
$conn = new mysqli($ServerName, $UserName, $Password, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
   //echo "<script>alert('Connected successfully');</script>";

// get user inputs from Register1.html

$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$password= $_POST['password'];


// =======================blacklisted================================
$blacklistedDomain = "@gesco.com";
$role_type = "customer"; // Default role type is 'customer' 

if (str_ends_with(strtolower($email), $blacklistedDomain)) {
    // If the email is blacklisted, stop the script and redirect/show error
    echo "<script>
            alert('Registration is not allowed for emails from the admin domain.');
            window.location.href = 'Register1.html'; // Redirect back to the registration page
          </script>";
    exit(); // IMPORTANT: Stop execution
}


//find if dupl user exists of gmail
$checkEmail = $conn->prepare("SELECT * FROM users WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$result = $checkEmail->get_result();

if ($result->num_rows > 0) {
    // Email already exists
    echo "<script>
            alert('Email already in use. please login.');
            window.location.href = 'Register1.html';
          </script>";
    exit();
}
$checkEmail->close();

// insert data into the users table  //register() method
$sql = "INSERT INTO users(role,name, email, phone, address, password) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt === FALSE) { 
     die('Insert prepare failed: ' . $conn->error);
}
    
// Bind parameters: 'i' for integer (role), 's' for strings
$stmt->bind_param("ssssss", $role_type, $name, $email, $phone, $address, $password);

if ($stmt->execute()) { 
        echo "<script>
        alert('New record saved successfully');
        window.location.href = 'login111.html';
        </script>";
} else {
    echo "<script>
            alert('Error saving record: " . $stmt->error . "');
            window.location.href = 'Register1.html';
          </script>";
}
}



// close the connection
$conn->close();
?>
