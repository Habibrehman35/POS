<?php
require '../config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ✅ Correct table
$entries = $pdo->query("
    SELECT g.*, 
           p.name AS product_name, 
           s.name AS supplier_name 
    FROM general_journal g
    LEFT JOIN products p ON g.reference_id = p.id AND g.reference_type = 'Supplier Payment'
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    ORDER BY g.id DESC
")->fetchAll();


?>
<!DOCTYPE html>
<html>
<head>
    <title>Journal Entries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container bg-white shadow-sm p-4 rounded">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="dashboard.php" class="btn btn-outline-secondary">← Back</a>
        <h3>📘 General Journal Entries</h3>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Date</th><th>Account</th><th>Description</th>
                <th>Debit</th><th>Credit</th><th>Reference</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($entries as $e): ?>
            <tr>
                <td><?= $e['entry_date'] ?></td>
                <td><?= htmlspecialchars($e['account']) ?></td>
           <td>
    <?php if ($e['reference_type'] === 'Supplier Payment' && !empty($e['supplier_name'])): ?>
        Supplier Payment to <?= htmlspecialchars($e['supplier_name']) ?>
    <?php else: ?>
        <?= htmlspecialchars($e['description']) ?>
    <?php endif; ?>
</td>

                <td class="text-success"><?= $e['debit'] > 0 ? number_format($e['debit'], 2) : '-' ?></td>
                <td class="text-danger"><?= $e['credit'] > 0 ? number_format($e['credit'], 2) : '-' ?></td>
                <td><?= $e['reference_type'] . '#' . $e['reference_id'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
