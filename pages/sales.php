<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'cashier'])) {

    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle barcode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barcode'])) {
    $barcode = $_POST['barcode'];
    $qty = $_POST['qty'] ?? 1;

    $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ?");
    $stmt->execute([$barcode]);
    $product = $stmt->fetch();

    if ($product) {
        $id = $product['id'];
        $stockQty = $product['quantity'];

        if ($qty > $stockQty) {
            $error = "Not enough stock available. Only $stockQty item(s) left.";
            header("Location: sales.php?error=" . urlencode($error));
            exit;
        }

        $_SESSION['cart'][$id]['id'] = $id;
        $_SESSION['cart'][$id]['name'] = $product['name'];
        $_SESSION['cart'][$id]['price'] = $product['price'];
        $_SESSION['cart'][$id]['quantity'] = ($_SESSION['cart'][$id]['quantity'] ?? 0) + $qty;
    }
}

if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: sales.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>New Sale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Arial', sans-serif;
            padding: 20px;
        }

        .container {
            max-width: 960px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        h3 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .table {
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .table th, .table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background-color: #007bff;
            color: white;
        }

        .table .btn-danger {
            background-color: #dc3545;
            font-size: 14px;
            border-radius: 5px;
        }

        .table .btn-danger:hover {
            background-color: #c82333;
        }

        .low-stock-message {
            background-color: #fff3cd;
            color: #856404;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 14px;
        }

        .suggestion-box {
            position: absolute;
            background: white;
            border: 1px solid #ccc;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1000;
            width: 100%;
            margin-top: 5px;
        }

        .suggestion-item {
            padding: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .suggestion-item img {
            width: 30px;
            height: 30px;
            margin-right: 10px;
        }

        .suggestion-item:hover {
            background-color: #f1f1f1;
        }

        .alert-danger {
            font-weight: 500;
            border-radius: 8px;
            padding: 12px 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h3 class="text-center fw-bold mb-4 text-primary">🛒 New Sale</h3>

    <div class="position-relative mb-4 shadow-sm">
        <input type="text" id="search_product" onkeyup="liveSearch()" 
               class="form-control form-control-lg" 
               placeholder="🔍 Search product by name..." autocomplete="off">
        <div id="suggestions" class="suggestion-box"></div>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['cart'])): ?>
    <table class="table">
        <thead class="table-primary">
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Tax</th>
                <th>Total (With Tax)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $grand = 0;

        foreach ($_SESSION['cart'] as $item): 
            $stmt = $pdo->prepare("SELECT tax_percent, discount_percent, quantity FROM products WHERE id = ?");
            $stmt->execute([$item['id']]);
            $product = $stmt->fetch();

            $discount = $product['discount_percent'] ?? 0;
            $tax = $product['tax_percent'] ?? 0;
            $stockQty = $product['quantity'] ?? 0;

            $price = $item['price'];
            $qty = $item['quantity'];

            $line_total = $price * $qty;
            $discount_amt = $line_total * $discount / 100;
            $final_total = $line_total - $discount_amt;
            $tax_amt = $final_total * $tax / 100;
            $net_total = $final_total + $tax_amt;

            $grand += $net_total;
            $stock_warning = $stockQty < 5 ? '<span class="badge bg-warning text-dark ms-2">⚠ Low Stock</span>' : '';
        ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?> <?= $stock_warning ?></td>
            <td><?= $qty ?></td>
            <td>₨ <?= number_format($price, 2) ?></td>
            <td><?= $discount > 0 ? "$discount% (-₨" . number_format($discount_amt, 2) . ")" : '-' ?></td>
            <td><?= $tax > 0 ? "$tax% (+₨" . number_format($tax_amt, 2) . ")" : '-' ?></td>
            <td>₨ <?= number_format($net_total, 2) ?></td>
            <td><a href="?remove=<?= $item['id'] ?>" class="btn btn-sm btn-danger">X</a></td>
        </tr>
        <?php endforeach; ?>

        <tr>
            <th colspan="5" class="text-end">Grand Total</th>
            <th colspan="2">₨ <?= number_format($grand, 2) ?></th>
        </tr>
        </tbody>
    </table>

    <form method="POST" action="checkout.php">
        <div class="mb-4">
            <label class="form-label fw-semibold">Payment Mode</label>
            <select name="payment_mode" class="form-select form-select-lg" required>
                <option value="cash">💵 Cash</option>
                <option value="card">💳 Card</option>
                <option value="qr">📱 QR</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Checkout</button>
    </form>
    <?php else: ?>
        <p>No items in cart.</p>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-outline-secondary mt-4">← Back</a>
</div>

<script>
function liveSearch() {
    const query = document.getElementById('search_product').value;
    const resultBox = document.getElementById('suggestions');

    if (query.length < 1) {
        resultBox.innerHTML = '';
        return;
    }

    fetch('./search_product.php?term=' + encodeURIComponent(query))
        .then(res => res.text())
        .then(data => {
            resultBox.innerHTML = data;
        });
}

function addToCart(barcodeField, qtyField) {
    const barcodeInput = document.getElementById(barcodeField);
    const qtyInput = document.getElementById(qtyField);

    if (!barcodeInput || !qtyInput) {
        alert("Fields not found.");
        return;
    }

    const barcode = barcodeInput.value;
    const quantity = parseInt(qtyInput.value);
    const stock = parseInt(qtyInput.getAttribute("data-stock"));

    if (!barcode || isNaN(quantity) || quantity <= 0) {
        alert("Enter a valid quantity.");
        return;
    }

    if (quantity > stock) {
        const errorBox = document.getElementById("errorBox");
        if (errorBox) {
            errorBox.textContent = `Only ${stock} item(s) left in stock.`;
            errorBox.classList.remove("d-none");
            setTimeout(() => {
                errorBox.classList.add("d-none");
                errorBox.textContent = '';
            }, 4000);
        } else {
            alert(`Only ${stock} item(s) left in stock.`);
        }
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'sales.php';

    const b = document.createElement('input');
    b.name = 'barcode';
    b.value = barcode;
    b.type = 'hidden';

    const q = document.createElement('input');
    q.name = 'qty';
    q.value = quantity;
    q.type = 'hidden';

    form.appendChild(b);
    form.appendChild(q);
    document.body.appendChild(form);
    form.submit();
}
</script>
</body>
</html>
