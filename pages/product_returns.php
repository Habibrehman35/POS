<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Handle new return submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_return'])) {
    $product_id = $_POST['product_id'];
    $return_qty = (int)($_POST['return_qty'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    $return_date = $_POST['return_date'] ?? '';

    if ($product_id && $return_qty > 0) {
        $stmt = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $current_qty = $stmt->fetchColumn();

        if ($current_qty === false) {
            $error = "❌ Product not found.";
        } elseif ($return_qty > $current_qty) {
            $error = "❌ Return quantity exceeds available stock.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO product_returns (product_id, return_qty, received_qty, resolved, reason, return_date) VALUES (?, ?, 0, 0, ?, ?)");
            $stmt->execute([$product_id, $return_qty, $reason, $return_date]);

          $pdo->prepare("UPDATE products 
  SET quantity = quantity - ?, 
      payment_due_quantity = payment_due_quantity - ? 
  WHERE id = ?")->execute([
      $return_qty, $return_qty, $product_id
]);


            $success = "✅ Product return submitted and stock updated.";
        }
    } else {
        $error = "❌ Please fill all required fields.";
    }
}

$products = $pdo->query("SELECT id, name, supplier_name, quantity FROM products ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Returns</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .vendor-box { font-weight: bold; font-size: 14px; }
    </style>
</head>
<body class="bg-light p-4">
<div class="container">
    <h3>🔁 Product Return</h3>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form method="POST" class="card p-4 shadow-sm mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label>Product</label>
                <select class="form-select" name="product_id" required onchange="updateInfo(this)">
                    <option value="">-- Select Product --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" data-supplier="<?= $p['supplier_name'] ?>" data-stock="<?= $p['quantity'] ?>">
                            <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Available</label>
                <input type="text" id="stockQty" class="form-control text-center fw-bold" readonly>
            </div>
            <div class="col-md-3">
                <label>Vendor</label>
                <input type="text" id="vendorName" class="form-control" readonly>
            </div>
            <div class="col-md-2">
                <label>Return Qty</label>
                <input type="number" class="form-control" name="return_qty" min="1" required>
            </div>
            <div class="col-md-3">
                <label>Return Date</label>
                <input type="date" class="form-control" name="return_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-5">
                <label>Reason</label>
                <input type="text" class="form-control" name="reason" placeholder="Optional reason">
            </div>
            <div class="col-md-12 text-end mt-3">
                <button name="submit_return" class="btn btn-danger">➕ Submit Return</button>
            </div>
        </div>
    </form>
</div>

<script>
function updateInfo(sel) {
    let option = sel.options[sel.selectedIndex];
    document.getElementById('vendorName').value = option.getAttribute('data-supplier') || '';
    document.getElementById('stockQty').value = option.getAttribute('data-stock') || '';
}
</script>
</body>
</html>
