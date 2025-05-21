<?php
require '../config.php';
session_start();

// ✅ Journal Entry Function
function insertJournalEntry($pdo, $date, $account, $desc, $debit, $credit, $refType, $refId) {
    $stmt = $pdo->prepare("INSERT INTO general_journal 
        (entry_date, account, description, debit, credit, reference_type, reference_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $account, $desc, $debit, $credit, $refType, $refId]);
}


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

    // Store product IDs for barcode printing
    $printProductIds = [];

    foreach ($_POST['name'] as $i => $name) {
        if (empty($name)) continue;

        $barcode = $_POST['barcode'][$i];
        $price = $_POST['price'][$i];
        $qty = $_POST['quantity'][$i];
        $cost_price = $_POST['cost_price'][$i] ?? 0;
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

        // Insert product
        $stmt = $pdo->prepare("INSERT INTO products (
            name, barcode, price, cost_price, quantity, payment_due_quantity,
            discount_percent, tax_percent, expiry_date, image,
            supplier_id, supplier_name, supplier_address, supplier_contact, is_paid, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");

        $stmt->execute([
            $name, $barcode, $price, $cost_price, $qty, $qty,
            $discount, $tax, $expiry, $image,
            $supplier_id, $supplierName, $address, $contact
        ]);

        $lastProductId = $pdo->lastInsertId();

        // ✅ Insert automatic general journal entry
        $totalAmount = $cost_price * $qty;
        insertJournalEntry($pdo, date('Y-m-d'), 'Inventory', "Purchase of $name", $totalAmount, 0, 'Purchase', $lastProductId);
        insertJournalEntry($pdo, date('Y-m-d'), 'Accounts Payable', "Payable to $supplierName", 0, $totalAmount, 'Purchase', $lastProductId);

        // Print barcode condition
        if (isset($_POST['print_barcode'][$i]) && $_POST['print_barcode'][$i] == '1') {
            $printProductIds[] = $lastProductId;
        }
    }

    // Output success message
    echo "<div style='background: #d4edda; padding:10px;'>✅ Products Added Successfully for Purchase ID <strong>$purchaseid</strong></div>";
    echo "<a href='add_product.php'>← Back</a> | <a href='supplier_payments.php'>💵 View Supplier Payments</a>";

    // Open barcode PDF in new tabs
    foreach ($printProductIds as $pid) {
        echo "<script>window.open('generate_barcode.php?id={$pid}', '_blank');</script>";
    }
}
?>
