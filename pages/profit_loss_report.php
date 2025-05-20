<?php
require '../config.php';

// Default date range: This month
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');

// Fetch Total Sales & COGS
$stmt = $pdo->prepare("
    SELECT 
        SUM(si.total_price) AS total_sales
    FROM sale_items si
    JOIN sales s ON s.id = si.sale_id
    WHERE DATE(s.created_at) BETWEEN :from AND :to
");
$stmt->execute(['from' => $from, 'to' => $to]);
$data = $stmt->fetch();


$totalSales = $data['total_sales'] ?? 0;
$totalCOGS  = 0;
$grossProfit = $totalSales - $totalCOGS;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profit & Loss Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h3 class="mb-4">📊 Profit & Loss Report</h3>

<form class="row g-3 mb-4" method="GET">
    <div class="col-md-3">
        <label class="form-label">From:</label>
        <input type="date" name="from" class="form-control" value="<?= $from ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">To:</label>
        <input type="date" name="to" class="form-control" value="<?= $to ?>">
    </div>
    <div class="col-md-3 align-self-end">
        <button class="btn btn-primary">🔍 Generate Report</button>
    </div>
    <div class="col-md-3 align-self-end">
        <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
</form>

<table class="table table-bordered w-50">
    <tr>
        <th>Total Sales</th>
        <td>$<?= number_format($totalSales, 2) ?></td>
    </tr>
    <tr>
        <th>Cost of Goods Sold (COGS)</th>
        <td>$<?= number_format($totalCOGS, 2) ?></td>
    </tr>
    <tr class="table-info">
        <th>Gross Profit</th>
        <td><strong>$<?= number_format($grossProfit, 2) ?></strong></td>
    </tr>
</table>

</body>
</html>
