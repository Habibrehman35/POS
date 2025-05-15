<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Optional filter (date range)
$start = $_GET['start_date'] ?? '';
$end = $_GET['end_date'] ?? '';
$query = "SELECT s.*, u.full_name FROM sales s JOIN users u ON s.user_id = u.id WHERE 1=1";
$params = [];

if ($start && $end) {
    $query .= " AND DATE(s.created_at) BETWEEN ? AND ?";
    $params[] = $start;
    $params[] = $end;
}

$query .= " ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sales = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
   <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>📊 Sales Report</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
</div>


    <!-- Filter by date -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Start Date</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($start) ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <label>End Date</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($end) ?>" class="form-control">
        </div>
        <div class="col-md-3 align-self-end">
            <button class="btn btn-primary">Filter</button>
            <a href="report.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <?php if (count($sales)): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Invoice #</th>
                    <th>Cashier</th>
                    <th>Payment</th>
                    <th>Total Amount</th>
                    <th>Date</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td>#<?= $sale['id'] ?></td>
                        <td><?= htmlspecialchars($sale['full_name']) ?></td>
                        <td><?= ucfirst($sale['payment_mode']) ?></td>
                        <td>$<?= number_format($sale['total_amount'], 2) ?></td>
                        <td><?= date("d-M-Y h:i A", strtotime($sale['created_at'])) ?></td>
                        <td><a href="print_invoice.php?sale_id=<?= $sale['id'] ?>" class="btn btn-sm btn-info">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No sales found for the selected period.</div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-outline-secondary mt-4">← Back to Dashboard</a>
</body>
</html>
