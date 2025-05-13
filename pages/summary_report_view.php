<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
    <h3 class="text-center">Sales Summary Report</h3>

    <!-- Filter options -->
    <div class="mb-4 text-center">
        <a href="?period=today" class="btn btn-primary mx-2">Today</a>
        <a href="?period=week" class="btn btn-info mx-2">This Week</a>
        <a href="?period=month" class="btn btn-success mx-2">This Month</a>
    </div>

    <!-- Sales Data -->
    <div class="alert alert-info">
        <h5>Total Sales: ₨ <?= number_format($total_sales, 2) ?></h5>
        <h5>Number of Transactions: <?= $transactions_count ?></h5>
    </div>

    <!-- Top Selling Products -->
    <h4>Top Selling Products</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Total Sold</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($top_selling_products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= $product['total_sold'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
