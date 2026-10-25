<?php

session_start();

// DB connection
$serverName = "localhost";
$userName   = "root";
$password   = "";
$dbname     = "gesco_db";

$conn = new mysqli($serverName, $userName, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Check login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login111.html");
    exit();
}

// Fetch user info
$email = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT role,name,email,phone,address,password FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Assign variables
$defaultAvatar   = "https://thumbs.dreamstime.com/b/default-avatar-profile-icon-vector-social-media-user-image-182145777.jpg";
$userRole        = $user['role'];
$userName        = $user['name'];
$userEmail       = $user['email'];
$userPhone       = $user['phone'];
$userAddress     = $user['address'] ?? "Not provided";
$userPassword    = $user['password'];
$userProfilePic  = $user['image'] ?? $defaultAvatar;

// ================= PROFILE UPDATE =================
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $address  = $_POST['address'];

    $profileImage = $userProfilePic;
    if (isset($_POST['remove_image'])) {
        $profileImage = $defaultAvatar;
    } elseif (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName   = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $profileImage = $targetFile;
        }
    }

    $stmt = $conn->prepare("UPDATE users SET name=?, address=?, image=? WHERE email=?");
    $stmt->bind_param("ssss", $name, $address, $profileImage, $email);
    $stmt->execute();
    $stmt->close();

    $_SESSION['user_name']      = $name;
    $_SESSION['user_address']   = $address;
    $_SESSION['image']          = $profileImage;

    echo "<script>alert('Profile updated successfully'); window.location='cus.php';</script>";
    exit();
}

// ================= PASSWORD UPDATE =================
if (isset($_POST['update_password'])) {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPass = $_POST['confirm_password'];

    if ($oldPassword !== $userPassword) {
        echo "<script>alert('Old password incorrect'); window.location='cus.php';</script>"; exit();
    }
    if ($newPassword !== $confirmPass) {
        echo "<script>alert('Passwords do not match'); window.location='cus.php';</script>"; exit();
    }

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
    $stmt->bind_param("ss", $newPassword, $email);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Password updated successfully'); window.location='cus.php';</script>";
    exit();
}

// Total orders
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($totalOrders);
$stmt->fetch();
$stmt->close();

// Last login
$stmt = $conn->prepare("SELECT MAX(login_time) as last_login FROM activitylog WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($lastLogin);
$stmt->fetch();
$stmt->close();
$lastLogin = $lastLogin ?? date("Y-m-d H:i a");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* same CSS as before */
body { 
    margin:0; font-family: Arial,sans-serif; background:#ccc;
 }

.dashboard {
     position:fixed; left:0; top:0; height:100vh; width:230px;
     background: linear-gradient(0deg,#667eea 15%,#85519bff 100%);
     color:white; display:flex; flex-direction:column; align-items:center;
     padding-top:52px;
     transition:width 0.3s; overflow:hidden; }
     
.dashboard:hover { width:250px; align-items:flex-start; }

.dashboard .profile { text-align:center; margin-bottom:30px; width:100%; }

.dashboard .profile img { 
    width:100px; height:100px; 
    border-radius:50%; object-fit:cover; 
    transition:transform 0.3s; }

.dashboard .profile img:hover { transform:scale(1.5); }

.dashboard .profile span { display:none; margin-top:10px; font-weight:bold; color: #fff;}

.dashboard:hover .profile span { display:block; }
.dashboard a { 
    display:flex; align-items:center; padding:12px 20px; width:100%; 
    color:white; text-decoration:none; 
    transition:background 0.3s,padding-left 0.3s; font-weight:bold; }

.dashboard a i { margin-right:1rem; }

.dashboard a:hover { background-color:#ccc; color:#000; padding-left:25px; }

.dashboard .nav { margin-top:30px; width:100%; }

.main-content { margin-left:230px; padding:20px; transition:margin-left 0.3s; }

.dashboard:hover ~ .main-content { margin-left:250px; }

.card img.profile-pic { 
    width:130px; height:130px; border-radius:50%; 
    object-fit:cover; display:block; margin-bottom:15px; border: 2px solid white;
 }

.stats-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    width: 100%; 
    margin-bottom: 20px; /* space between cards */
}

.stats-card:hover { 
    transform:translateY(-5px);
    box-shadow:0 8px 25px rgba(0,0,0,0.15); 
}
.stats-card.stats-purple {  background:linear-gradient(135deg,#580c92,#fa8b98); }
.stats-card.stats-green { background:linear-gradient(135deg,#0f0874,#059669); }
.stats-card .card-body { 
    color:white;
    padding:25px 20px; }
.stats-card .card-title {
     font-size:0.9rem; opacity:0.9; 
     margin-bottom:8px; font-weight:500; }
.stats-card .card-value {
    font-size:1.8rem; 
    font-weight:bold; 
    margin-bottom:8px; 
}
.stats-icon { 
    width:50px; height:50px; border-radius:50%; 
    display:flex; align-items:center;
    justify-content:center; background:rgba(255,255,255,0.2); }
.stats-icon i { font-size:1.5rem; }
.welcome-card { 
    border:none; box-shadow:0 4px 6px rgba(0,0,0,0.07); 
    border-radius:12px; background:linear-gradient(135deg,#374151 0%,#524b63ff 100%); 
    color:white; padding:20px; margin-bottom:30px; }
</style>
</head>
<body>

<div class="dashboard">
    <div class="profile">
        <img src="<?php echo $userProfilePic; ?>" alt="Profile Picture">
        <br>  <br>
        <span><?php echo $userName; ?></span>
    </div>
    <div class="nav">
        <a href="viewstock.php"><i class="fa-solid fa-house"></i>Home</a>
        <a href="cus.php"><i class="fa-solid fa-user"></i>Profile</a>
        <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i>Cart</a>
        <a href="adreview.php"><i class="fa-solid fa-pen"></i>Reviews</a>
        <a href="returns.php"><i class="fa-solid fa-rotate-left"></i>Return Items</a>
        <a href="#" style="margin-top: 60px;" onclick="signout()"><i class="fa-solid fa-right-from-bracket"></i>Sign Out</a>
    </div>
</div>

<div class="main-content">
    <div class="card welcome-card">
        <div class="card-body">
            <h1 class="card-title"><b>Welcome, <?php echo $userName; ?></b></h1>
            <p class="card-text">Welcome back! Enjoy your shopping experience.</p>
        </div>
    </div>
    
    <div class="row mt-4">
    <div class="col-md-6">
        <div class="card stats-card stats-purple">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="card-title">Total Orders</p>
                    <h4 class="card-value"><?php echo $totalOrders; ?> purchases 📦</h4>
                </div>
                <div class="stats-icon purple"><i class="fas fa-list"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stats-card stats-green">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="card-title">Last Login</p>
                    <h4 class="card-value"><?php echo $lastLogin; ?> 🗓️</h4>
                </div>
                <div class="stats-icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>
</div>


    <div class="row">
        <!-- Profile Form -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h3 class="card-title">User Profile</h3><hr>
                    <form method="POST" enctype="multipart/form-data">
                        <img id="profilePreview" class="profile-pic" src="<?php echo $userProfilePic; ?>" alt="Profile Pic">
                        <label for="upload">Upload Profile:</label>
                        <input type="file" name="image" id="upload" accept="image/*" class="form-control mb-2">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image">
                            <label class="form-check-label" for="remove_image">Remove Profile Picture</label>
                        </div>
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control mb-2" value="<?php echo $userName; ?>">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control mb-2" value="<?php echo $userAddress; ?>">
                        <center><button type="submit" name="update_profile" class="btn btn-success mt-2">Update</button></center>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password Form -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h3 class="card-title">Change Password</h3><hr>
                    <form method="POST">
                        <label class="form-label">Old Password</label>
                        <input type="password" name="old_password" class="form-control mb-2" required>
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control mb-2" required>
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control mb-2" required>
                        <center><button type="submit" name="update_password" class="btn btn-success mt-2">Update</button></center>
                    </form>
                </div>
            </div>
        </div>
    </div>


<script>
const uploadInput = document.getElementById('upload');
const profilePreview = document.getElementById('profilePreview');
const removeCheck = document.getElementById('remove_image');

uploadInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) { profilePreview.src = e.target.result; }
        reader.readAsDataURL(file);
    }
});

removeCheck.addEventListener('change', function() {
    if(this.checked){
        profilePreview.src = "<?php echo $defaultAvatar; ?>";
    } else {
        profilePreview.src = "<?php echo $userProfilePic; ?>";
    }
});
function signout() {
    if (confirm("Are you sure you want to sign out?")) {
      window.location.href = "login111.html";
    }
  }
</script>
</body>
</html>
