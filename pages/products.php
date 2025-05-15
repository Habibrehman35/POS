<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require '../config.php';

$products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h3 {
            font-weight: bold;
        }
        .table th {
            text-align: center;
        }
        .table td {
            vertical-align: middle;
            text-align: center;
        }
        img.product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body class="p-4">

 <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>📦 Product Management</h3>
    <div>
        <a href="dashboard.php" class="btn btn-outline-secondary me-2">← Back to Dashboard</a>
        <a href="add_product.php" class="btn btn-success">➕ Add Product</a>
    </div>
</div>


    <?php if (count($products)): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Barcode</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Discount</th>
                    <th>Tax</th>
                    <th>Expiry</th>
                      <th><strong>Vendor</strong></th> 
                    <th>Actions</th>
                    
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <?php if (!empty($p['image'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" class="product-img" alt="Product Image">
                        <?php else: ?>
                            <span class="text-muted">No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['barcode']) ?></td>
                    <td>$<?= number_format($p['price'], 2) ?></td>
                    <td><?= (int) $p['quantity'] ?></td>
                    <td><?= number_format($p['discount_percent'], 2) ?>%</td>
                    <td><?= number_format($p['tax_percent'], 2) ?>%</td>
                    <td><?= $p['expiry_date'] ?: 'N/A' ?></td>
                       <td><?= htmlspecialchars($p['supplier_name'] ?? 'N/A') ?></td>

                    <td>
                        <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="delete_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                 
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No products added yet.</div>
    <?php endif; ?>

   

</body>
</html>
