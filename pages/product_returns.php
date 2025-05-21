<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

function insertJournalEntry($pdo, $date, $account, $desc, $debit, $credit, $refType, $refId) {
    $stmt = $pdo->prepare("INSERT INTO general_journal 
        (entry_date, account, description, debit, credit, reference_type, reference_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $account, $desc, $debit, $credit, $refType, $refId]);
}



$entries = $pdo->query("SELECT * FROM general_journal ORDER BY entry_date DESC")->fetchAll();
// 🟢 Handle new return submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_return'])) {
    $product_id = $_POST['product_id'];
    $return_qty = (int)($_POST['return_qty'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    $return_date = $_POST['return_date'] ?? '';

    if ($product_id && $return_qty > 0) {
        $stmt = $pdo->prepare("SELECT name, quantity, supplier_name, cost_price FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            $error = "❌ Product not found.";
        } elseif ($return_qty > $product['quantity']) {
            $error = "❌ Return quantity exceeds available stock.";
        } else {
            // Insert into product_returns
            $stmt = $pdo->prepare("INSERT INTO product_returns 
                (product_id, return_qty, received_qty, resolved, reason, return_date) 
                VALUES (?, ?, 0, 0, ?, ?)");
            $stmt->execute([$product_id, $return_qty, $reason, $return_date]);

            // Update stock and due quantity
            $pdo->prepare("UPDATE products 
                SET quantity = quantity - ?, 
                    payment_due_quantity = payment_due_quantity - ? 
                WHERE id = ?")->execute([$return_qty, $return_qty, $product_id]);

            // ✅ Insert into general journal
            $amount = $return_qty * $product['cost_price'];
            $desc = "Product return - " . $product['name'];

            insertJournalEntry($pdo, $return_date, "Purchase Returns", $desc, $amount, 0, "Product Return", $product_id);
            insertJournalEntry($pdo, $return_date, "Inventory", $desc, 0, $amount, "Product Return", $product_id);

            $success = "✅ Product return submitted, stock updated, and journal entry recorded.";
        }
    } else {
        $error = "❌ Please fill all required fields.";
    }
}

// Load product list
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
