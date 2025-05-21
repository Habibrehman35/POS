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



// Handle Quantity Received Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_received'])) {
    $return_id = $_POST['return_id'];
    $received_qty = $_POST['received_qty'];

    $stmt = $pdo->prepare("SELECT return_qty, received_qty FROM product_returns WHERE id = ?");
    $stmt->execute([$return_id]);
    $return = $stmt->fetch();

    if ($return) {
        $total_received = $return['received_qty'] + $received_qty;

        if ($total_received > $return['return_qty']) {
            $error = "❌ Received quantity exceeds returned quantity.";
        } else {
            $resolved = ($total_received == $return['return_qty']) ? 1 : 0;

            // Update return record
            $pdo->prepare("UPDATE product_returns 
                SET received_qty = ?, received_at = NOW(), resolved = ? 
                WHERE id = ?")->execute([$total_received, $resolved, $return_id]);

           // Update product stock AND payment_due_quantity
$pdo->prepare("UPDATE products 
    SET 
        quantity = quantity + ?, 
        payment_due_quantity = payment_due_quantity + ? 
    WHERE id = (SELECT product_id FROM product_returns WHERE id = ?)")
    ->execute([$received_qty, $received_qty, $return_id]);

    // Get product details for journal entry
$productStmt = $pdo->prepare("
    SELECT p.name, p.cost_price 
    FROM products p
    JOIN product_returns r ON r.product_id = p.id
    WHERE r.id = ?
");
$productStmt->execute([$return_id]);
$product = $productStmt->fetch();

if ($product) {
    $amount = $received_qty * $product['cost_price'];
    $desc = "Received returned product - " . $product['name'];

    // Add to general_journal
    insertJournalEntry($pdo, date('Y-m-d'), "Inventory", $desc, $amount, 0, "Return Received", $return_id);
    insertJournalEntry($pdo, date('Y-m-d'), "Purchase Returns", $desc, 0, $amount, "Return Received", $return_id);
}



            $success = "✅ Return updated successfully.";
        }
    }
}





// Filters
$search = $_GET['search'] ?? '';
$date_filter = $_GET['date'] ?? '';

$where = 'WHERE 1=1';
$params = [];

if ($search) {
    $where .= " AND (p.name LIKE ? OR p.supplier_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($date_filter) {
    $where .= " AND r.return_date = ?";
    $params[] = $date_filter;
}

// Fetch data
$stmt = $pdo->prepare("
    SELECT r.*, p.name AS product_name, p.supplier_name
    FROM product_returns r
    JOIN products p ON r.product_id = p.id
    $where
    ORDER BY r.id DESC
");
$stmt->execute($params);
$returns = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Return History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
        }
        .badge {
            font-size: 0.9em;
        }
        .table td, .table th {
            vertical-align: middle;
            text-align: center;
        }
    </style>
</head>
<body class="p-4">
<div class="container bg-white p-4 rounded shadow-sm">
   <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">📦 Product Return History</h3>
    <a href="dashboard.php" class="btn btn-outline-dark">← Back</a>
</div>


    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <!-- Filter -->
    <form method="GET" class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label class="form-label">🔍 Search (Product / Vendor)</label>
            <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. Vendor or Product">
        </div>
        <div class="col-md-3">
            <label class="form-label">📅 Return Date</label>
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date_filter) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-dark w-100">Filter</button>
        </div>
        <div class="col-md-3 text-end">
            <a href="return_history.php" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Vendor</th>
                    <th>Returned</th>
                    <th>Received</th>
                    <th>Remaining</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($returns as $i => $r): 
                    $remaining = $r['return_qty'] - $r['received_qty'];
                    $status = $r['resolved'] ? '✅ Fully Received' : '❌ Pending';
                    $badge_class = $r['resolved'] ? 'bg-success' : 'bg-warning text-dark';
                ?>
                <tr class="<?= $r['resolved'] ? 'table-success' : '' ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($r['product_name']) ?></td>
                    <td><?= htmlspecialchars($r['supplier_name'] ?? 'N/A') ?></td>
                    <td><?= $r['return_qty'] ?></td>
                    <td><?= $r['received_qty'] ?></td>
                    <td><strong><?= $remaining ?></strong></td>
                    <td><?= $r['return_date'] ?></td>
                    <td><span class="badge <?= $badge_class ?>"><?= $status ?></span></td>
                    <td>
                        <?php if (!$r['resolved']): ?>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="return_id" value="<?= $r['id'] ?>">
                            <input type="number" name="received_qty" class="form-control form-control-sm" value="1" min="1" max="<?= $remaining ?>" required>
                            <button name="update_received" class="btn btn-sm btn-success">➕ Update</button>
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

    
</div>
</body>
</html>
