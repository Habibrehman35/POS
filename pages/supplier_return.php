<?php
// File: supplier_return.php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = $error = '';

// Handle supplier return form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_return'])) {
    $product_id = $_POST['product_id'];
    $return_qty = (int)($_POST['return_qty'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    $return_date = $_POST['return_date'] ?? date('Y-m-d');

    $stmt = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $current_qty = $stmt->fetchColumn();

    if (!$product_id || $return_qty < 1 || $return_qty > $current_qty) {
        $error = "❌ Invalid return quantity.";
    } else {
        // Insert return
        $pdo->prepare("INSERT INTO supplier_returns (product_id, return_qty, reason, return_date) 
            VALUES (?, ?, ?, ?)")
            ->execute([$product_id, $return_qty, $reason, $return_date]);

        // Subtract from stock
        $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?")
            ->execute([$return_qty, $product_id]);

        $success = "✅ Product return to supplier recorded.";
    }
}

$products = $pdo->query("SELECT id, name, quantity, supplier_name FROM products ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Supplier Return</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 shadow-sm rounded">
    <h4 class="mb-4">🔁 Supplier Return</h4>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form method="POST" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-select" required onchange="updateFields(this)">
                <option value="">-- Select Product --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" data-qty="<?= $p['quantity'] ?>" data-sup="<?= htmlspecialchars($p['supplier_name']) ?>">
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label>Available</label>
            <input type="text" id="stockQty" class="form-control" readonly>
        </div>
        <div class="col-md-3">
            <label>Vendor</label>
            <input type="text" id="supplierName" class="form-control" readonly>
        </div>
        <div class="col-md-2">
            <label>Return Qty</label>
            <input type="number" name="return_qty" min="1" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label>Return Date</label>
            <input type="date" name="return_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="col-md-5">
            <label>Reason</label>
            <input type="text" name="reason" class="form-control">
        </div>
        <div class="col-md-12 text-end">
            <button name="submit_return" class="btn btn-danger">➕ Submit Return</button>
        </div>
    </form>
</div>

<script>
function updateFields(sel) {
    let opt = sel.options[sel.selectedIndex];
    document.getElementById('stockQty').value = opt.getAttribute('data-qty') || '';
    document.getElementById('supplierName').value = opt.getAttribute('data-sup') || '';
}
</script>
</body>
</html>
