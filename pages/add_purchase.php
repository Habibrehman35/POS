<?php
include_once("init.php");

// Generate new Purchase ID
$last_id = $db->maxOfAll("stock_id", "stock_entries");
$num = (int) filter_var($last_id, FILTER_SANITIZE_NUMBER_INT) + 1;
$new_id = "PR" . str_pad($num, 3, "0", STR_PAD_LEFT);

// Fetch suppliers
$suppliers = $db->query("SELECT * FROM supplier_details");
$supplier_list = [];
while ($s = mysqli_fetch_assoc($suppliers)) {
    $supplier_list[] = $s;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $supplier = $_POST['supplier'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $mode = $_POST['mode'];
    $description = $_POST['description'];
    $subtotal = $_POST['subtotal'];
    $purchaseid = $_POST['purchaseid'];
    $date = date("Y-m-d H:i:s");

    foreach ($_POST['item'] as $index => $item_name) {
        if (empty($item_name)) continue;
        $qty = $_POST['quantity'][$index];
        $cost = $_POST['cost'][$index];
        $sell = $_POST['sell'][$index];
        $total = $_POST['total'][$index];

        $existing = $db->countOf("stock_avail", "name='$item_name'");
        if ($existing == 0) {
            $db->query("INSERT INTO stock_avail(name, quantity) VALUES ('$item_name', $qty)");
        } else {
            $prevQty = $db->queryUniqueValue("SELECT quantity FROM stock_avail WHERE name='$item_name'");
            $newQty = $prevQty + $qty;
            $db->query("UPDATE stock_avail SET quantity=$newQty WHERE name='$item_name'");
        }

        $db->query("INSERT INTO stock_entries(stock_id, stock_name, stock_supplier_name, quantity, company_price,
        selling_price, opening_stock, closing_stock, date, username, type, total, mode, description, subtotal, count1)
        VALUES ('$purchaseid', '$item_name', '$supplier', $qty, $cost, $sell, 0, $qty, '$date', 'admin', 'entry',
        $total, '$mode', '$description', $subtotal, $index + 1)");
    }

    echo "<div style='padding:10px; background:lightgreen;'>✅ Purchase ID <strong>$purchaseid</strong> added successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Purchase</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .form input { margin: 5px; padding: 5px; }
        .round { border-radius: 5px; }
        .button { padding: 10px; border: none; cursor: pointer; }
        .form-table th, .form-table td { padding: 8px; }
    </style>
</head>
<body>

<form method="POST">
    <input type="hidden" name="purchaseid" value="<?= $new_id ?>">
    <table>
        <tr>
            <td>Purchase ID:</td>
            <td><input type="text" value="<?= $new_id ?>" readonly></td>
            <td>Date:</td>
            <td><input type="text" value="<?= date('Y-m-d H:i:s') ?>" readonly></td>
        </tr>
        <tr>
            <td><span style="color:red">*</span> Supplier:</td>
            <td>
                <select id="supplier_dropdown" name="supplier" required>
                    <option value="">-- Select Supplier --</option>
                    <?php foreach ($supplier_list as $sup): ?>
                        <option value="<?= htmlspecialchars($sup['supplier_name']) ?>"
                                data-address="<?= htmlspecialchars($sup['supplier_address']) ?>"
                                data-contact="<?= htmlspecialchars($sup['supplier_contact1']) ?>">
                            <?= htmlspecialchars($sup['supplier_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>Address:</td>
            <td><input type="text" name="address" id="supplier_address" readonly></td>
            <td>Contact:</td>
            <td><input type="text" name="contact" id="supplier_contact" readonly></td>
        </tr>
    </table>

    <table class="form-table" id="item_table">
        <thead>
        <tr>
            <th>Item</th><th>Quantity</th><th>Cost</th><th>Selling</th><th>Total</th><th>Action</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><input name="item[]" type="text" required></td>
            <td><input name="quantity[]" type="number" required></td>
            <td><input name="cost[]" type="number" step="0.01" required></td>
            <td><input name="sell[]" type="number" step="0.01"></td>
            <td><input name="total[]" class="line_total" readonly></td>
            <td><button type="button" onclick="removeRow(this)">🗑</button></td>
        </tr>
        </tbody>
    </table>
    <button type="button" onclick="addRow()">➕ Add Row</button>

    <table>
        <tr>
            <td>Mode:</td>
            <td>
                <select name="mode">
                    <option value="Cash">Cash</option>
                    <option value="Cheque">Cheque</option>
                    <option value="Credit">Credit</option>
                </select>
            </td>
            <td>Description:</td>
            <td><textarea name="description"></textarea></td>
            <td>Grand Total:</td>
            <td><input type="text" name="subtotal" id="grand_total" readonly></td>
        </tr>
    </table>

    <button type="submit" class="button round blue">ADD ➕</button>
    <button type="reset" class="button round red">RESET</button>
    
</form>

<script>
function addRow() {
    let row = `<tr>
        <td><input name="item[]" type="text" required></td>
        <td><input name="quantity[]" type="number" required></td>
        <td><input name="cost[]" type="number" step="0.01" required></td>
        <td><input name="sell[]" type="number" step="0.01"></td>
        <td><input name="total[]" class="line_total" readonly></td>
        <td><button type="button" onclick="removeRow(this)">🗑</button></td>
    </tr>`;
    $('#item_table tbody').append(row);
}

function removeRow(btn) {
    $(btn).closest('tr').remove();
    updateGrandTotal();
}

$(document).on('input', 'input[name^="quantity"], input[name^="cost"]', function () {
    let row = $(this).closest('tr');
    let qty = parseFloat(row.find('input[name^="quantity"]').val()) || 0;
    let cost = parseFloat(row.find('input[name^="cost"]').val()) || 0;
    let total = qty * cost;
    row.find('input[name^="total"]').val(total.toFixed(2));
    updateGrandTotal();
});

function updateGrandTotal() {
    let grandTotal = 0;
    $('.line_total').each(function () {
        grandTotal += parseFloat($(this).val()) || 0;
    });
    $('#grand_total').val(grandTotal.toFixed(2));
}

document.getElementById('supplier_dropdown').addEventListener('change', function () {
    let selected = this.options[this.selectedIndex];
    document.getElementById('supplier_address').value = selected.getAttribute('data-address') || '';
    document.getElementById('supplier_contact').value = selected.getAttribute('data-contact') || '';
});
</script>

</body>
</html>
