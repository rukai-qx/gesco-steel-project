<?php
$pass_error = "";
$pass_success = "";

// DB connection
$host = "localhost";
$user = "root";     // your MySQL username
$pass = "";         // your MySQL password
$db   = "gesco_db"; // your DB name
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    if ($password !== $confirmPassword) {
        $pass_error = "Passwords do not match.";
    } else {
        // Update password in DB
        $update_stmt = $conn->prepare("UPDATE users SET password=? WHERE name=? AND email=?");
        $update_stmt->bind_param("sss", $password, $username, $email);
        $update_stmt->execute();

        if ($update_stmt->affected_rows > 0) {
            $pass_success = "Password updated successfully!";
        } else {
            $pass_error = "No matching account found. Please check username and email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style2.css">
</head>

<body>
    <div class="container-fluid custom-width">
        <div class="row min-vh-100">
            <!-- Form Section -->
            <div class="col-lg-6 p-0">
                <div class="form-section">
                    <div class="mb-2">
                        <h3 class="display-7 fw-bold text-dark mb-1">Reset Password</h3>
                        <p class="subtitle">Create a new password for your account</p>
                    </div>

                    <form id="resetPasswordForm" action="" method="POST" novalidate>
                        <!-- Username Field -->
                        <div class="mb-4">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person-fill"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username"
                                    placeholder="Enter your username" required>
                                <div class="invalid-feedback">
                                    Please provide a valid username.
                                </div>
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope-fill"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Enter your email address" required>
                                <div class="invalid-feedback">
                                    Please provide a valid email address.
                                </div>
                            </div>
                        </div>

                        <!-- New Password Field -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Enter new password" required minlength="8">
                                <div class="invalid-feedback">
                                    Password must be at least 8 characters long.
                                </div>
                            </div>
                            <div class="password-strength mt-2" id="passwordStrength">
                                <i class="bi bi-info-circle me-2"></i>Password must be at least 8 characters long
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label fw-semibold">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-shield-check-fill"></i>
                                </span>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                    placeholder="Confirm new password" required>
                                <div class="invalid-feedback">
                                    Passwords do not match.
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg" name="update_password">
                                <i class="bi bi-check-circle me-2"></i>Save New Password
                            </button>
                        </div>
                    </form>

                    <?php if (!empty($pass_error)): ?>
                        <div class="alert alert-danger"><?= $pass_error ?></div>
                    <?php elseif (!empty($pass_success)): ?>
                        <div class="alert alert-success"><?= $pass_success ?></div>
                        <script>
                            setTimeout(() => {
                                window.location.href = "login111.html";
                            }, 3000);
                        </script>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Welcome Section -->
            <div class="col-lg-6 p-0">
                <div class="welcome-section">
                    <div>
                        <div class="mb-4">
                            <i class="bi bi-shield-lock display-1 mb-3"></i>
                        </div>
                        <h2 class="display-4 fw-bold mb-4">Reset Your Password</h2>
                        <p class="lead mb-5">Choose a strong password to keep your account secure. Make sure it's
                            something you'll remember!</p>
                        <a href="login111.html" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>SIGN IN
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // (keep your JS validations for username/email/password strength here exactly as you already pasted earlier)
    </script>
</body>
</html>
