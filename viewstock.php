<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login111.html");
    exit;
}

$email = $_SESSION['user_email'];


// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "gesco_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search
//browse() //search()
$search = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT * FROM items 
            WHERE name LIKE '%$search%' 
               OR category LIKE '%$search%' 
               OR description LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM items";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shop Items</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #ccc;
    margin: 0;
}
header {
    background: linear-gradient(345deg,#667eea 5%,#85519bff 100%);
    padding: 15px 0;
}
footer {
    background-color: #000000ff;
    color: white;
    text-align: center;
    padding: 15px 0;
    margin-top: 150px;
}
/* cards */
.item-card {
    width: 100%;
    transition: transform 0.3s, box-shadow 0.3s;
    border-radius: 12px;
}
.item-card:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 15px rgba(9, 48, 32, 0.15);
}
.item-card img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    background-color: #fff;
    padding: 5px;
    border-radius: 8px;
}
.price {
    font-weight: bold;
    color: #0b6868;
}
.add-to-cart-btn {
    min-height: 38px;
    transition: all 0.3s ease;
}
.add-to-cart-btn:disabled {
    opacity: 0.7;
}
</style>
</head>
<body>

<!-- Navbar -->
<header class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container">
    <a class="navbar-brand" href="#">GESCO STEEL</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="adreview.php">Reviews</a></li>
       
        <li class="nav-item"><a class="nav-link" href="cus.php">Dashboard</a></li>
        <a href="cart.php" class="btn btn-outline-light position-relative">
          <i class="bi bi-cart3"></i> Cart
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">0</span>
        </a>
      </ul>
      <!-- Search -->
      <form class="d-flex ms-auto" method="get">
        <input class="form-control me-2" 
               type="search" 
               name="search" 
               id="searchInput"
               value="<?php echo htmlspecialchars($search); ?>" 
               placeholder="Search items...">
        <button class="btn btn-outline-light" type="submit">Search</button>
      </form>
      <button class="btn btn-warning ms-2" onclick="signOut()">Sign Out</button>
    </div>
  </div>
</header>

<br><br><br>
<div class="container my-4">
  <h2 class="text-center mb-4">Shop Items</h2>
  <p class="text-center">Shop with convenience, have your picks delivered at home</p>
  <hr>

  <div class="row g-4">
    <?php
    //viewStock()
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cart_qty = 1; // default 
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $cart_item) {
                if ($cart_item['id'] == $row['id']) {
                    $cart_qty = $cart_item['quantity'];
                    break;
                }
          }
        }
?>
    <div class="col-md-4 col-sm-6 d-flex align-items-stretch">
      <div class="card item-card h-100">
        <img src="<?php echo $row['image_url']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
        <div class="card-body text-center d-flex flex-column">
          <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5> 
          <hr>
          <p class="card-text flex-grow-1"><?php echo htmlspecialchars($row['description']); ?></p>
          <p class="price">LKR <?php echo number_format($row['price'], 2); ?></p>
          <p><strong>Stock:</strong> 
            <?php echo $row['stock_quantity'] > 0 ? $row['stock_quantity'] : "<span class='text-danger'>Out of Stock</span>"; ?>
          </p>

          <div class="mt-auto">
            <?php if ($row['stock_quantity'] > 0): ?>
            <div class="input-group mb-3 justify-content-center" style="width: 200px; margin: 0 auto;">
                <span class="input-group-text">Qty</span>
                <input type="number" 
                       class="form-control text-center" 
                       value="<?php echo $cart_qty; ?>" 
                       min="1" 
                       max="<?php echo $row['stock_quantity']; ?>" 
                       id="qty-<?php echo $row['id']; ?>">
            </div>
            <button class="btn btn-success mt-2 add-to-cart-btn" data-item-id="<?php echo $row['id']; ?>">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
            </button>
            <?php else: ?>
              <button class="btn btn-secondary mt-2" disabled>
                <i class="fa-solid fa-ban"></i> Not Available
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php
        }
    } else {
        echo "<p class='text-center'>No items found.</p>";
    }
    ?>
  </div>
</div>

<footer>
  <h3>Contact Us</h3>
  <p>Contact number: +94 112 444 885</p>
  <p>Address : 38/1/3 Hultsdorf St, Colombo 01200, Sri Lanka</p>
  &copy; 2025 Gesco Steel Pvt Ltd. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="add_to_cart_functionality.js"> </script>
<script>
document.querySelectorAll('.add-to-cart-btn').forEach(button => {
    button.addEventListener('click', function() {
        const itemId = this.getAttribute('data-item-id');
        const qtyInput = document.getElementById(`qty-${itemId}`);
        const quantity = qtyInput ? qtyInput.value : 1;

        this.disabled = true;
        this.innerHTML = "Adding...";

        fetch('add_to_cart.php', {   // make sure file name matches your handler
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add_to_cart&item_id=${itemId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);

                //update cart badge immediately
                updateCartCount();
            } else {
                alert(data.message);
            }
           
        })
        .catch(error => {
            console.error('Error:', error);
            alert('failed to add item to the cart');
        })
        .finally(()=> {
          //re-enable btn
          this.disabled=false;
          this.innerHTML='<i class="fa-solid fa-cart-shopping"></i> Add to Cart';
        });
    });
});

function updateCartCount() {
    fetch('add_to_cart.php?action=get_cart_count')
        .then(response => response.json())
        .then(data => {
            const cartCountElem = document.querySelector('.cart-count');
            if (cartCountElem) {
                cartCountElem.textContent = data.cart_count;
            }
        })
        .catch(error => console.error('Error fetching cart count:', error));
}

//intialize cart badge on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();  // ensures correct count is shown immediately
});

document.getElementById('searchInput').addEventListener('input', function () {
  if (this.value.trim() === '') {
    this.form.submit();
  }
});
function signOut() {
    if (confirm("Are you sure you want to sign out?")) {
        window.location.href = "login111.html"; // Redirect to login page
    }
}
</script>
</body>
</html>
<?php $conn->close(); ?>
