<?php
session_start();
require '../config.php';

// Optional: restrict access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .product-card img {
            height: 100px;
            object-fit: contain;
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="p-4">
    <h3 class="mb-4">🗂️ All Products</h3>

    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-3 col-sm-6">
                <div class="product-card">
                    <?php if ($product['image']): ?>
                        <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= $product['name'] ?>" class="img-fluid">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/100x100?text=No+Image" class="img-fluid">
                    <?php endif; ?>

                    <h6><?= htmlspecialchars($product['name']) ?></h6>
                    <p>Price: <strong>$<?= $product['price'] ?></strong></p>
                    <p>Qty: <?= $product['quantity'] ?></p>
                    <?php if ($product['discount_percent'] > 0): ?>
                        <p class="text-success">Discount: <?= $product['discount_percent'] ?>%</p>
                    <?php endif; ?>
                    <?php if ($product['expiry_date']): ?>
                        <p class="text-muted">Expiry: <?= $product['expiry_date'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-4">← Back to Dashboard</a>
</body>
</html>
