<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $return_qty = (int)($_POST['return_qty'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    $return_date = $_POST['return_date'] ?? '';
    $subtract_qty = isset($_POST['subtract_qty']) ? 1 : 0;

    if ($product_id && $return_qty > 0) {
        $stmt = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $current_qty = $stmt->fetchColumn();

        if ($current_qty === false) {
            $error = "❌ Product not found.";
        } elseif ($subtract_qty && $return_qty > $current_qty) {
            $error = "❌ Return quantity exceeds available stock.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO product_returns (product_id, return_qty, reason, return_date, subtract_qty, received_qty, resolved) VALUES (?, ?, ?, ?, ?, 0, 0)");
            $stmt->execute([$product_id, $return_qty, $reason, $return_date, $subtract_qty]);

            if ($subtract_qty) {
                $stmt = $pdo->prepare("UPDATE products SET quantity = GREATEST(quantity - ?, 0) WHERE id = ?");
                $stmt->execute([$return_qty, $product_id]);
            }

            $success = "✅ Product return recorded successfully.";
        }
    } else {
        $error = "❌ Please fill all required fields correctly.";
    }
}

$search = $_GET['search'] ?? '';
$date_filter = $_GET['date'] ?? '';
$where = '';
$params = [];

if ($search) {
    $where .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}
if ($date_filter) {
    $where .= " AND r.return_date = ?";
    $params[] = $date_filter;
}

$products = $pdo->query("SELECT id, name, supplier_name FROM products ORDER BY name")->fetchAll();

$query = "
    SELECT r.*, p.name AS product_name, p.supplier_name 
    FROM product_returns r
    JOIN products p ON r.product_id = p.id
    WHERE 1 $where
    ORDER BY r.id DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$returns = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Returns</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
    <h3 class="mb-4">🔁 Product Return Management</h3>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form method="POST" class="card p-4 mb-4 shadow-sm">
        <h5>Add New Return</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Select Product</label>
                <select name="product_id" class="form-select" required>
                    <option value="">-- Choose Product --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Qty</label>
                <input type="number" name="return_qty" class="form-control" min="1" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Return Date</label>
                <input type="date" name="return_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Reason</label>
                <input type="text" name="reason" class="form-control" placeholder="Optional">
            </div>
            <div class="col-md-4">
                <div class="form-check mt-3">
                    <input type="checkbox" class="form-check-input" name="subtract_qty" id="subtract_qty">
                    <label for="subtract_qty" class="form-check-label">Subtract from stock</label>
                </div>
            </div>
            <div class="col-12 mt-2">
                <button class="btn btn-danger">Submit Return</button>
            </div>
        </div>
    </form>

    <form method="GET" class="row mb-4 g-3">
        <div class="col-md-4">
            <input type="text" name="search" placeholder="Search Product" class="form-control" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date_filter) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-secondary">Filter</button>
        </div>
        <div class="col-md-3 text-end">
            <a href="export_returns.php" class="btn btn-outline-success">⬇ Export to Excel</a>
        </div>
    </form>

    <h5>🧾 Return History</h5>
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Vendor</th>
                <th>Qty</th>
                <th>Reason</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($returns as $i => $r): ?>
            <?php
                $rowClass = ($r['received_qty'] >= $r['return_qty']) ? 'table-success' : 'table-warning';
                $statusLabel = ($r['received_qty'] >= $r['return_qty']) ? 'Fully Received' : 'Pending';
            ?>
            <tr class="<?= $rowClass ?>">
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td><?= htmlspecialchars($r['supplier_name'] ?? 'N/A') ?></td>
                <td><?= $r['return_qty'] ?></td>
                <td><?= htmlspecialchars($r['reason']) ?></td>
                <td><?= $r['return_date'] ?></td>
                <td>
                    <?= $r['received_qty'] ?> / <?= $r['return_qty'] ?>
                    <span class="badge <?= $r['received_qty'] >= $r['return_qty'] ? 'bg-success' : 'bg-warning' ?>">
                        <?= $statusLabel ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-outline-dark mt-3">← Back to Dashboard</a>
</div>
</body>
</html>
