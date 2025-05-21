<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ✅ Place function here
function insertJournalEntry($pdo, $date, $account, $desc, $debit, $credit, $refType, $refId) {
    $stmt = $pdo->prepare("INSERT INTO ledger_entries 
        (date, account, description, debit, credit, reference_type, reference_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $account, $desc, $debit, $credit, $refType, $refId]);
}

$success = '';
$error = '';

// Handle sales return submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_submit'])) {
    $invoice_id = $_POST['invoice_id'];
    $product_ids = $_POST['product_id'];
    $return_qtys = $_POST['return_qty'];
    $reasons = $_POST['reason'];

    foreach ($product_ids as $index => $pid) {
        $qty = (int)$return_qtys[$index];
        $reason = trim($reasons[$index]);

        if ($qty > 0) {
            $pdo->prepare("INSERT INTO sales_returns (sale_id, product_id, return_qty, reason, return_date) VALUES (?, ?, ?, ?, NOW())")
                ->execute([$invoice_id, $pid, $qty, $reason]);

            $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?")
                ->execute([$qty, $pid]);
        }
    }

    $success = "✅ Sales return recorded and stock updated.";
}

// Fetch invoices
$invoices = $pdo->query("
    SELECT id, CONCAT('#INV', LPAD(id, 5, '0')) AS invoice_no, created_at
    FROM sales ORDER BY id DESC
")->fetchAll();

// Fetch return history
$return_history = $pdo->query("
    SELECT sr.*, p.name AS product_name, s.id AS invoice_id 
    FROM sales_returns sr
    JOIN products p ON sr.product_id = p.id
    JOIN sales s ON sr.sale_id = s.id
    ORDER BY sr.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sales Return</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-control, .form-select { border-radius: 6px; }
        .suggestion-box { max-height: 200px; overflow-y: auto; }
    </style>
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 shadow-sm rounded">
    <h4 class="mb-4">🔁 Sales Return</h4>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <form method="POST">
        <!-- Manual Search Box -->
       <label class="form-label">Search Invoice</label>
<input type="text" class="form-control mb-2" id="invoiceSearch" onkeyup="filterInvoices()" placeholder="Type invoice number...">

<small class="text-muted">OR</small>

<select name="invoice_id" class="form-select" required id="invoiceDropdown" onchange="fetchSaleItems(this.value)">
    <option value="">-- Select Invoice --</option>
    <?php foreach ($invoices as $inv): ?>
        <option value="<?= $inv['id'] ?>" data-text="<?= strtolower($inv['invoice_no']) ?>">
            <?= $inv['invoice_no'] ?> (<?= date('d-M-Y', strtotime($inv['created_at'])) ?>)
        </option>
    <?php endforeach; ?>
</select>


        <!-- Items will be loaded here -->
        <div id="sale_items" class="mt-4"></div>

        <button type="submit" name="return_submit" class="btn btn-danger mt-3">Process Return</button>
        <a href="dashboard.php" class="btn btn-secondary mt-3">← Back</a>
    </form>
</div>

<!-- Return History -->
<div class="container mt-5 bg-white p-4 shadow-sm rounded">
    <h5>📦 Return History</h5>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th><th>Invoice</th><th>Product</th><th>Returned Qty</th><th>Reason</th><th>Return Date</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($return_history as $i => $row): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td>#INV<?= str_pad($row['invoice_id'], 5, '0', STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= $row['return_qty'] ?></td>
                <td><?= htmlspecialchars($row['reason']) ?></td>
                <td><?= $row['return_date'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function fetchSaleItems(invoiceId) {
    if (!invoiceId) return;
    fetch('fetch_sale_items.php?sale_id=' + invoiceId)
        .then(res => res.text())
        .then(html => document.getElementById('sale_items').innerHTML = html);
}

function filterInvoices() {
    const search = document.getElementById('invoiceSearch').value.toLowerCase();
    const dropdown = document.getElementById('invoiceDropdown');
    const options = dropdown.options;
    let matchedValue = "";

    for (let i = 0; i < options.length; i++) {
        const text = options[i].textContent.toLowerCase();
        const match = text.includes(search);
        options[i].style.display = i === 0 || match ? 'block' : 'none';

        if (match && i > 0 && matchedValue === "") {
            matchedValue = options[i].value; // get first match
        }
    }

    // Auto-select first matched value
    if (matchedValue) {
        dropdown.value = matchedValue;
        fetchSaleItems(matchedValue); // load sale items immediately
    } else {
        dropdown.value = "";
        document.getElementById('sale_items').innerHTML = ""; // clear item table
    }
}
</script>

</body>
</html>
