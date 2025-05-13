<?php
require '../config.php';
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'];
    $invoice = $_POST['invoice_number'];
    $amount = $_POST['amount'];
    $mode = $_POST['payment_mode'];
    $remarks = $_POST['remarks'];

    $stmt = $pdo->prepare("INSERT INTO supplier_payments (supplier_id, invoice_number, amount, payment_mode, remarks) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$supplier_id, $invoice, $amount, $mode, $remarks]);

    header("Location: supplier_payments.php?success=1");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Supplier Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container bg-white p-4 rounded shadow">
    <h4>➕ Add Supplier Payment</h4>
    <form method="POST">
        <div class="row g-3">
            <div class="col-md-4">
                <label>Supplier</label>
                <select name="supplier_id" class="form-control" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= $sup['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>Invoice No.</label>
                <input type="text" name="invoice_number" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Amount</label>
                <input type="number" name="amount" step="0.01" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Payment Mode</label>
                <select name="payment_mode" class="form-control">
                    <option>Cash</option>
                    <option>Cheque</option>
                    <option>Bank Transfer</option>
                </select>
            </div>
            <div class="col-12">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control"></textarea>
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-success">Save Payment</button>
            <a href="supplier_payments.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>
</body>
</html>
