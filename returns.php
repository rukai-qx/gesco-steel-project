<?php
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
    // Generate unique return ID
    $return_id = 'RET-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    
    // Get form data
    $order_id = $_POST['orderId'];
    $order_date = $_POST['orderDate'];
    $item_id = $_POST['itemId'];
    $quantity = $_POST['quantity'];
    $condition = $_POST['condition'];
    $return_reason = $_POST['returnReason'];
    $additional_details = $_POST['additionalDetails'];
    $customer_name = $_POST['customerName'];
    $customer_phone = $_POST['customerPhone'];
    $customer_email = $_POST['customerEmail'];
    
    // Insert return request into database
    $stmt = $conn->prepare("INSERT INTO returns (return_id, order_id, order_date, item_id, quantity, item_condition, return_reason, additional_details, customer_name, customer_phone, customer_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssissssss", $return_id, $order_id, $order_date, $item_id, $quantity, $condition, $return_reason, $additional_details, $customer_name, $customer_phone, $customer_email);
    
   if ($stmt->execute()) {
        echo json_encode(['success' => true, 'return_id' => $return_id]);
    } else {
        // Output the specific MySQL error for debugging
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]); 
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
    <title>Returns & Refunds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-end: #06b6d4;
            --dark: #374151;
            --dark-end: #4b5563;
            --danger: #ef4444;
            --danger-end: #dc2626;
            --info-bg: #dbeafe;
            --info-border: #3b82f6;
            --info-text: #1e40af;
        }

        body { background-color: #f8f9fa; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.07); border-radius: 12px; }
        .section-title { color: var(--dark); border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; }
        .form-label { font-weight: 600; color: var(--dark); }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25); }

        .returns-header { 
            background: linear-gradient(135deg, var(--dark) 0%, var(--dark-end) 100%); 
            color: white; 
            border-radius: 12px 12px 0 0; 
        }

        .btn-custom { 
            border: none; 
            border-radius: 25px; 
            padding: 15px 30px; 
            font-weight: 600; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            transition: all 0.3s ease; 
            color: white;
        }
        
        .submit-btn { 
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-end) 100%); 
        }
        .submit-btn:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(16,185,129,0.4); 
            color: white;
        }

        .cancel-btn {
            background: linear-gradient(135deg, var(--danger) 0%, var(--danger-end) 100%);
        }
        .cancel-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239,68,68,0.3);
            color: white;
        }

        .back-arrow {
            color: var(--dark);
            text-decoration: none;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-arrow:hover { color: var(--primary); transform: translateX(-5px); }
        
        .info-box {
            background: linear-gradient(135deg, var(--info-bg) 0%, #e0f2fe 100%);
            border: 2px solid var(--info-border);
            border-radius: 12px;
            padding: 15px;
        }
        .info-text { color: var(--info-text); font-weight: 500; }
        
        .modal-content { border-radius: 12px; }
        .modal .submit-btn { padding: 10px 20px; }
    </style>
</head>

<body>
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <a href="viewstock.php" class="back-arrow">
                    <i class="bi bi-arrow-left me-2"></i>Returns & Refunds
                </a>

                <div class="card shadow-sm">
                    <div class="returns-header p-4 text-center">
                        <h2 class="mb-0 d-flex align-items-center justify-content-center">
                            <i class="bi bi-arrow-return-left me-3"></i>Submit Return Request
                        </h2>
                    </div>

                    <div class="card-body p-4">
                        <div class="info-box mb-4 text-center">
                            <p class="mb-0 info-text">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>NOTE:</strong> Refunds can be taken only by visiting the store once the request is approved
                            </p>
                        </div>

                        <form id="returnsForm" class="row g-3 needs-validation" novalidate>
                            <div class="col-12">
                                <h5 class="section-title mt-3"><i class="bi bi-receipt me-2 text-primary"></i>Order Information</h5>
                            </div>
                            <div class="col-md-6">
                                <label for="orderId" class="form-label">Order ID *</label>
                                <input type="text" class="form-control" id="orderId" name="orderId" placeholder="e.g. ORD-12345678" required pattern="^[a-zA-Z0-9-]+$">
                                <div class="invalid-feedback">Please enter a valid Order ID.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="orderDate" class="form-label">Order Date *</label>
                                <input type="date" class="form-control" id="orderDate" name="orderDate" required>
                                <div class="invalid-feedback">Please select the order date.</div>
                            </div>

                            <div class="col-12">
                                <h5 class="section-title mt-4"><i class="bi bi-box me-2 text-primary"></i>Item Details</h5>
                            </div>
                            <div class="col-12">
                                <label for="itemId" class="form-label">Item ID to return *</label>
                                <input type="text" class="form-control" id="itemId" name="itemId" placeholder="e.g. 1, 2, 3" required pattern="^[0-9]+$">
                                <div class="invalid-feedback">Please enter a valid Item ID (numbers only).</div>
                            </div>
                            <div class="col-md-6">
                                <label for="quantity" class="form-label">Quantity to Return *</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                                <div class="invalid-feedback">Please enter a valid quantity.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="condition" class="form-label">Item Condition *</label>
                                <select class="form-select" id="condition" name="condition" required>
                                    <option selected disabled value="">Choose condition...</option>
                                    <option value="unused">Unused - Original packaging</option>
                                    <option value="lightly_used">Lightly used - Good condition</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="defective">Defective</option>
                                </select>
                                <div class="invalid-feedback">Please select the item condition.</div>
                            </div>

                            <div class="col-12">
                                <h5 class="section-title mt-4"><i class="bi bi-chat-left-text me-2 text-primary"></i>Return Details</h5>
                            </div>
                            <div class="col-12">
                                <label for="returnReason" class="form-label">Reason for return *</label>
                                <select class="form-select mb-3" id="returnReason" name="returnReason" required>
                                    <option selected disabled value="">Select a reason...</option>
                                    <option value="changed_mind">Changed my mind</option>
                                    <option value="wrong_item">Wrong item delivered</option>
                                    <option value="damaged">Item arrived damaged</option>
                                    <option value="defective">Product defective</option>
                                    <option value="not_as_described">Not as described</option>
                                    <option value="size_issue">Size/fit issue</option>
                                    <option value="other">Other (please specify below)</option>
                                </select>
                                <div class="invalid-feedback">Please select a reason for return.</div>
                            </div>
                            <div class="col-12">
                                <label for="additionalDetails" class="form-label">Additional Details</label>
                                <textarea class="form-control" id="additionalDetails" name="additionalDetails" rows="4" placeholder="Please provide additional details here..."></textarea>
                                <div class="form-text">This is required if you select 'Other' as the reason.</div>
                            </div>

                            <div class="col-12">
                                <h5 class="section-title mt-4"><i class="bi bi-person me-2 text-primary"></i>Contact Information</h5>
                            </div>
                            <div class="col-md-6">
                                <label for="customerName" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="customerName" name="customerName" required>
                                <div class="invalid-feedback">Please enter your full name.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="customerPhone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="customerPhone" name="customerPhone" placeholder="+94 XX XXX XXXX" required pattern="^\+?[0-9]{10,14}$">
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>
                            <div class="col-12">
                                <label for="customerEmail" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="customerEmail" name="customerEmail" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>

                            <div class="col-12 d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-custom cancel-btn" onclick="window.location.href='viewstock.php'">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-custom submit-btn">
                                    <i class="bi bi-send me-2"></i>Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-5">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h3 class="mt-3 mb-3 text-success">Request Submitted!</h3>
                <p class="text-muted mb-4" id="success-message">
                    Your return request has been submitted successfully. We'll contact you within 24-48 hours.
                </p>
                <button type="button" class="btn submit-btn" onclick="window.location.href='viewstock.php'">
                    <i class="bi bi-house me-2"></i>Go to Home
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('returnsForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const form = this;
            const returnReason = document.getElementById('returnReason');
            const additionalDetails = document.getElementById('additionalDetails');

            // Custom validation for 'Other' reason
            if (returnReason.value === 'other' && additionalDetails.value.trim() === '') {
                additionalDetails.setCustomValidity('Please provide details when selecting "Other" as the reason.');
            } else {
                additionalDetails.setCustomValidity('');
            }

            if (!form.checkValidity()) {
                event.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            // Submit form data
            try {
                const formData = new FormData(form);
                const response = await fetch('returns.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Update success message with return ID
                    document.getElementById('success-message').innerHTML = `
                        Your return request has been submitted successfully!<br>
                        Return ID: <strong>${result.return_id}</strong><br>
                        We'll contact you within 24-48 hours.
                    `;

                    // Show success modal
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                } else {
                    alert('Error submitting return request: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error submitting return request. Please try again.');
            }

            form.classList.add('was-validated');
        });

        // Clear 'Other' validation if reason changes or user starts typing
        document.getElementById('returnReason').addEventListener('change', function() {
            document.getElementById('additionalDetails').setCustomValidity('');
        });
        document.getElementById('additionalDetails').addEventListener('input', function() {
            this.setCustomValidity('');
        });
    </script>
</body>
</html>