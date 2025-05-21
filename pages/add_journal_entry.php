<?php
require '../config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); exit;
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['entry_date'];
    $account = $_POST['account'];
    $description = $_POST['description'];
    $debit = $_POST['debit'] ?: 0;
    $credit = $_POST['credit'] ?: 0;
    $ref_type = $_POST['reference_type'];
    $ref_id = $_POST['reference_id'];

    $stmt = $pdo->prepare("INSERT INTO general_journal 
        (entry_date, account, description, debit, credit, reference_type, reference_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $account, $description, $debit, $credit, $ref_type, $ref_id]);

    $success = "✅ Journal entry added successfully.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Journal Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container bg-white shadow-sm p-4 rounded">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="dashboard.php" class="btn btn-outline-secondary">← Back</a>
        <h3>Add General Journal Entry</h3>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <form method="POST" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">📅 Entry Date</label>
            <input type="date" name="entry_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">📘 Account</label>
            <input type="text" name="account" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">🧾 Description</label>
            <input type="text" name="description" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label text-success">💸 Debit</label>
            <input type="number" name="debit" step="0.01" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label text-danger">💰 Credit</label>
            <input type="number" name="credit" step="0.01" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Ref Type (optional)</label>
            <input type="text" name="reference_type" class="form-control" placeholder="e.g. PO, INV">
        </div>
        <div class="col-md-3">
            <label class="form-label">Ref ID (optional)</label>
            <input type="number" name="reference_id" class="form-control" placeholder="e.g. 101">
        </div>
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">➕ Add Entry</button>
        </div>
    </form>
</div>
</body>
</html>
