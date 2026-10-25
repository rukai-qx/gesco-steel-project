<?php
// add_to_cart.php - Handles adding items to cart
session_start();

// Initialize cart in session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gesco_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle AJAX request to add item to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $item_id = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']) ?: 1;
    
    // Get item details from database
    $stmt = $conn->prepare("SELECT * FROM items WHERE id = ? AND stock_quantity >= ?");
    $stmt->bind_param("ii", $item_id, $quantity);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        
        // Check if item already exists in cart
        $found = false;
        // foreach ($_SESSION['cart'] as &$cart_item) {
        //     if ($cart_item['id'] == $item_id) {
        //         $cart_item['quantity'] += $quantity;
        //         $found = true;
        //         break;
        //     }
        // }
        // $existing_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = (SELECT user_id FROM users WHERE email = ? LIMIT 1);");
        // $existing_cart->bind_param("s", $_SESSION['user_email']);
        // $existing_cart->execute();

        // if($existing_cart->error) {
        //     echo json_encode([
        //         ['false' => false, 'message' => $existing_cart->error]
        //     ]);
        // }
        // $existing_cart_res  = $existing_cart->get_result();

        // $has_cart = $existing_cart_res->num_rows > 0;

        // if($has_cart) {
        // $existing_cart = $existing_cart_res->fetch_assoc();

        $stmt_user = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1;");
        $user_email = $_SESSION['user_email'];
        $stmt_user->bind_param("s", $user_email);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        if ($result_user->num_rows === 0) die("User not found");
        $user = $result_user->fetch_assoc();
        $ins_cart_stmt = $conn->prepare("insert into cart (item_id, quantity, user_id) values(?,?,?) on duplicate key update quantity = quantity + values(quantity);");
        $ins_cart_stmt->bind_param("iii", $item['id'], $quantity, $user['user_id']);
        $ins_cart_stmt->execute();

         if($ins_cart_stmt->error) {
            echo json_encode([
                ['false' => false, 'message' => $existing_cart->error]
            ]);
        }
        // }

        // If not found, add new item to cart
        // if (!$has_cart) {
            // $_SESSION['cart'][] = array(
            //     'id' => $item['id'],
            //     'name' => $item['name'],
            //     'description' => $item['description'],
            //     'price' => $item['price'],
            //     'image_url' => $item['image_url'],
            //     'quantity' => $quantity
            // );
        //     $ins_new_cart_stmt = $conn->prepare("insert into cart (item_id, quantity, user_id) values(?,?,(select user_id from users where email = ? limit 1))");
        //     $user_email = $_SESSION['user_email'];
        //     $ins_new_cart_stmt->bind_param("iis", $item['id'], $quantity, $user_email);
        //     $ins_new_cart_stmt->execute();
        // }
        
        // Return success response
        echo json_encode([
            'success' => true, 
            'message' => 'Item added to cart successfully!',
            // 'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
            'cart_count' => 1
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not available or insufficient stock']);
    }
    
    $stmt->close();
    exit;
}

// Handle request to get cart count
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_cart_count') {
    // $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
    $stmt_user = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1;");
    $user_email = $_SESSION['user_email'];
    $stmt_user->bind_param("s", $user_email);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows === 0) exit;
    $user = $result_user->fetch_assoc();
    $cart_count = $conn->prepare("Select sum(quantity) as tot from cart where user_id = ?;");
    $cart_count->bind_param("i", $user['user_id']);
    $cart_count->execute();
    $result_count = $cart_count->get_result();
$row = $result_count->fetch_assoc();
    // if ($cart_count->num_rows <= 0) exit;
    // $cart_count->fetch_assoc();
    echo json_encode(['cart_count' =>(int) $row['tot']]);
    exit;
}

// Handle request to get cart items
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_cart_items') {
    // echo json_encode(['cart_items' => $_SESSION['cart']]);
     $stmt_user = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1;");
    $user_email = $_SESSION['user_email'];
    $stmt_user->bind_param("s", $user_email);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows === 0) exit;
    $user = $result_user->fetch_assoc();
    $cart_count = $conn->prepare("Select * from cart where user_id = ?;");
    $cart_count->bind_param("i", $user['user_id']);
    $cart_count->execute();
    $result_count = $cart_count->get_result();
$row = $result_count->fetch_assoc();
    if ($cart_count->num_rows <= 0) exit;
   $cart_data =  $cart_count->fetch_assoc();
    echo json_encode(['cart_items' => $cart_data]);
    exit;
}

$conn->close();
?>

<!-- JavaScript for Add to Cart functionality -->
<script>
// Function to add item to cart
function addToCart(itemId, quantity = 1) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_to_cart&item_id=${itemId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification(data.message, 'success');
            
            // Update cart count in navigation
            updateCartCount();
            
            // Optional: Show mini cart preview
            showMiniCart();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to add item to cart', 'error');
    });
}

// Function to update cart count in navigation
function updateCartCount() {
    fetch('add_to_cart.php?action=get_cart_count')
    .then(response => response.json())
    .then(data => {
        // Update cart count badge in navigation
        const cartBadge = document.querySelector('.cart-count');
        if (cartBadge) {
            cartBadge.textContent = data.cart_count;
        }
    });
}

// Function to show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Function to show mini cart (optional feature)
function showMiniCart() {
    // This can be implemented to show a quick preview of cart contents
    console.log('Item added to cart!');
}

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});
</script>