<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Search & Filter
$search = $_GET['search'] ?? '';
$date = $_GET['date'] ?? '';

$where = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (CAST(id AS CHAR) LIKE ? OR payment_mode LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($date)) {
    $where .= " AND DATE(created_at) = ?";
    $params[] = $date;
}

// Fetch sales data
$stmt = $pdo->prepare("SELECT id, total_amount, payment_mode, created_at FROM sales $where ORDER BY created_at DESC");
$stmt->execute($params);
$sales = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Sales Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .badge-mode { text-transform: capitalize; font-size: 0.9rem; }
        .table th, .table td { vertical-align: middle; }
        .table td { text-align: center; }
    </style>
</head>
<body class="p-4">
<div class="container bg-white p-4 rounded shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-receipt-cutoff me-2"></i>All Sales Records</h4>
        <a href="dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
    </div>

    <!-- 🔎 Filter -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Search Invoice / Mode</label>
            <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. INV001 or cash">
        </div>
        <div class="col-md-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-dark w-100">Filter</button>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <a href="all_sales.php" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
    </form>

    <!-- 🧾 Sales Table -->
    <table class="table table-bordered table-striped text-center">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Invoice No</th>
                <th>Payment Mode</th>
                <th>Total (₨)</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($sales)): ?>
            <?php foreach ($sales as $i => $sale): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>#INV<?= str_pad($sale['id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td><span class="badge bg-info text-dark badge-mode"><?= htmlspecialchars($sale['payment_mode']) ?></span></td>
                    <td><strong>₨ <?= number_format($sale['total_amount'], 2) ?></strong></td>
                    <td><?= date('d-M-Y h:i A', strtotime($sale['created_at'])) ?></td>
                    <td>
                        <a href="print_invoice.php?sale_id=<?= $sale['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-printer-fill"></i> Print
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-muted">No sales records found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
