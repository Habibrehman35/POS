<?php
require '../config.php';
session_start();

$success = '';
// ✅ Place function here
function insertJournalEntry($pdo, $date, $account, $desc, $debit, $credit, $refType, $refId) {
    $stmt = $pdo->prepare("INSERT INTO general_journal 
        (entry_date, account, description, debit, credit, reference_type, reference_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $account, $desc, $debit, $credit, $refType, $refId]);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['expense_date'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    // Insert into expenses
    $stmt = $pdo->prepare("INSERT INTO miscellaneous_expenses (expense_date, category, amount, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$date, $category, $amount, $description]);
    $expense_id = $pdo->lastInsertId(); // used for journal reference


    // Journal Entry (Auto)
insertJournalEntry($pdo, $date, $category, $description ?: "Expense - $category", $amount, 0, "Misc Expense", $expense_id);
insertJournalEntry($pdo, $date, "Cash", $description ?: "Expense - $category", 0, $amount, "Misc Expense", $expense_id);


    // Auto journal entries
    $entry_desc = "Expense - $category";

    // 🟢 Debit the expense account (e.g., Fuel, Stationery)
    insertJournalEntry($pdo, $date, $category, $entry_desc, $amount, 0, 'Misc Expense', $expense_id);

    // 🔴 Credit Cash or Bank
    insertJournalEntry($pdo, $date, 'Cash', $entry_desc, 0, $amount, 'Misc Expense', $expense_id);

    $success = "✅ Expense added and journal entry recorded.";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Expense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="dashboard.php" class="btn btn-outline-secondary">← Back</a>
        <h3>Add Miscellaneous Expense</h3>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <form method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="expense_date" class="form-control" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="mb-3">
    <label class="form-label">Category</label>
    <select name="category" class="form-select" required>
        <option value="">-- Select Category --</option>
        <option value="Fuel">Fuel</option>
        <option value="Stationery">Stationery</option>
        <option value="Snacks">Snacks</option>
        <option value="Repair">Repair</option>
        <option value="Maintenance">Maintenance</option>
        <option value="Other">Other</option>
    </select>
</div>

        <div class="mb-3">
            <label class="form-label">Amount (₨)</label>
            <input type="number" name="amount" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description (optional)</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">➕ Add Expense</button>
    </form>
</div>
</body>
</html>
