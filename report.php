<?php
// Database connection
$host = "localhost";
$user = "root";   // your DB username
$pass = "";       // your DB password
$db   = "gesco_db";   // your database name
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}
//////

$manager = $conn->query("SELECT order_items.order_id, order_items.price, sum(order_items.stock_quantity) as qty, SUM(order_items.total_amount) as tot, order_items.item_id from order_items INNER JOIN items on items.id = order_items.item_id GROUP by order_items.item_id;");


/////
//$manager = $conn->query("SELECT * FROM users where role = 'manager'");
$success = $error = "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Convert to PDF - Bootstrap Example</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- jsPDF and html2pdf.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="bg-light">

  <div class="container mt-5">
    <div class="card shadow-lg p-4">
      <h3 class="text-center text-primary mb-4">🧾 Gesco Sales Report</h3>

      <!-- Content to Convert and fetch data here that should be downloaded -->
      <div id="content-to-pdf" class="border rounded p-3 bg-white">
        <h5>Sales Report</h5>
         <!-- manager Table -->
            <div class="card">
                <div class="card-header">Sales Table</div>
                <div class="card-body table-responsive">
                    <?php if ($manager && $manager->num_rows > 0): ?>
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <?php 
                                    if($manager){
                                        foreach($manager->fetch_fields() as $field);
                                    ?>
                                        
                                    <?php
                                        
                                    }
                                    ?>
                                    <th>Item ID</th>
                                    <th>Order ID</th>
                                    <th>Price</th>
                                    <th>Stock Quantity</th>
                                    <th>Total Amount</th>
                                   

                                </tr>
                               
                            </thead>
                            <tbody>
                               <?php
                                 // Reset pointer for printing rows
                                if ($manager) $manager->data_seek(0);
                                while ($row = $manager->fetch_assoc()) { 
                                ?>
                                <tr>
                                <!-- Use explicit column names from the SELECT query in the order specified in the header -->
                               
                                <td><?php echo htmlspecialchars($row['item_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['price']); ?></td>
                                <td><?php echo htmlspecialchars($row['qty']); ?></td>
                                <td><?php echo htmlspecialchars($row['tot']); ?></td>
                               
                                <!-- Hide the password data, as it's not useful to display to the admin -->
                                <td class="d-none"><?php echo htmlspecialchars($row['password']); ?></td>
                                
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                 <?php else: ?>
                        <p class="text-muted">No manager records found.</p>
                    <?php endif; ?>
                </div>
            </div>
      </div>

      <!-- Buttons -->
      <div class="text-center mt-4">
        <button class="btn btn-success" id="download-pdf">
          <i class="bi bi-file-earmark-pdf"></i> Download as PDF
        </button>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS (optional for styling only) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- PDF generation logic -->
  <script>
    document.getElementById('download-pdf').addEventListener('click', function () {
      const element = document.getElementById('content-to-pdf');
      const options = {
        margin: 0.5,
        filename: 'document.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
      };
      html2pdf().from(element).set(options).save();
    });


  </script>
</body>
</html>