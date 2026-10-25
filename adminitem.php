<?php
// db connections
$servername = "localhost";
$username = "root";
$password = "";
$dbName = "gesco_db";

$conn = new mysqli($servername, $username, $password, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// image 
$uploadFolder = "uploads/";

// handle
$errors = [];
$editMode = false;
$editItem = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = trim($_POST['itemName'] ?? '');
    $description = trim($_POST['itemDescription'] ?? '');
    $price = trim($_POST['itemPrice'] ?? '');
    $stock = trim($_POST['itemStock'] ?? ''); 
    $category = trim($_POST['itemCategory'] ?? '');
    $imagePath = '';

    // validation
    if ($name === '') $errors[] = "Name is required.";
    if ($stock === '') $errors[] = "Stock is required.";
    if ($description === '') $errors[] = "Description is required.";
    if ($price === '' || !is_numeric($price) || $price == 0) $errors[] = "Valid price is required.";
    if ($category === '') $errors[] = "Category is required.";

    // 
    if (!empty($_FILES['itemImage']['name'])) {
        $file = $_FILES['itemImage'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = "Only JPG, PNG, GIF, and WEBP images are allowed.";
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading the image.";
        } else {
            // Save image
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('img_') . '.' . $ext;
            $dest = $uploadFolder . $filename;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $errors[] = "Failed to save uploaded image.";
            } else {
                $imagePath = $dest;
            }
        }
    }
    

    if (empty($errors)) {
        if ($id > 0) {
            // Edit item: keep img if not replaced
            if ($imagePath === '') {
                $stmt = $conn->prepare("SELECT image_url FROM items WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($existingImage);
                $stmt->fetch();
                $stmt->close();
                $imagePath = $existingImage;
            }
            else {
                // dlt old pics
                
                $stmt = $conn->prepare("SELECT image_url FROM items WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($existingImagePath);
                $stmt->fetch();
                $stmt->close();

                if ($existingImagePath && file_exists($existingImagePath)) {
                    unlink($existingImagePath); }
            }

            // Update query
            $stmt = $conn->prepare("UPDATE items 
            SET name=?, description=?, price=?, image_url=?, stock_quantity=?, category=? WHERE id=?");
            $stmt->bind_param("ssdsisi", $name, $description, $price, $imagePath, $stock, $category, $id);
            $stmt->execute();
            $stmt->close();

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            // Insert new item
            $stmt = $conn->prepare("INSERT INTO items (name, description, price, stock_quantity, image_url, category) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdiss", $name, $description, $price, $stock, $imagePath, $category);
            $stmt->execute();
            $stmt->close();

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

// Handle Delete GET request
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    // Optionally delete image file here (not implemented)
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Load item to edit if ?edit=ID
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editItem = $result->fetch_assoc();
    $stmt->close();
    if ($editItem) $editMode = true;
}

// Fetch all items for display
$items = [];
$result = $conn->query("SELECT * FROM items ORDER BY id DESC");
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin - Items Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" /> 
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    body {
      background-color:#ccc;
    }
    table {
    border-collapse: collapse;
    width: 100%;
    }
    .table td, .table th {
  vertical-align: middle;
  padding: 12px 15px; /* Add padding to make the content more readable */
  text-align: center;
  border: 1px solid #ddd; /* Light border between cells */
}

.table th {
  background-color: #f8f9fa;
  color: #495057;
  font-weight: bold;
  font-size: 14px;
}

/* Table Rows Styling */
.table tbody tr:nth-child(odd) {
  background-color: #f9f9f9; /* Alternating row colors */
}

.table tbody tr:nth-child(even) {
  background-color: #fff; /* Keep even rows white */
}

/* Hover effect on rows */
.table tbody tr:hover {
  background-color: #f1f1f1; /* Light grey when hovering */
}
.table td img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 5px;
}

/* Ensuring the table fits well on small screens */
@media (max-width: 768px) {
  .table td, .table th {
    padding: 8px 10px; /* Less padding on smaller screens */
  }

  .table th {
    font-size: 14px;
  }
}
.header {
    background: white;
    padding: 20px 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}



    img.item-image {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 5px;
    }
    img.preview-image {
      width: 120px;
      height: 120px;
      object-fit: contain;
      border: 2px solid #333;
      border-radius: 6px;
    }
    .navbar {
        background: linear-gradient(345deg,#667eea 5%,#85519bff 100%);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

    .table td, .table th {
      vertical-align: middle;
    }
    .nav-link.active {
      font-weight: bold;
      color: #fff !important;
    }
    .navbar-nav .nav-link:not(.active):hover {
      color: #fec1feff !important; 
      font-weight: bold;
      transition: 0.3s;
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
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg bg-dark border-bottom border-body">
  <div class="container-fluid">
    <a class="navbar-brand text-white" href="#" style="padding-left: 20px; min-height: 50px;">Admin - Items Management</a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
      <ul class="navbar-nav" style="padding-right: 20px;">
        <li class="nav-item">
          <a class="nav-link active text-white" aria-current="page" href="<?php echo $_SERVER['PHP_SELF']; ?>">Items Management</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="admindash.php">Admin Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#" onclick="signout()">Sign Out</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main class="container my-5">
  <div class="row">
    <!-- Left side: Table -->
    <div class="col-md-8 mb-4">
        <div class="header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="text-muted mb-1">Admin</h6>
                    <h2 class="text-muted mb-0">Items Management</h2>
                </div>
                
            </div>
      <input type="text" id="searchbar" class="form-control mb-3" placeholder="Search items..." oninput="filterItems()" />
      <div class="table-responsive">
        <table class="table table-bordered align-middle" id="itemsTable">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Image</th>
              <th>Name</th>
              <th>Description</th>
              <th>Stock</th>
              <th>Price (LKR)</th>
              <th>Category</th>
              <th style="width:120px;"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <tr>
                <td><?php echo $item['id']; ?></td>
                <td>
                  <?php if ($item['image_url'] && file_exists($item['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="item-image" alt="#itemImage" />
                  <?php else: ?>
                    <span class="text-muted">No image</span>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo htmlspecialchars($item['description']); ?></td>
                <td><?php echo htmlspecialchars($item['stock_quantity']); ?></td>
                <td><?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo htmlspecialchars($item['category']); ?></td>
                <td>
                  <a href="?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning mb-1 w-100">Edit</a>
                  <button class="btn btn-sm btn-outline-danger w-100" onclick="confirmDelete(<?php echo $item['id']; ?>)"><i class="fa-solid fa-trash"></i></button>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (count($items) === 0): ?>
              <tr><td colspan="7" class="text-center text-muted">No items found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Right side: Add/Edit Form -->
    <div class="col-md-4">
      <div class="card">
        <div class="card-header"><?php echo $editMode ? "Edit Item (ID: {$editItem['id']})" : "Add Item"; ?></div>
        <div class="card-body text-center">
          <?php if ($errors): ?>
            <div class="alert alert-danger text-start">
              <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                  <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" id="itemForm" onsubmit="return confirmSave()" novalidate>

            <input type="hidden" name="id" value="<?php echo $editMode ? $editItem['id'] : 0; ?>" />
            <div class="mb-3">
              <img id="preview" 
                   src="<?php echo $editMode && $editItem['image_url'] && file_exists($editItem['image_url']) ? htmlspecialchars($editItem['image_url']) : ''; ?>" 
                   class="img-thumbnail preview-image <?php echo ($editMode && $editItem['image_url']) ? '' : 'd-none'; ?>" 
                   alt="Preview" />
            </div>
            <div class="mb-3 text-start">
              <label for="itemName" class="form-label">Name</label>
              <input type="text" id="itemName" name="itemName" class="form-control" value="<?php echo $editMode ? htmlspecialchars($editItem['name']) : ''; ?>" required />
            </div>
            <div class="mb-3 text-start">
              <label for="itemDescription" class="form-label">Description</label>
              <textarea id="itemDescription" name="itemDescription" class="form-control" required><?php echo $editMode ? htmlspecialchars($editItem['description']) : ''; ?></textarea>
            </div>
            <div class="mb-3 text-start">
              <label for="itemPrice" class="form-label">Price (LKR)</label>
              <input type="number" step="0.01" min="0" id="itemPrice" name="itemPrice" class="form-control" value="<?php echo $editMode ? htmlspecialchars($editItem['price']) : ''; ?>" required />
            </div>

            <div class="mb-3 text-start">
            <label for="itemStock" class="form-label">Stock Quantity</label>
            <input type="number" min="0" id="itemStock" name="itemStock" class="form-control" 
                value="<?php echo $editMode ? htmlspecialchars($editItem['stock_quantity']) : ''; ?>" required />
            </div>

            <div class="mb-3 text-start">
            <label for="itemCategory" class="form-label">Category</label>
            <select id="itemCategory" name="itemCategory" class="form-select" required>
                <option value="">Choose Category...</option>
                <?php 
                $categories = ["marine items", "hardware tools", "industrial items"];
                $selectedCategory = $editMode ? $editItem['category'] : ($_POST['itemCategory'] ?? '');
                foreach ($categories as $cat) {
                    $isSelected = ($selectedCategory === $cat) ? "selected" : "";
                    echo "<option value='".htmlspecialchars($cat)."' $isSelected>".ucfirst($cat)."</option>";
                }
                ?>
            </select>
            </div>

            <div class="mb-3 text-start">
              <label for="itemImage" class="form-label">Image <?php echo $editMode ? "(choose to replace)" : ""; ?></label>
              <input type="file" id="itemImage" name="itemImage" class="form-control" accept="image/*" onchange="previewSelectedImage(event)" />
            </div>
            <button type="submit" class="btn btn-success w-100"><?php echo $editMode ? "Update Item" : "Add Item"; ?></button>
            <?php if ($editMode): ?>
              <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary w-100 mt-2">Cancel Edit</a>
            <?php endif; ?>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<footer class="bg-light text-center py-3 mt-auto">
  <span class="text-muted">&copy; 2025 GESCO STEEL</span>
  <p>Contact Us : 0112 444 885</p>
</footer>

<!-- js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  function signout() {
    if (confirm("Are you sure you want to sign out?")) {
      window.location.href = "login111.html";
    }
  }

  function confirmSave() {
    const form = document.getElementById('itemForm');
    const isEditMode = form.querySelector('input[name="id"]').value !== "0";
    
    const message = isEditMode
      ? "Are you sure you want to update this item?"
      : "Are you sure you want to add this item?";

    return confirm(message);
  }

  function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this item?")) {
      window.location.href = "?delete=" + id;
    }
  }

  function previewSelectedImage(event) {
    const preview = document.getElementById('preview');
    const file = event.target.files[0];
    if (!file) {
      preview.src = '';
      preview.classList.add('d-none');
      return;
    }
    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      preview.classList.remove('d-none');
    };
    reader.readAsDataURL(file);
  }

  function filterItems() {
    const search = document.getElementById('searchbar').value.toLowerCase();
    const rows = document.querySelectorAll('#itemsTable tbody tr');

    rows.forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(search) ? '' : 'none';
    });
  }
</script>

</body>
</html>