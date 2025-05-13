<?php
require '../config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("❌ Invalid payment ID.");
}

$stmt = $pdo->prepare("SELECT p.*, s.name, s.address, s.phone 
                       FROM supplier_payments p 
                       JOIN suppliers s ON p.supplier_id = s.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$payment = $stmt->fetch();

if (!$payment) {
    die("❌ Payment record not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Supplier Payment Invoice</title>
    <style>
        body {
            font-family: Arial;
            padding: 30px;
            background: #fff;
        }
        .invoice-box {
            max-width: 700px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 30px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 15px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-box">
        <div class="header">
            <h2>Supplier Payment Invoice</h2>
        </div>

        <div class="section">
            <div><span class="label">Supplier:</span> <?= htmlspecialchars($payment['name']) ?></div>
            <div><span class="label">Phone:</span> <?= htmlspecialchars($payment['phone']) ?></div>
            <div><span class="label">Address:</span> <?= htmlspecialchars($payment['address']) ?></div>
        </div>

        <div class="section">
            <div><span class="label">Invoice No:</span> <?= htmlspecialchars($payment['invoice_number']) ?></div>
            <div><span class="label">Date:</span> <?= date('d-M-Y H:i', strtotime($payment['payment_date'])) ?></div>
            <div><span class="label">Amount Paid:</span> ₨ <?= number_format($payment['amount'], 2) ?></div>
            <div><span class="label">Payment Mode:</span> <?= htmlspecialchars($payment['payment_mode']) ?></div>
        </div>

        <div class="section">
            <div><span class="label">Remarks:</span><br> <?= nl2br(htmlspecialchars($payment['remarks'])) ?></div>
        </div>
    </div>
</body>
</html>
