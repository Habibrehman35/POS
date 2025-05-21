<?php
require '../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit;
}

// 🟢 Inventory as Asset (current stock * cost_price)
$inventory_value = $pdo->query("
    SELECT SUM(quantity * cost_price) AS total 
    FROM products
")->fetchColumn() ?: 0;

// 🟢 Miscellaneous cash expenses as used assets
$misc_expenses = $pdo->query("
    SELECT SUM(amount) AS total FROM miscellaneous_expenses
")->fetchColumn() ?: 0;

// 🔴 Supplier Payables (unpaid product costs)
$payables = $pdo->query("
    SELECT SUM(cost_price * quantity) AS total 
    FROM products 
    WHERE is_paid = 0
")->fetchColumn() ?: 0;

// 🟡 Sales revenue
$sales = $pdo->query("SELECT SUM(total_amount) FROM sales")->fetchColumn() ?: 0;

// 🔴 COGS from sale_items (cost * quantity)
$cogs = $pdo->query("
    SELECT SUM(si.quantity * p.cost_price) AS total
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
")->fetchColumn() ?: 0;

// 🟡 Equity: Sales - COGS - Expenses
$retained_earnings = $sales - $cogs - $misc_expenses;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Balance Sheet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container bg-white p-4 shadow-sm rounded">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="dashboard.php" class="btn btn-outline-secondary">← Back</a>
        <h3 class="mb-0">📊 Balance Sheet</h3>
    </div>

    <div class="row">
        <!-- Assets -->
        <div class="col-md-6">
            <h5 class="text-success">Assets</h5>
            <table class="table table-bordered">
                <tr><td>📦 Inventory</td><td>₨ <?= number_format($inventory_value, 2) ?></td></tr>
                <tr><td>🧾 Expenses (Stationery, Fuel, etc.)</td><td>₨ <?= number_format($misc_expenses, 2) ?></td></tr>
                <tr class="table-light fw-bold"><td>Total Assets</td><td>₨ <?= number_format($inventory_value + $misc_expenses, 2) ?></td></tr>
            </table>
        </div>

        <!-- Liabilities & Equity -->
        <div class="col-md-6">
            <h5 class="text-danger">Liabilities</h5>
            <table class="table table-bordered">
                <tr><td>📤 Accounts Payable (Suppliers)</td><td>₨ <?= number_format($payables, 2) ?></td></tr>
                <tr class="table-light fw-bold"><td>Total Liabilities</td><td>₨ <?= number_format($payables, 2) ?></td></tr>
            </table>

            <h5 class="text-primary mt-4">Equity</h5>
            <table class="table table-bordered">
                <tr><td>💼 Retained Earnings</td><td>₨ <?= number_format($retained_earnings, 2) ?></td></tr>
                <tr class="table-light fw-bold"><td>Total Equity</td><td>₨ <?= number_format($retained_earnings, 2) ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Validation -->
    <div class="alert alert-info text-center fw-semibold">
        Total Assets = <?= number_format($inventory_value + $misc_expenses, 2) ?> |
        Liabilities + Equity = <?= number_format($payables + $retained_earnings, 2) ?><br>
        <?= ($inventory_value + $misc_expenses == $payables + $retained_earnings) ? '✅ Balanced' : '❌ Not Balanced' ?>
    </div>
</div>
</body>
</html>