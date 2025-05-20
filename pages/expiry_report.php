<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require '../config.php';

$today = date('Y-m-d');
$threshold = date('Y-m-d', strtotime('+20 days'));

$products = $pdo->query("SELECT * FROM products WHERE expiry_date IS NOT NULL ORDER BY expiry_date ASC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Expiry Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .expired { background-color: #dc3545 !important; color: #fff; font-weight: bold; }
        .expiring-soon { background-color: #ffc107 !important; color: #212529; font-weight: bold; }
        .valid { background-color: #198754 !important; color: #fff; font-weight: bold; }
        .no-expiry { background-color: #6c757d !important; color: #fff; font-weight: bold; }
        .filter-select { width: 200px; }

        @media print {
            #controls { display: none; }
        }
    </style>
</head>
<body>

<div id="controls" class="d-flex justify-content-between align-items-center mb-4">
    <h3>🧯 Expiry Report</h3>
    <div class="d-flex gap-2">
        <a href="dashboard.php" class="btn btn-secondary">← Back</a>
        <select id="filter" class="form-select filter-select">
            <option value="all">All</option>
            <option value="expired">Expired</option>
            <option value="soon">Expiring Soon</option>
            <option value="valid">Valid</option>
            <option value="none">No Expiry</option>
        </select>
        <button onclick="printReport()" class="btn btn-outline-dark">🖨️ Print</button>
        <button onclick="downloadPDF()" class="btn btn-outline-primary">⬇️ Export PDF</button>
    </div>
</div>


<div id="reportContent">
    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Barcode</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Expiry Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="reportBody">
            <?php foreach ($products as $p): 
                // Determine status and class
                if (!$p['expiry_date']) {
                    $status = 'No Expiry';
                    $class = 'no-expiry';
                } elseif ($p['expiry_date'] < $today) {
                    $status = 'Expired';
                    $class = 'expired';
                } elseif ($p['expiry_date'] <= $threshold) {
                    $status = 'Expiring Soon';
                    $class = 'expiring-soon';
                } else {
                    $status = 'Valid';
                    $class = 'valid';
                }

                // Badge class for status
                $badgeClass = match($class) {
                    'expired' => 'danger',
                    'expiring-soon' => 'warning',
                    'valid' => 'success',
                    'no-expiry' => 'secondary',
                    default => 'secondary'
                };
            ?>
            <tr class="<?= $class ?>">
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['barcode']) ?></td>
                <td>$<?= number_format($p['price'], 2) ?></td>
                <td><?= (int)$p['quantity'] ?></td>
                <td><?= htmlspecialchars($p['expiry_date']) ?></td>
                <td><span class="badge bg-<?= $badgeClass ?>"><?= $status ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Print function
function printReport() {
    window.print();
}

// Export to PDF
function downloadPDF() {
    const element = document.getElementById('reportContent');
    html2pdf().from(element).set({
        margin: 10,
        filename: 'expiry_report.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }).save();
}

// Filter rows by class
document.getElementById('filter').addEventListener('change', function() {
    const rows = document.querySelectorAll('#reportBody tr');
    const filter = this.value;

    rows.forEach(row => {
        row.style.display = 'table-row';

        if (filter === 'expired' && !row.classList.contains('expired')) {
            row.style.display = 'none';
        } else if (filter === 'soon' && !row.classList.contains('expiring-soon')) {
            row.style.display = 'none';
        } else if (filter === 'valid' && !row.classList.contains('valid')) {
            row.style.display = 'none';
        } else if (filter === 'none' && !row.classList.contains('no-expiry')) {
            row.style.display = 'none';
        }
    });
});
</script>

</body>
</html>
