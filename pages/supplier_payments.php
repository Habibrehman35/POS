<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// ✅ Journal Entry Helper
function insertJournalEntry($pdo, $date, $account, $desc, $debit, $credit, $refType, $refId) {
    $stmt = $pdo->prepare("INSERT INTO general_journal 
        (entry_date, account, description, debit, credit, reference_type, reference_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $account, $desc, $debit, $credit, $refType, $refId]);
}

$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll();
$supplier_id = $_GET['supplier_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_supplier'])) {
    $product_id = $_POST['product_id'];
    $payment_amount = $_POST['payment_amount'];
    $payment_method = $_POST['payment_method'];
    $note = $_POST['note'] ?? '';
    $date = date('Y-m-d');

    // Insert transaction
    $stmt = $pdo->prepare("INSERT INTO supplier_payment_transactions (product_id, amount_paid, payment_method, note, paid_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$product_id, $payment_amount, $payment_method, $note, $date]);

    // Check if now fully paid
    $stmt = $pdo->prepare("SELECT SUM(amount_paid) FROM supplier_payment_transactions WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $total_paid = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT price, payment_due_quantity FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    $due_total = $product['price'] * $product['payment_due_quantity'];

    if ($total_paid >= $due_total) {
        $pdo->prepare("UPDATE products SET is_paid = 1, paid_at = NOW() WHERE id = ?")->execute([$product_id]);
    }

    // Insert journal
    $desc = "Supplier Payment for Product ID #$product_id";
    insertJournalEntry($pdo, $date, "Accounts Payable", $desc, $payment_amount, 0, "Supplier Payment", $product_id);
    insertJournalEntry($pdo, $date, $payment_method, $desc, 0, $payment_amount, "Supplier Payment", $product_id);

    header("Location: supplier_payments.php?supplier_id=$supplier_id");
    exit;
}

// Fetch product-based balances
$pending = $paid = [];
$pending_total = $paid_total = 0;

if ($supplier_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE supplier_id = ? ORDER BY created_at DESC");
    $stmt->execute([$supplier_id]);
    $all = $stmt->fetchAll();

    foreach ($all as $p) {
        $qty = $p['payment_due_quantity'] ?? $p['quantity'];
        $discount = ($p['discount_percent'] ?? 0) / 100 * $p['price'] * $qty;
        $total = $p['price'] * $qty - $discount;

        $stmt = $pdo->prepare("SELECT SUM(amount_paid) FROM supplier_payment_transactions WHERE product_id = ?");
        $stmt->execute([$p['id']]);
        $paid_amount = $stmt->fetchColumn() ?? 0;

        $p['total'] = $total;
        $p['paid'] = $paid_amount;
        $p['remaining'] = max(0, $total - $paid_amount);

      $paid_total += $paid_amount;

if ($p['is_paid']) {
    $paid[] = $p; // Fully paid records
} else {
    $pending[] = $p; // Still pending
    $pending_total += $p['remaining'];
}

    }

    // ✅ Fetch payment history
    $stmt = $pdo->prepare("
        SELECT t.*, p.name 
        FROM supplier_payment_transactions t
        JOIN products p ON t.product_id = p.id
        WHERE p.supplier_id = ?
        ORDER BY t.paid_at DESC
    ");
    $stmt->execute([$supplier_id]);
    $payment_history = $stmt->fetchAll();
} else {
    $payment_history = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Supplier Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container bg-white p-4 shadow-sm rounded">
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-primary mb-0">💵 Supplier Payment Management</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary">← Back</a>
</div>


    <!-- Dropdown -->
    <form method="GET" class="mb-4">
        <select name="supplier_id" class="form-select" onchange="this.form.submit()" required>
            <option value="">-- Select Supplier --</option>
            <?php foreach ($suppliers as $s): ?>
                <option value="<?= $s['id'] ?>" <?= $supplier_id == $s['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($supplier_id): ?>
        <!-- Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="alert alert-success"><b>Paid:</b> ₨<?= number_format($paid_total, 2) ?></div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-warning"><b>Pending:</b> ₨<?= number_format($pending_total, 2) ?></div>
            </div>
        </div>

        <!-- Pending Table -->
        <h5>📌 Pending Payments</h5>
        <?php if (count($pending)): ?>
            <table class="table table-bordered align-middle">
                <thead class="table-warning">
                <tr><th>#</th><th>Name</th><th>Qty</th><th>Total</th><th>Paid</th><th>Remaining</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php foreach ($pending as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= $p['payment_due_quantity'] ?></td>
                        <td>₨<?= number_format($p['total'], 2) ?></td>
                        <td>₨<?= number_format($p['paid'], 2) ?></td>
                        <td>₨<?= number_format($p['remaining'], 2) ?></td>
                        <td>
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="number" name="payment_amount" value="<?= $p['remaining'] ?>" class="form-control form-control-sm" step="0.01" required>
                                <select name="payment_method" class="form-select form-select-sm" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                </select>
                                <input type="text" name="note" class="form-control form-control-sm" placeholder="Note (optional)">
                                <button class="btn btn-sm btn-success" name="pay_supplier">✔ Pay</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-success">✅ No pending bills.</p>
        <?php endif; ?>

        <!-- Payment History -->
        <h5 class="mt-5">📖 Payment History</h5>
        <?php if (count($payment_history)): ?>
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-secondary">
                <tr><th>#</th><th>Product</th><th>Amount</th><th>Method</th><th>Note</th><th>Date</th></tr>
                </thead>
                <tbody>
                <?php foreach ($payment_history as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td>₨<?= number_format($row['amount_paid'], 2) ?></td>
                        <td><?= htmlspecialchars($row['payment_method']) ?></td>
                        <td><?= htmlspecialchars($row['note']) ?></td>
                        <td><?= date('d-M-Y', strtotime($row['paid_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No transactions yet.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
