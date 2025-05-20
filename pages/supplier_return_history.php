<?php
// File: supplier_return_history.php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = $error = '';

// Handle receive logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_received'])) {
    $return_id = $_POST['return_id'];
    $received_qty = (int)$_POST['received_qty'];

    $stmt = $pdo->prepare("SELECT * FROM supplier_returns WHERE id = ?");
    $stmt->execute([$return_id]);
    $return = $stmt->fetch();

    if ($return && !$return['resolved']) {
        $total_received = $return['received_qty'] + $received_qty;

        if ($total_received > $return['return_qty']) {
            $error = "❌ Cannot receive more than returned.";
        } else {
            $resolved = ($total_received == $return['return_qty']) ? 1 : 0;

            $pdo->prepare("UPDATE supplier_returns 
                SET received_qty = ?, resolved = ?, received_at = NOW()
                WHERE id = ?")
                ->execute([$total_received, $resolved, $return_id]);

            $pdo->prepare("UPDATE products 
                SET quantity = quantity + ?
                WHERE id = ?")
                ->execute([$received_qty, $return['product_id']]);

            $success = "✅ Supplier return marked as received.";
        }
    } else {
        $error = "❌ Invalid return or already resolved.";
    }
}

// Fetch returns
$returns = $pdo->query("
    SELECT r.*, p.name AS product_name, p.supplier_name 
    FROM supplier_returns r
    JOIN products p ON r.product_id = p.id
    ORDER BY r.id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Supplier Return History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 shadow-sm rounded">
    <h4 class="mb-4">📋 Supplier Return History</h4>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
        <tr>
            <th>#</th><th>Product</th><th>Vendor</th><th>Returned</th>
            <th>Received</th><th>Remaining</th><th>Status</th><th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($returns as $i => $r): 
            $remaining = $r['return_qty'] - $r['received_qty'];
            $status = $r['resolved'] ? '✅ Completed' : '❌ Pending';
            $badge = $r['resolved'] ? 'bg-success' : 'bg-warning text-dark';
        ?>
            <tr class="<?= $r['resolved'] ? 'table-success' : '' ?>">
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td><?= htmlspecialchars($r['supplier_name']) ?></td>
                
                <td><?= $r['return_qty'] ?></td>
                <td><?= $r['received_qty'] ?></td>
                <td><strong><?= $remaining ?></strong></td>
                <td><span class="badge <?= $badge ?>"><?= $status ?></span></td>
                <td>
                    <?php if (!$r['resolved']): ?>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="return_id" value="<?= $r['id'] ?>">
                            <input type="number" name="received_qty" class="form-control form-control-sm" value="1" min="1" max="<?= $remaining ?>" required>
                            <button name="mark_received" class="btn btn-sm btn-success">✔ Receive</button>
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
