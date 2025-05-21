<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ledger entries
$ledgerEntries = $pdo->query("
    SELECT account, SUM(debit) AS debit, SUM(credit) AS credit
    FROM ledger_entries
    GROUP BY account
")->fetchAll(PDO::FETCH_ASSOC);

// Miscellaneous expenses grouped by category
$miscExpenses = $pdo->query("
    SELECT category AS account, SUM(amount) AS debit
    FROM miscellaneous_expenses
    GROUP BY category
")->fetchAll(PDO::FETCH_ASSOC);

// Merge both into a combined associative array by account
$balances = [];

foreach ($ledgerEntries as $entry) {
    $account = $entry['account'];
    $balances[$account] = [
        'debit' => $entry['debit'] ?? 0,
        'credit' => $entry['credit'] ?? 0
    ];
}

foreach ($miscExpenses as $exp) {
    $account = $exp['account'];
    if (!isset($balances[$account])) {
        $balances[$account] = ['debit' => 0, 'credit' => 0];
    }
    $balances[$account]['debit'] += $exp['debit'];
}

// Calculate totals
$total_debit = 0;
$total_credit = 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trial Balance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container bg-white shadow-sm p-4 rounded">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="dashboard.php" class="btn btn-outline-secondary">← Back</a>
        <h3 class="mb-0">📊 Trial Balance</h3>
    </div>

    <table class="table table-bordered table-striped text-center">
        <thead class="table-dark">
            <tr>
                <th>Account</th>
                <th>Debit (₨)</th>
                <th>Credit (₨)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($balances as $account => $amounts): 
            $debit = round($amounts['debit'], 2);
            $credit = round($amounts['credit'], 2);
            $total_debit += $debit;
            $total_credit += $credit;
        ?>
            <tr>
                <td><?= htmlspecialchars($account) ?></td>
                <td><?= $debit > 0 ? number_format($debit, 2) : '-' ?></td>
                <td><?= $credit > 0 ? number_format($credit, 2) : '-' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light fw-bold">
            <tr>
                <td class="text-end">Grand Total</td>
                <td>₨ <?= number_format($total_debit, 2) ?></td>
                <td>₨ <?= number_format($total_credit, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <?php if ($total_debit === $total_credit): ?>
        <div class="alert alert-success text-center fw-semibold">✅ Trial Balance is Balanced.</div>
    <?php else: ?>
        <div class="alert alert-danger text-center fw-semibold">❌ Trial Balance is NOT Balanced.</div>
    <?php endif; ?>
</div>
</body>
</html>
