<?php

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

// Handle Delete GET request
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        $success = "Review deleted successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']); // Reload the page
    exit;
}
// Add a comment
if (isset($_POST['add_comment'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $comments = $_POST['comment'];

    $sql = "INSERT INTO reviews (name, email, comments) VALUES ('$name', '$email', '$comments')";
    if ($conn->query($sql)) {
        $success = "Comments added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Fetch reviews
$reviews = $conn->query("SELECT * FROM reviews ");
   // $stmt->bind_param("is", $deleteId, $customer_email);
if (!$reviews) {
    die("Error fetching reviews: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gadgets Admin - Reviews</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #ccc;
        margin: 0;
    }

    header {
        background: linear-gradient(345deg,#667eea 5%,#85519bff 100%);
        color: white;
        padding: 15px 0;
    }

    header .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    header nav {
        display: flex;
        gap: 3rem; 
    }

    header nav a {
        color: #a8c0fbff; 
        text-decoration: none;
        font-weight: bold;
        position: relative;
        padding: 5px 0;
        transition: transform 0.3s ease;
    }

    header nav a:hover {
        transform: scale(1.1); 
        color: whitesmoke;
    }

    header nav a::after {
        content: "";
        position: absolute;
        left: 50%;           
        bottom: -6px;       
        width: 0%;
        height: 2px;         
        background-color: #39ffe5;
        transition: all 0.3s ease;
        transform: translateX(-50%); 
    }

    header nav a:hover::after {
        width: 100%;
    }

    .reviews-container {
        margin: 40px auto;
        width: 90%;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .reviews-container h2 {
        margin-bottom: 20px;
        color: #373347ff;
    }

    table th {
        background-color: #0b6868;
        color: white;
    }

    footer {
        background-color: #000000ff;
        color: white;
        text-align: center;
        padding: 15px 0;
        margin-top: 150px;
    }
    .card-header {
        
        background:linear-gradient(135deg,#374151 0%,#524b63ff 100%);
        color: white;
        padding: 10px 15px;
        border-bottom: 2px solid #261f34ff;
    }
  </style>
</head>
<body>

<header>
  <div class="container">
      <div class="logo">
          <h3>GESCO STEEL</h3>
      </div>
      
    <nav>
      <a href="cus.php">Dashboard</a>
      <a href="viewstock.php">Home</a>
      <a href="adreview.php">Reviews</a>
      <a href="#" onclick="signOut()">Sign Out</a>
    </nav>
  </div>
</header>
 <!-- Alerts -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php elseif (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <br> <br>

            <!-- Add Comment Form -->
            <div class="card mb-4">
                <div class="card-header">Add Comments</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Comment</label>
                            <textarea id="comment" name="comment" class="form-control" required></textarea>
                        </div>
                        <button type="submit" name="add_comment" class="btn btn-primary">Add Comment</button>
                    </form>
                </div>
            </div>

            <!-- Reviews Table -->
            <div class="card">
                <div class="card-header">Reviews Table</div>
                <div class="card-body table-responsive">
                    <?php if ($reviews->num_rows > 0): ?>
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <?php
                                    $fields = $reviews->fetch_fields();
                                    foreach ($fields as $field): ?>
                                        <th><?php echo htmlspecialchars($field->name); ?></th>
                                    <?php endforeach; ?>
                                    <th>Actions</th> <!-- button -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $reviews->fetch_assoc()): ?>
                                    <tr>
                                        <?php foreach ($row as $val): ?>
                                            <td><?php echo htmlspecialchars($val); ?></td>
                                        <?php endforeach; ?>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger w-100" onclick="confirmDelete(<?php echo $row['id']; ?>)">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No comments records found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>


<footer>
  <h3>Contact Us</h3>
  <hr>
  <p>Contact Us : +94 0112 444 885</p>
  <p>Address : 38/1/3 Hultsdorf St, Colombo 01200, Sri Lanka</p>
  <p>&copy; 2025 Gesco Steel Pvt Ltd. All rights reserved.</p>
</footer>

<script>
    function confirmDelete(id) {
        if (confirm("Are you sure you want to delete this review?")) {
            window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?delete=" + id;
        }
    }
    function signOut() {
        if (confirm("Are you sure you want to sign out?")) {
            window.location.href = "login111.html"; // Redirect to login page
        }
    }
</script>
</body>
</html>

<?php $conn->close(); ?>
