<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll();
$supplier_id = $_GET['supplier_id'] ?? null;

// Handle Mark as Paid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {
    $purchase_id = $_POST['purchase_id'];
    $stmt = $pdo->prepare("UPDATE products SET is_paid = 1, paid_at = NOW() WHERE id = ?");
    $stmt->execute([$purchase_id]);
    header("Location: supplier_payments.php?supplier_id=" . $supplier_id);
    exit;
}

$pending = $paid = [];
$pending_total = $paid_total = 0;
if ($supplier_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE supplier_id = ? AND is_paid = 0 ORDER BY created_at DESC");
    $stmt->execute([$supplier_id]);
    $pending = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM products WHERE supplier_id = ? AND is_paid = 1 ORDER BY paid_at DESC");
    $stmt->execute([$supplier_id]);
    $paid = $stmt->fetchAll();

    // Calculate outstanding
    foreach ($pending as $p) {
        $discount = ($p['discount_percent'] ?? 0) / 100 * $p['price'] * $p['quantity'];
        $pending_total += $p['price'] * $p['quantity'] - $discount;
    }

    foreach ($paid as $p) {
        $discount = ($p['discount_percent'] ?? 0) / 100 * $p['price'] * $p['quantity'];
        $paid_total += $p['price'] * $p['quantity'] - $discount;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Supplier Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f8fa;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
        }
        h3, h5 {
            font-weight: 600;
        }
        .summary-box {
            padding: 15px 25px;
            border-radius: 10px;
        }
        .table th, .table td {
            vertical-align: middle !important;
        }
    </style>
</head>
<body class="p-4">
<div class="container">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary">💵 Supplier Payment Management</h3>
            <a href="dashboard.php" class="btn btn-outline-secondary">← Back</a>
        </div>

        <!-- Select Supplier -->
        <form method="GET" class="mb-4">
            <select name="supplier_id" class="form-select shadow-sm" onchange="this.form.submit()" required>
                <option value="">-- Select Supplier --</option>
                <?php foreach ($suppliers as $sup): ?>
                    <option value="<?= $sup['id'] ?>" <?= $sup['id'] == $supplier_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sup['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($supplier_id): ?>
            <!-- Summary -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="bg-light summary-box border-start border-primary border-4 shadow-sm">
                        <h6 class="text-muted mb-1">Total Paid</h6>
                        <h5 class="text-success">₨<?= number_format($paid_total, 2) ?></h5>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light summary-box border-start border-warning border-4 shadow-sm">
                        <h6 class="text-muted mb-1">Pending Payment</h6>
                        <h5 class="text-danger">₨<?= number_format($pending_total, 2) ?></h5>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light summary-box border-start border-dark border-4 shadow-sm">
                        <h6 class="text-muted mb-1">Total (All Time)</h6>
                        <h5 class="text-dark fw-bold">₨<?= number_format($paid_total + $pending_total, 2) ?></h5>
                    </div>
                </div>
            </div>

            <!-- Pending Payments -->
            <h5 class="text-danger mb-3">📌 Pending Bills</h5>
            <?php if (count($pending)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-danger">
                        <tr>
                            <th>#</th><th>Name</th><th>Barcode</th><th>Qty</th>
                            <th>Price</th><th>Discount</th><th>Total</th><th>Date</th><th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pending as $i => $p):
                            $discount = ($p['discount_percent'] ?? 0) / 100 * $p['price'] * $p['quantity'];
                            $total = $p['price'] * $p['quantity'] - $discount;
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($p['name']) ?></td>
                                <td><?= $p['barcode'] ?></td>
                                <td><?= $p['quantity'] ?></td>
                                <td>₨<?= number_format($p['price'], 2) ?></td>
                                <td><?= $p['discount_percent'] ?? 0 ?>%</td>
                                <td><strong>₨<?= number_format($total, 2) ?></strong></td>
                                <td><?= date('d-M-Y', strtotime($p['created_at'])) ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="purchase_id" value="<?= $p['id'] ?>">
                                        <button type="submit" name="mark_paid" class="btn btn-sm btn-success">Mark as Paid</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-success">✅ No pending payments.</div>
            <?php endif; ?>

            <!-- Paid History -->
            <h5 class="text-muted mt-5 mb-3">📖 Payment History</h5>
            <?php if (count($paid)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-secondary">
                        <tr>
                            <th>#</th><th>Name</th><th>Barcode</th><th>Qty</th>
                            <th>Price</th><th>Discount</th><th>Paid</th><th>Paid Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($paid as $i => $p):
                            $discount = ($p['discount_percent'] ?? 0) / 100 * $p['price'] * $p['quantity'];
                            $total = $p['price'] * $p['quantity'] - $discount;
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($p['name']) ?></td>
                                <td><?= $p['barcode'] ?></td>
                                <td><?= $p['quantity'] ?></td>
                                <td>₨<?= number_format($p['price'], 2) ?></td>
                                <td><?= $p['discount_percent'] ?? 0 ?>%</td>
                                <td><strong>₨<?= number_format($total, 2) ?></strong></td>
                                <td><?= date('d-M-Y', strtotime($p['paid_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-secondary">No payments recorded yet.</div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="mt-4 d-flex gap-3">
                <button onclick="window.print()" class="btn btn-outline-dark">🖨 Print Invoice</button>
                <a href="export_supplier_payments.php?supplier_id=<?= $supplier_id ?>" class="btn btn-outline-primary">⬇ Export CSV</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
