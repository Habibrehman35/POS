<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Init cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle barcode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barcode'])) {
    $barcode = $_POST['barcode'];
    $qty = $_POST['qty'] ?? 1;

    // Fetch product from the database
    $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ?");
    $stmt->execute([$barcode]);
    $product = $stmt->fetch();

    if ($product) {
        $id = $product['id'];
        $stockQty = $product['quantity']; // Available stock

        // Check if the requested quantity is available
        if ($qty > $stockQty) {
            $error = "Not enough stock available. Only $stockQty item(s) left.";
            // Redirect to sales page with error message
            header("Location: sales.php?error=" . urlencode($error));
            exit;
        }

        $_SESSION['cart'][$id]['id'] = $id;
        $_SESSION['cart'][$id]['name'] = $product['name'];
        $_SESSION['cart'][$id]['price'] = $product['price'];
        $_SESSION['cart'][$id]['quantity'] = ($_SESSION['cart'][$id]['quantity'] ?? 0) + $qty;
    }
}


// Handle remove
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
    /* General Body and Page Styles */
body {
    background-color: #f4f7fc;
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 20px;
}

/* Container for centralizing content */
.container {
    max-width: 1200px;
    margin: auto;
}

/* Title styling */
h3 {
    color: #2c3e50;
    font-size: 24px;
    margin-bottom: 20px;
}

/* Input and button styling */
input[type="text"], input[type="number"], select {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    margin-bottom: 20px;
}
.container {
    max-width: 960px;
    background-color: #ffffff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}


/* Button Styling */
button, .btn {
    background-color: #007bff;
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    border: none;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.3s;
}
.alert-danger {
    font-weight: 500;
    border-radius: 8px;
    padding: 12px 20px;
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
}


button:hover, .btn:hover {
    background-color: #0056b3;
}

/* Table Styling */
.table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
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

.table tr:hover {
    background-color: #f1f1f1;
}

.table .btn-danger {
    background-color: #dc3545;
    color: white;
    padding: 5px 10px;
    font-size: 14px;
    border-radius: 5px;
}

.table .btn-danger:hover {
    background-color: #c82333;
}

/* Low stock warning styles */
.low-stock-message {
    background-color: #fff3cd;
    color: #856404;
    padding: 3px 8px;
    border-radius: 5px;
    font-size: 14px;
}

/* Custom styling for quantity input */
input[type="number"] {
    width: 70px;
    text-align: center;
}

/* Fixing layout for the cart and summary section */
.cart-summary {
    background-color: #fff;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.cart-summary .grand-total {
    font-size: 18px;
    font-weight: bold;
    color: #2c3e50;
    text-align: right;
}

/* Search and suggestions box styling */
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
    object-fit: cover;
    margin-right: 10px;
}

.suggestion-item:hover {
    background-color: #f1f1f1;
}


</style>


<script>
        function liveSearch() {
            let query = document.getElementById('search_product').value;
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
    const barcode = document.getElementById(barcodeField).value;
    const quantity = document.getElementById(qtyField).value;
    const stock = document.getElementById(qtyField).getAttribute("data-stock");

    if (!barcode || !quantity || quantity <= 0) return alert("Enter valid quantity");

    // Check if the selected quantity exceeds the available stock
    if (quantity > stock) {
        alert(`Only ${stock} item(s) left in stock.`);
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


</head>
<body>
    <div class="container">
      <h3 class="text-center fw-bold mb-4 text-primary">🛒 New Sale</h3>


        <!-- 🔍 Smart Search Bar with Suggestions -->
     <div class="position-relative mb-4 shadow-sm">
    <input type="text" id="search_product" onkeyup="liveSearch()" 
           class="form-control form-control-lg" 
           placeholder="🔍 Search product by name..." autocomplete="off" 
           style="border-radius: 10px;">
    <div id="suggestions" class="suggestion-box"></div>
</div>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Cart and Product Details -->
        <?php if (!empty($_SESSION['cart'])): ?>
        <table class="table">
           <thead class="table-primary">
    <tr>
        <th>Product</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Discount</th>
        <th>Total</th>
        <th>Action</th>
    </tr>
</thead>

            <tbody>
            <?php 
            $grand = 0;
            foreach ($_SESSION['cart'] as $item): 
                $stmt = $pdo->prepare("SELECT discount_percent, quantity FROM products WHERE id = ?");
                $stmt->execute([$item['id']]);
                $product = $stmt->fetch();

                $discount = $product['discount_percent'] ?? 0;
                $stockQty = $product['quantity'] ?? 0;
                $price = $item['price'];
                $qty = $item['quantity'];
                $line_total = $price * $qty;
                $discount_amt = $line_total * $discount / 100;
                $final_total = $line_total - $discount_amt;
                $grand += $final_total;

               $stock_warning = $stockQty < 5 ? '<span class="badge bg-warning text-dark ms-2">⚠ Low Stock</span>' : '';

            ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?> <?= $stock_warning ?></td>
                    <td><?= $qty ?></td>
                    <td>₨ <?= number_format($price, 2) ?></td>
                    <td><?= $discount > 0 ? "$discount% (-₨" . number_format($discount_amt, 2) . ")" : '-' ?></td>
                    <td>₨ <?= number_format($final_total, 2) ?></td>
                    <td><a href="?remove=<?= $item['id'] ?>" class="btn btn-sm btn-danger">X</a></td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <th colspan="4" class="text-end">Grand Total</th>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function liveSearch() {
            let query = document.getElementById('search_product').value;
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
            const barcode = document.getElementById(barcodeField).value;
            const quantity = document.getElementById(qtyField).value;
            const stock = document.getElementById(qtyField).getAttribute("data-stock");

            if (!barcode || !quantity || quantity <= 0) return alert("Enter valid quantity");

            if (quantity > stock) {
                alert(`Only ${stock} item(s) left in stock.`);
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