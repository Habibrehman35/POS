<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$products = $pdo->query("SELECT id, name, barcode FROM products ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reprint Barcode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f8fa;
        }
        .container {
            max-width: 700px;
        }
    </style>
</head>
<body class="p-4 bg-light">
<div class="container bg-white shadow-sm p-4 rounded">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>📦 Reprint Product Barcode</h4>
        <a href="dashboard.php" class="btn btn-outline-secondary">← Back</a>
    </div>

    <form method="GET" action="generate_barcode.php" target="_blank" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Select Product</label>
            <select name="id" class="form-select" required>
                <option value="">-- Choose Product --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['name']) ?> (<?= $p['barcode'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="qty" class="form-control" value="1" min="1" required>
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">🖨️ Reprint</button>
        </div>
    </form>
</div>
</body>
</html>
