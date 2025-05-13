<?php
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplierName = $_POST['supplier'] ?? '';
    $address = $_POST['address'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $mode = $_POST['mode'] ?? '';
    $description = $_POST['description'] ?? '';
    $subtotal = $_POST['subtotal'] ?? '';
    $purchaseid = $_POST['purchaseid'] ?? '';
    $date = date("Y-m-d H:i:s");

    // Find supplier_id from name
    $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE name = ?");
    $stmt->execute([$supplierName]);
    $supplier_id = $stmt->fetchColumn();

    if (!$supplier_id) {
        die("❌ Supplier not found.");
    }

    // Loop through product data
    foreach ($_POST['name'] as $i => $name) {
        if (empty($name)) continue;

        $barcode = $_POST['barcode'][$i];
        $price = $_POST['price'][$i];
        $qty = $_POST['quantity'][$i];
        $discount = $_POST['discount'][$i];
        $tax = $_POST['tax'][$i];
        $expiry = $_POST['expiry'][$i];
        $image = '';

        // Upload image
        if (!empty($_FILES['image']['name'][$i])) {
            $filename = time() . '_' . basename($_FILES['image']['name'][$i]);
            $target = '../uploads/' . $filename;
            if (!file_exists('../uploads')) {
                mkdir('../uploads', 0755, true);
            }
            if (move_uploaded_file($_FILES['image']['tmp_name'][$i], $target)) {
                $image = $filename;
            }
        }

        // Insert into DB
        $stmt = $pdo->prepare("INSERT INTO products (
            name, barcode, price, quantity, discount_percent, tax_percent, expiry_date, image,
            supplier_id, supplier_name, supplier_address, supplier_contact, is_paid, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");

        $stmt->execute([
            $name, $barcode, $price, $qty, $discount, $tax, $expiry, $image,
            $supplier_id, $supplierName, $address, $contact
        ]);
    }

    echo "<div style='background: #d4edda; padding:10px;'>✅ Products Added Successfully for Purchase ID <strong>$purchaseid</strong></div>";
    echo "<a href='add_product.php'>← Back</a> | <a href='supplier_payments.php'>💵 View Supplier Payments</a>";
}
?>
