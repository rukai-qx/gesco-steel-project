<?php
// Start session
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login111.html");
    exit;
}

$email = $_SESSION['user_email'];

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gesco_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

  $stmt_user = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1;");
    $user_email = $_SESSION['user_email'];
    $stmt_user->bind_param("s", $user_email);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows === 0) die("no user");
    $user = $result_user->fetch_assoc();

// Handle cart updates (remove item, update quantity)
//deleteItem
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'remove_item':
                $item_id = intval($_POST['item_id']);
                // $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($item_id) {
                //     return $item['id'] != $item_id;
                // });
                // $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
                $delete_stmt = $conn->prepare("delete from cart where item_id = ?;");
                $delete_stmt->bind_param('i',$item_id );
                $delete_stmt->execute();
                echo json_encode(['success' => true]);
                exit;
                
            case 'update_quantity':
                $item_id = intval($_POST['item_id']);
                $new_quantity = intval($_POST['quantity']);
                
                if ($new_quantity <= 0) {
                    // Remove item if quantity is 0 or less
                    // $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($item_id) {
                    //     return $item['id'] != $item_id;
                    // });
                    // $_SESSION['cart'] = array_values($_SESSION['cart']);
                $item_id = intval($_POST['item_id']);
                $delete_stmt = $conn->prepare("delete from cart where item_id = ?;");
                $delete_stmt->bind_param('i',$item_id );
                $delete_stmt->execute();
                echo json_encode(['success' => true]);
                exit;
                } else {
                $update_item = $conn->prepare('UPDATE cart SET quantity = ? WHERE user_id = ? AND item_id = ?');
                $update_item->bind_param('iii', $new_quantity, $user['user_id'], $item_id);
                $update_item->execute();
                }
                echo json_encode(['success' => true]);
                exit;
        }
    }
}

// Calculate cart totals

$cart_count = 0;




  
    $cart_from_query = $conn->prepare("Select * from cart inner join items on cart.item_id = items.id where cart.user_id = ?;");
    $cart_from_query->bind_param("i", $user['user_id']);
    $cart_from_query->execute();
    $res = $cart_from_query->get_result();
   $cart_data = [];
   $cart_count = 0;
   $cart_total = 0;

    if ($res->num_rows >=0 ) {
         
  while ($row = $res->fetch_assoc()) {
    $cart_data[] = $row;
  }

 
    $cart_count_query = $conn->prepare("Select sum(cart.quantity) as tot, sum(cart.quantity * items.price) as tot_price from cart inner join items on items.id = cart.item_id where cart.user_id = ?;");
    $cart_count_query->bind_param("i", $user['user_id']);
    $cart_count_query->execute();
    $result_count = $cart_count_query->get_result();
    $cart_count_assc = $result_count->fetch_assoc();
    $cart_count = (int) $cart_count_assc['tot'];
    $cart_total = (float)  $cart_count_assc['tot_price'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_cart_items') {
    echo json_encode(['cart_items' => $cart_data, 'cart_total' => $cart_total]);
    exit;
}
    }
   
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            width: 100%;
        }

        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #dee2e6;
            background: #ffffff;
        }

        .price-text {
            font-weight: 600;
            color: #198754;
        }

        .cart-header {
            background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .cart-item {
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-quantity {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dee2e6;
        }

        .checkout-btn {
            background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            color: white;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .continue-shopping-btn {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
        }

        .continue-shopping-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border-radius: 12px;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            color: #6c757d;
        }

        .empty-cart i {
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="cart-container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="cart-header p-4">
                            <h2 class="mb-0 d-flex align-items-center">
                                <i class="bi bi-cart3 me-3"></i>
                                Shopping Cart
                                <span class="badge bg-dark text-light ms-3" id="cart-count">
                                    <?php echo $cart_count; ?> <?php echo ($cart_count == 1) ? 'item' : 'items'; ?>
                                </span>
                            </h2>
                        </div>

                        <div class="card-body p-0" id="cart-items-container">
                            <?php if ($cart_count<= 0): ?>
                                <!-- Empty Cart State -->
                                <div class="empty-cart">
                                    <i class="bi bi-cart-x text-muted"></i>
                                    <h4>Your Cart is Empty</h4>
                                    <p>Looks like you haven't added any items to your cart yet.</p>
                                    <a href="viewstock.php" class="btn btn-primary">
                                        <i class="bi bi-shop me-2"></i>Start Shopping
                                    </a>
                                </div>
                            <?php else: ?>
                                <!-- Cart Items -->
                                <?php foreach($cart_data as $item): ?>
                                <div class="cart-item p-4 border-bottom" data-id="<?php echo $item['id']; ?>" data-price="<?php echo $item['price']; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-sm-2 text-center mb-3 mb-sm-0">
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                                alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img rounded">
                                        </div>

                                        <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
                                            <p class="text-muted mb-1"><?php echo htmlspecialchars($item['description']); ?></p>
                                            <small class="text-success">In Stock</small>
                                        </div>

                                        <div class="col-6 col-sm-2 text-center mb-3 mb-sm-0">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <button class="btn btn-outline-secondary btn-quantity me-2"
                                                    onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="text" value="<?php echo $item['quantity']; ?>" class="form-control quantity-input" readonly>
                                                <button class="btn btn-outline-secondary btn-quantity ms-2"
                                                    onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="col-4 col-sm-2 text-center mb-3 mb-sm-0">
                                            <span class="price-text h5">Rs.<?php echo number_format($item['price'] * $item['quantity']); ?></span>
                                        </div>

                                        <div class="col-2 col-sm-2 text-center">
                                            <button class="btn btn-outline-danger btn-sm" onclick="removeItem(<?php echo $item['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <!-- Cart Footer with Total and Checkout -->
                                <div class="card-footer bg-white p-4">
    <div class="row align-items-center">
        <!-- Left Side: Continue Shopping -->
        <div class="col-md-6">
            <button class="btn continue-shopping-btn" onclick="proceedToHome()">
                <i class="bi bi-arrow-left me-2"></i>Continue Shopping
            </button>
        </div>
        
        <!-- Right Side: Total + Checkout -->
        <div class="col-md-6 text-end">
            <h4 class="mb-2">
                Total: <span class="price-text">Rs.<?php echo number_format($cart_total); ?></span>
            </h4>
            <br><br>
            <button id="checkout-btn" class="btn checkout-btn" type="button">
                <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
            </button>
        </div>
    </div>
</div>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmRemoveModal" tabindex="-1" aria-labelledby="confirmRemoveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmRemoveModalLabel">Remove Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to remove this item from your cart?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRemoveBtn">Remove</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let removeConfirmModal = null;
        let itemToRemove = null;

        function updateQuantity(itemId, change) {
            const cartItem = document.querySelector(`[data-id="${itemId}"]`);
            const quantityInput = cartItem.querySelector('.quantity-input');
            const currentQuantity = parseInt(quantityInput.value);
            const newQuantity = currentQuantity + change;

            if (newQuantity <= 0) {
                removeItem(itemId);
                return;
            }

            // Update quantity on server
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_quantity&item_id=${itemId}&quantity=${newQuantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Refresh page to show updated totals
                }
            });
        }

        function removeItem(itemId) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove_item&item_id=${itemId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Refresh page to show updated cart
                    }
                });
            }
        }

        function proceedToHome() {
            window.location.href = 'viewstock.php';
        }

        // Transfer session cart to localStorage for checkout
        function prepareCheckout() {
            const cartItems = <?php echo json_encode($_SESSION['cart']); ?>;
            const cartTotal = <?php echo $cart_total; ?>;
            
            localStorage.setItem('cartItems', JSON.stringify(cartItems));
            localStorage.setItem('cartTotal', cartTotal);
            
            window.location.href = 'checkout.php';
        }

        document.getElementById('checkout-btn')?.addEventListener('click', prepareCheckout);
    </script>
</body>

</html>