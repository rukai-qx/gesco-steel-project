<?php
require __DIR__ . '/vendor/autoload.php';
require_once('get_cart_items.php');

use GuzzleHttp\Client;

// Start session
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gesco_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate unique order ID
    $order_id = 'ORD-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    
    // Get form data
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_SESSION['user_email'];
    $address = $_POST['address'];
    $payment_method = $_POST['paymentMethod'];

    $senderEmail = $_POST['email'];
    // $total_amount = $_POST['totalAmount'];
    $cart_info = getCartItems();
    $total_amount = $cart_info['cart_total'];
    $cart_items = $cart_info['cart_data'];

    
    // Insert order into database //purchaseitem() by clicking purchase order
    $stmt = $conn->prepare("INSERT INTO orders (order_id,name, phone, email, address, total_amount, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssds",$order_id,$name, $phone, $email, $address, $total_amount, $payment_method);

    


    
    //receive invoice email
    if ($stmt->execute()) {
        
        echo json_encode(['success' => true, 'order_id' => $order_id]);
       try {

        $ins_order_items = $conn->prepare("insert into order_items (item_id, order_id, price, stock_quantity, total_amount) values(?,?,?,?,?);");
    foreach ($cart_items as $row) {
    $tot = (float) $row['price'] * (int) $row['quantity'];
    $ins_order_items->bind_param("isdid", $row['item_id'], $order_id, $row['price'], $row['quantity'], $tot);
    $ins_order_items->execute();
    if($ins_order_items->error) {
        die($ins_order_items->error);
    }    
    }

    $delete = $conn->prepare("delete from cart where user_id = (select user_id from users where email = ?);");
    $delete->bind_param('s', $_SESSION['user_email']);
    $delete->execute();


$client = new GuzzleHttp\Client();
$mailItems = [];

foreach($cart_items  as $mitm) {
    $mailItems[]= [ "description" => $mitm['name'], "price" => $mitm['price'], "quantity" => $mitm['quantity'] ];
}

$payload = [ "invoice_id" => $order_id, "customer_email" =>$senderEmail, "customer_name" => $name, "due_date" => date('Y-m-d h:i:s'), "amount" => $total_amount, "items" => $mailItems ];
$client->request('POST', 'https://rukaiqx01.app.n8n.cloud/webhook/send-invoice', [
    'json' => $payload
]);
  
} catch (e) {

       }

    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    
    $stmt->close();
    $conn->close();
    exit;
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
    }

    .checkout-header {
        background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
        color: white;
        border-radius: 12px 12px 0 0;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
    }

    .place-order-btn {
        background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
        border: none;
        border-radius: 25px;
        padding: 15px 30px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        color: white;
    }

    .place-order-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        color: white;
    }

    .back-btn {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border: none;
        color: white;
        transition: all 0.3s ease;
        padding: 15px 30px;
        border-radius: 25px;
        font-weight: 600;
    }

    .back-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        color: white;
    }

    .card {
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        border-radius: 12px;
    }

    .order-total {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 2px solid #10b981;
        border-radius: 12px;
        padding: 20px;
    }

    .price-text {
        font-weight: 600;
        color: #198754;
    }

    .cart-summary {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .cart-item-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #dee2e6;
    }

    .cart-item-summary:last-child {
        border-bottom: none;
    }
    </style>
</head>

<body>
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm">
                    <div class="checkout-header p-4">
                        <h2 class="mb-0 d-flex align-items-center justify-content-center">
                            <i class="bi bi-bag-check me-3"></i>
                            Checkout
                        </h2>
                    </div>

                    <div class="card-body p-4">
                        <!-- Cart Summary -->
                        <div class="cart-summary">
                            <h5 class="mb-3"><i class="bi bi-cart3 me-2"></i>Order Summary</h5>
                            <div id="cart-items-summary">
                                <!-- Cart items will be loaded here -->
                            </div>
                        </div>

                        <!-- Order Total -->
                        <div class="order-total mb-4">
                            <h4 class="mb-3 text-center">
                                <i class="bi bi-receipt me-2"></i>
                                Order Total: <span class="price-text" id="order-total-display">Rs.0</span>
                            </h4>
                            <div class="text-center text-muted">
                                <small id="order-summary">
                                    <i class="bi bi-info-circle me-1"></i>
                                    0 items • Free shipping • Tax included
                                </small>
                            </div>
                        </div>

                        <form id="checkoutForm" class="row g-3 needs-validation" novalidate>
                            <!-- Customer Information -->
                            <div class="col-12">
                                <h5 class="mb-3 mt-3">
                                    <i class="bi bi-person me-2 text-primary"></i>
                                    Customer Information
                                </h5>
                            </div>

                            <div class="col-md-6">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="valid-feedback">Looks good!</div>
                                <div class="invalid-feedback">Please enter your first name.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    placeholder="+94 XX XXX XXXX" required pattern="^\+?[0-9]{10,14}$">
                                <div class="valid-feedback">Looks good!</div>
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="valid-feedback">Looks good!</div>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>

                            <!-- Delivery Address -->
                            <div class="col-12">
                                <h5 class="mb-3 mt-4">
                                    <i class="bi bi-geo-alt me-2 text-primary"></i>
                                    Delivery Address
                                </h5>
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Address *</label>
                                <textarea class="form-control" id="address" name="address" rows="2"
                                    placeholder="Enter your delivery address" required></textarea>
                                <div class="invalid-feedback">Please enter your delivery address.</div>
                            </div>

                            <!-- Payment Method -->
                            <div class="col-12">
                                <h5 class="mb-3 mt-4">
                                    <i class="bi bi-credit-card me-2 text-primary"></i>
                                    Payment Method
                                </h5>
                                <div class="form-check p-3 border rounded bg-light">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="cod"
                                        value="cod" checked>
                                    <label class="form-check-label fw-bold" for="cod">
                                        <i class="bi bi-cash-coin me-2 text-success"></i>
                                        Cash on Delivery
                                    </label>
                                    <div class="text-muted mt-1">
                                        <small>Pay when you receive your order</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden field for total amount -->
                            <input type="hidden" id="totalAmount" name="totalAmount">

                            <!-- Action Buttons -->
                            <div class="col-12 d-flex justify-content-between mt-4">
                                <a href="cart.php" class="btn back-btn">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Cart
                                </a>
                                <button type="submit" class="btn place-order-btn">
                                    <i class="bi bi-bag-check me-2"></i>Place Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    <h3 class="mt-3 mb-3 text-success">Order Placed Successfully!</h3>
                    <p class="text-muted mb-4" id="success-message">
                        Thank you for your order. You will receive a confirmation email shortly.<br>
                        Our delivery team will contact you within 24 hours.
                    </p>
                    <button type="button" class="btn place-order-btn" onclick="window.location.href='viewstock.php'">
                        Continue Shopping
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Load cart data from localStorage and display
    document.addEventListener('DOMContentLoaded', () => {
        loadCartData();

        // Form validation and submission
        const form = document.querySelector('#checkoutForm');
        form.addEventListener('submit', handleFormSubmit);
    });

    function loadCartData() {
        // const cartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');
        // const cartTotal = parseFloat(localStorage.getItem('cartTotal') || '0');

         fetch('cart.php?action=get_cart_items')
    .then(response => response.json())
    .then(data => {
        const cartItems = (data)['cart_items'];
        const cartTotal = (data)['cart_total'];
         const cartSummary = document.getElementById('cart-items-summary');
        const orderTotalDisplay = document.getElementById('order-total-display');
        const orderSummary = document.getElementById('order-summary');
        const totalAmountInput = document.getElementById('totalAmount');

        // Clear existing content
        cartSummary.innerHTML = '';

        if (cartItems.length === 0) {
            cartSummary.innerHTML = '<p class="text-center text-muted">No items in cart</p>';
            orderTotalDisplay.textContent = 'Rs.0';
            orderSummary.innerHTML = '<i class="bi bi-info-circle me-1"></i>0 items • Free shipping • Tax included';
            totalAmountInput.value = '0';
            return;
        }

        let totalItems = 0;
        cartItems.forEach(item => {
            totalItems += item.quantity;
            const itemPrice= parseFloat(item.price);
            const itemTotal = itemPrice * item.quantity;

            const itemDiv = document.createElement('div');
            itemDiv.className = 'cart-item-summary';
            itemDiv.innerHTML = `
                    <div>
                        <strong>${item.name}</strong>
                        <br>
                        <small class="text-muted">${item.description} (x${item.quantity})</small>
                    </div>
                    <div class="price-text">Rs.${itemTotal.toLocaleString()}</div>
                `;
            cartSummary.appendChild(itemDiv);
        });

        // Update totals
        orderTotalDisplay.textContent = `Rs.${cartTotal.toLocaleString()}`;
        orderSummary.innerHTML =
            `<i class="bi bi-info-circle me-1"></i>${totalItems} items • Free shipping • Tax included`;
        totalAmountInput.value = cartTotal;
    });

       
    }

    async function handleFormSubmit(event) {
        event.preventDefault();

        const form = event.target;

        if (!form.checkValidity()) {
            event.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        // Prepare form data
        const formData = new FormData(form);

        try {
            const response = await fetch('checkout.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Clear cart from localStorage
                localStorage.removeItem('cartItems');
                localStorage.removeItem('cartTotal');

                // Update success message with order ID
                document.getElementById('success-message').innerHTML = `
                        Thank you for your order! Your Order ID is: <strong>${result.order_id}</strong><br>
                        You will receive a confirmation email shortly.<br>
                        Our delivery team will contact you within 24 hours.
                    `;

                // Show success modal
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            } else {
                alert('Error placing order: ' + (result.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error submitting order. Please try again.');
        }

        form.classList.add('was-validated');
    }
    </script>
</body>

</html>