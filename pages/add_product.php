<?php
require '../config.php';

// Fetch suppliers
$suppliers = $pdo->query("SELECT * FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);

// Generate Purchase ID (Example: PR001, PR002...)
$stmt = $pdo->query("SELECT MAX(id) FROM products");
$lastId = (int) $stmt->fetchColumn();
$new_id = "PR" . str_pad($lastId + 1, 3, "0", STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h4 class="mb-4">➕ Add Stock/Product</h4>

<form method="POST" action="insert_purchase.php" enctype="multipart/form-data">
    <input type="hidden" name="purchaseid" value="<?= htmlspecialchars($new_id) ?>">

    <div class="row mb-3">
        <div class="col-md-2"><label>Purchase ID</label></div>
        <div class="col-md-3"><input class="form-control" readonly value="<?= $new_id ?>"></div>
        <div class="col-md-2"><label>Date</label></div>
        <div class="col-md-3"><input class="form-control" readonly value="<?= date('Y-m-d H:i:s') ?>"></div>
    </div>

    <div class="row mb-3">
        <div class="col-md-2"><label><strong>* Supplier</strong></label></div>
        <div class="col-md-3">
            <select class="form-control" id="supplier_dropdown" name="supplier" required>
                <option value="">-- Select Supplier --</option>
                <?php foreach ($suppliers as $sup): ?>
                    <option value="<?= htmlspecialchars($sup['name']) ?>"
                            data-address="<?= htmlspecialchars($sup['address']) ?>"
                            data-contact="<?= htmlspecialchars($sup['phone']) ?>">
                        <?= htmlspecialchars($sup['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><label>Address</label></div>
        <div class="col-md-3"><input class="form-control" name="address" id="supplier_address" readonly></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-2"><label>Contact</label></div>
        <div class="col-md-3"><input class="form-control" name="contact" id="supplier_contact" readonly></div>
    </div>

    <table class="table table-bordered text-center" id="product_table">
        <thead class="table-dark">
        <tr>
            <th>Image</th><th>Name</th><th>Barcode</th><th>Price</th> <th>Cost Price</th>
            <th>Qty</th><th>Discount</th><th>Tax</th><th>Expiry</th><th>P.Code</th><th>Remove</th>
        </tr>
        </thead>
        <tbody>
    <tr>
    <td><input type="file" name="image[]" class="form-control" accept="image/*" required></td>
    <td><input type="text" name="name[]" class="form-control" required></td>
    <td><input type="text" name="barcode[]" class="form-control" required></td>
    <td><input type="number" name="price[]" class="form-control" step="0.01" required></td>
    <td><input type="number" name="cost_price[]" class="form-control" step="0.01" required></td>

    <td><input type="number" name="quantity[]" class="form-control" required></td>
    <td><input type="number" name="discount[]" class="form-control" step="0.01" value="0"></td>
    <td><input type="number" name="tax[]" class="form-control" step="0.01" value="0"></td>
    <td><input type="date" name="expiry[]" class="form-control"></td>

    <td class="text-center"><input type="checkbox" class="form-check-input" onclick="handleBarcodePreview(this)">
</td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">×</button></td>
</tr>

        </tbody>
    </table>
    <button type="button" class="btn btn-outline-primary mb-3" onclick="addRow()">➕ Add Row</button>

    <div class="row mb-3">
        <div class="col-md-2">Mode</div>
        <div class="col-md-2">
            <select name="mode" class="form-control">
                <option value="Cash">Cash</option>
                <option value="Cheque">Cheque</option>
                <option value="Credit">Credit</option>
            </select>
        </div>
        <div class="col-md-2">Description</div>
        <div class="col-md-3"><textarea name="description" class="form-control"></textarea></div>
        <div class="col-md-2">Grand Total</div>
        <div class="col-md-1"><input type="text" name="subtotal" id="grand_total" class="form-control" readonly></div>
    </div>

    <div class="d-flex gap-2 mt-2">
        <button type="submit" class="btn btn-success">ADD ➕</button>
        <button type="reset" class="btn btn-secondary">RESET</button>
        <a href="products.php" class="btn btn-outline-dark">← Back</a>
    </div>
</form>

<script>
function addRow() {
    const row = `<tr>
        <td><input type="file" name="image[]" class="form-control" accept="image/*" required></td>
        <td><input type="text" name="name[]" class="form-control" required></td>
        <td><input type="text" name="barcode[]" class="form-control" required></td>
        <td><input type="number" name="price[]" class="form-control" step="0.01" required></td>
        <td><input type="number" name="cost_price[]" class="form-control" step="0.01" required></td>
        <td><input type="number" name="quantity[]" class="form-control" required></td>
        <td><input type="number" name="discount[]" class="form-control" step="0.01" value="0"></td>
        <td><input type="number" name="tax[]" class="form-control" step="0.01" value="0"></td>
        <td><input type="date" name="expiry[]" class="form-control"></td>
        <td class="text-center"><input type="checkbox" class="form-check-input" onclick="handleBarcodePreview(this)"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">×</button></td>
    </tr>`;
    document.querySelector("#product_table tbody").insertAdjacentHTML('beforeend', row);
}


function removeRow(btn) {
    btn.closest('tr').remove();
}

document.getElementById('supplier_dropdown').addEventListener('change', function () {
    let selected = this.options[this.selectedIndex];
    document.getElementById('supplier_address').value = selected.getAttribute('data-address');
    document.getElementById('supplier_contact').value = selected.getAttribute('data-contact');
});


function handleBarcodePreview(checkbox) {
    if (!checkbox.checked) return;

    const row = checkbox.closest('tr');
    const barcode = row.querySelector('input[name="barcode[]"]').value;
    const quantity = row.querySelector('input[name="quantity[]"]').value;
    const name = row.querySelector('input[name="name[]"]').value;

    if (!barcode || !quantity || quantity < 1 || !name) {
        alert("Please enter valid Barcode, Quantity, and Name first.");
        checkbox.checked = false;
        return;
    }

    const url = `/pos-system/pages/barcode_viewer.php?barcode=${encodeURIComponent(barcode)}&qty=${encodeURIComponent(quantity)}&name=${encodeURIComponent(name)}`;
    window.open(url, '_blank', 'width=600,height=600');
}
</script>

<script>
document.getElementById("productForm").addEventListener("submit", function(e) {
    const rows = document.querySelectorAll("#product_table tbody tr");
    let hasError = false;

    rows.forEach((row, index) => {
        const price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
        const cost = parseFloat(row.querySelector('input[name="cost_price[]"]').value) || 0;

        if (cost > price) {
            hasError = true;
            alert(`❌ Row ${index + 1}: Cost Price ($${cost}) is greater than Sale Price ($${price})`);
        }
    });

    if (hasError) {
        e.preventDefault(); // Stop form submission
    }
});
</script>



</body>
</html>
