<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require '../config.php';

// Get product ID
if (!isset($_GET['id'])) {
    die("Product ID is required.");
}
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    die("Product not found.");
}

// Fetch suppliers for dropdown
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();

// Update logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $barcode = $_POST['barcode'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $tax = $_POST['tax_percent'];
    $discount = $_POST['discount_percent'];
    $expiry = $_POST['expiry_date'];
    $supplier = $_POST['supplier'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $image = $product['image'];

    // Image re-upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        $image = time() . '_' . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image);
    }

    // Update DB
    $stmt = $pdo->prepare("UPDATE products SET name=?, barcode=?, price=?, quantity=?, tax_percent=?, discount_percent=?, expiry_date=?, image=?, supplier_name=?, supplier_address=?, supplier_contact=? WHERE id=?");
    $stmt->execute([
        $name, $barcode, $price, $quantity, $tax, $discount, $expiry, $image, $supplier, $address, $contact, $id
    ]);

    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
    <h3 class="mb-4"><i class="bi bi-pencil-square"></i> ✏️ Edit Product</h3>
    <form method="POST" enctype="multipart/form-data">

        <div class="row g-3 mb-2">
            <div class="col-md-6">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Barcode</label>
                <input type="text" name="barcode" value="<?= htmlspecialchars($product['barcode']) ?>" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Price</label>
                <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Quantity</label>
                <input type="number" name="quantity" value="<?= $product['quantity'] ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Expiry Date</label>
                <input type="date" name="expiry_date" value="<?= $product['expiry_date'] ?>" class="form-control">
            </div>

            <div class="col-md-4">
                <label>Tax %</label>
                <input type="number" name="tax_percent" step="0.01" value="<?= $product['tax_percent'] ?>" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Discount %</label>
                <input type="number" name="discount_percent" step="0.01" value="<?= $product['discount_percent'] ?>" class="form-control">
            </div>

            <div class="col-md-4">
                <label><strong>Supplier</strong></label>
                <select class="form-control" id="supplier_dropdown" name="supplier" required>
                    <option value="">-- Select Supplier --</option>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?= htmlspecialchars($sup['name']) ?>"
                                data-address="<?= htmlspecialchars($sup['address']) ?>"
                                data-contact="<?= htmlspecialchars($sup['phone']) ?>"
                                <?= ($sup['name'] == $product['supplier_name']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sup['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label>Supplier Address</label>
                <input type="text" name="address" id="supplier_address" class="form-control" value="<?= htmlspecialchars($product['supplier_address']) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label>Supplier Contact</label>
                <input type="text" name="contact" id="supplier_contact" class="form-control" value="<?= htmlspecialchars($product['supplier_contact']) ?>" readonly>
            </div>

            <div class="col-md-6">
                <label>Product Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <?php if ($product['image']): ?>
                    <div class="mt-2">
                        <img src="../uploads/<?= $product['image'] ?>" alt="Current Image" class="img-thumbnail" width="120">
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">💾 Update Product</button>
            <a href="products.php" class="btn btn-secondary">← Cancel</a>
        </div>
    </form>
</div>
