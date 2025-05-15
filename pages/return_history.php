<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_received'])) {
    $return_id = $_POST['return_id'];
    $received_qty = (int)$_POST['received_qty'];

    $stmt = $pdo->prepare("SELECT return_qty, received_qty, product_id FROM product_returns WHERE id = ?");
    $stmt->execute([$return_id]);
    $return = $stmt->fetch();

    if ($return) {
        $total = $return['received_qty'] + $received_qty;
        if ($total > $return['return_qty']) {
            $error = "❌ Received exceeds return.";
        } else {
            $resolved = ($total === $return['return_qty']) ? 1 : 0;

            $pdo->prepare("UPDATE product_returns SET received_qty = ?, resolved = ?, received_at = NOW() WHERE id = ?")
                ->execute([$total, $resolved, $return_id]);

            $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?")
                ->execute([$received_qty, $return['product_id']]);

            $success = "✅ Quantity received and updated.";
        }
    }
}

$returns = $pdo->query("
    SELECT r.*, p.name AS product_name, p.supplier_name
    FROM product_returns r
    JOIN products p ON r.product_id = p.id
    ORDER BY r.id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Return History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 shadow rounded">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>📦 Return History & Receive</h4>
        <a href="dashboard.php" class="btn btn-outline-dark">← Back</a>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <table class="table table-bordered align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>#</th><th>Product</th><th>Vendor</th><th>Returned</th><th>Received</th><th>Remaining</th><th>Status</th><th>Update</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($returns as $i => $r):
            $remaining = $r['return_qty'] - $r['received_qty'];
            $resolved = $r['resolved'] ? '✅ Full' : '⏳ Partial';
            $badge = $r['resolved'] ? 'bg-success' : 'bg-warning';
        ?>
            <tr class="<?= $r['resolved'] ? 'table-success' : 'table-warning' ?>">
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td><?= htmlspecialchars($r['supplier_name'] ?? 'N/A') ?></td>
                <td><?= $r['return_qty'] ?></td>
                <td><?= $r['received_qty'] ?></td>
                <td><?= $remaining ?></td>
                <td><span class="badge <?= $badge ?>"><?= $resolved ?></span></td>
                <td>
                    <?php if (!$r['resolved']): ?>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="return_id" value="<?= $r['id'] ?>">
                            <input type="number" name="received_qty" class="form-control form-control-sm" min="1" max="<?= $remaining ?>" required>
                            <button class="btn btn-sm btn-success" name="update_received">➕</button>
                        </form>
                    <?php else: ?>
                        <span class="text-muted">✔ Done</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
