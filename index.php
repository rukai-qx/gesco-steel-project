<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

    <nav class="navbar navbar-expand-sm bg-light navbar-light">
        <div class="container-fluid">
            <div class="navbar-header col-lg-12">
                <a class="navbar-brand" href="#">PHP Sales Report</a>
            </div>
        </div>
    </nav>
    
    <div class="container my-4">
        
        <form action="report.php" method="POST">
            <div class="row">
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="item">Choose the item ID</label>
                        <select class="form-control" name="item" id="item">
                            <option value="">Select....</option>
                            <?php 
                                // while($row = $itemResult->fetch_assoc()){ 
                                //     echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['itemId']) . '</option>';
                                // } 
                            ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="order">Choose the Order ID</label>
                        <select name="order" class="form-control" id="order">
                            <option value="">Select....</option>
                            <?php 
                                // while($row = $orderResult->fetch_assoc()){ 
                                //     echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['orderId']) . '</option>';
                                // } 
                            ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                </div>

                <div class="col-md-3"></div>
            </div>
        </form>

        <br>
        <h3 class="mt-4">Sales Data Will Appear in 'report.php'</h3>

    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
