<?php
require '../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Regular ledger entries
$ledgerEntries = $pdo->query("
    SELECT id, date, account, description, debit, credit, reference_type, reference_id, 'ledger' AS source
    FROM ledger_entries
")->fetchAll();

// Miscellaneous expenses as ledger-like entries
$miscExpenses = $pdo->query("
    SELECT 
        id,
        expense_date AS date,
        category AS account,
        description,
        amount AS debit,
        0 AS credit,
        'Misc' AS reference_type,
        id AS reference_id,
        'expense' AS source
    FROM miscellaneous_expenses
")->fetchAll();

// Merge & sort entries by date DESC
$entries = array_merge($ledgerEntries, $miscExpenses);
usort($entries, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ledger</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container bg-white p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
        <h3 class="mb-0">📋 Ledger</h3>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Account</th>
                <th>Description</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Ref</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($entries as $e): ?>
            <tr>
                <td><?= date('d-M-Y', strtotime($e['date'])) ?></td>
                <td><?= htmlspecialchars($e['account']) ?></td>
                <td><?= htmlspecialchars($e['description']) ?></td>
                <td><?= $e['debit'] > 0 ? number_format($e['debit'], 2) : '-' ?></td>
                <td><?= $e['credit'] > 0 ? number_format($e['credit'], 2) : '-' ?></td>
                <td><?= htmlspecialchars($e['reference_type']) . '#' . $e['reference_id'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
