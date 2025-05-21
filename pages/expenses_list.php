<?php
require '../config.php';
session_start();

$expenses = $pdo->query("SELECT * FROM miscellaneous_expenses ORDER BY expense_date DESC")->fetchAll();
$total = $pdo->query("SELECT SUM(amount) FROM miscellaneous_expenses")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Expenses List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="dashboard.php" class="btn btn-outline-secondary">← Back</a>
        <h3>📋 Expense History</h3>
    </div>

    <div class="mb-3">
        <strong>Total Expenses: ₨ <?= number_format($total, 2) ?></strong>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Date</th><th>Category</th><th>Amount</th><th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenses as $e): ?>
            <tr>
                <td><?= $e['expense_date'] ?></td>
                <td><?= htmlspecialchars($e['category']) ?></td>
                <td>₨ <?= number_format($e['amount'], 2) ?></td>
                <td><?= htmlspecialchars($e['description']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
