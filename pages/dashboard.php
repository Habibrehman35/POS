<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user['role'] === 'admin') {
    $stmtTotal = $pdo->query("SELECT SUM(total_amount) AS total, COUNT(*) AS count FROM sales");
    $salesData = $stmtTotal->fetch();

    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $total_customers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    $total_suppliers = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
} else {
    $stmtTotal = $pdo->prepare("SELECT SUM(total_amount) AS total, COUNT(*) AS count FROM sales WHERE user_id = ?");
    $stmtTotal->execute([$user_id]);
    $salesData = $stmtTotal->fetch();
}

$total_sales = $pdo->query("
    SELECT 
        IFNULL(SUM(s.total_amount), 0) - 
        IFNULL((SELECT SUM(sr.return_qty * si.unit_price) 
                FROM sales_returns sr 
                JOIN sale_items si ON sr.sale_id = si.sale_id AND sr.product_id = si.product_id), 0)
    FROM sales s
")->fetchColumn();

$total_invoices = $salesData['count'] ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - POS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body.dark-mode {
            background-color: #212529 !important;
            color: #e9ecef;
        }
        body.dark-mode .dashboard-box,
        body.dark-mode .alert,
        body.dark-mode .btn {
            background-color: #343a40;
            color: #f8f9fa;
            border-color: #495057;
        }
        body.dark-mode .sidebar {
            background: linear-gradient(180deg, #1c1f23, #121417);
        }
        .toggle-switch {
            position: absolute;
            top: 20px;
            right: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: white;
        }
       .sidebar {
    min-height: 100vh;
    width: 220px;
    background: #1f2937;
    padding: 20px 15px;
    color: #ffffff;
    font-family: 'Segoe UI', sans-serif;
    position: relative;
}

.sidebar h5 {
    font-size: 16px;
    margin-bottom: 20px;
    color: #9ca3af;
    letter-spacing: 0.5px;
}

.sidebar a {
    color: #e5e7eb;
    text-decoration: none;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    font-size: 15px;
    transition: all 0.3s ease;
    border-radius: 6px;
}

.sidebar a:hover,
.sidebar .dropdown-toggle:hover {
    background-color: #374151;
    color: #ffffff;
    padding-left: 16px;
}

.sidebar .dropdown-toggle::after {
    margin-left: auto;
    font-size: 0.8rem;
}

.sidebar .collapse a {
    padding-left: 25px;
    font-size: 14px;
    color: #d1d5db;
}

.sidebar .collapse a:hover {
    color: #ffffff;
}

.sidebar a.text-danger {
    color: #f87171 !important;
}

.sidebar a.text-danger:hover {
    background-color: #7f1d1d;
}

        .dashboard-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        }
        .dashboard-heading {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        #salesChart {
            height: 200px !important;
        }

        .sidebar .collapse a {
    padding-left: 25px;
    font-size: 14px;
    color: #d1d5db;
    display: block;
}

.sidebar .collapse a:hover {
    color: #ffffff;
    background-color: #374151;
    border-radius: 5px;
}
.form-check-input {
    cursor: pointer;
}

.form-check-label {
    font-size: 13px;
}
.footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    position: relative;
    bottom: 0;
    left: 0;
    width: 100%;
    font-size: 0.85rem;
    color: #6c757d;
}

body.dark-mode .footer {
    background-color: #343a40;
    border-top: 1px solid #495057;
    color: #ced4da;
}

body.dark-mode .footer a {
    color: #adb5bd;
}

    </style>
</head>
<body>
<div class="d-flex">
    <div class="sidebar">
       <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 text-light"><i class="bi bi-list"></i> Navigation</h5>
    <div class="form-check form-switch ms-auto">
        <input class="form-check-input" type="checkbox" id="darkToggle">
        <label class="form-check-label text-light ms-2" for="darkToggle">Dark Mode</label>
    </div>
</div>

      
        <a href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
        <div class="mb-2">
    <a class="text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#salesMenu">
        <span><i class="bi bi-cart4"></i> Sales</span>
        <i class="bi bi-caret-down-fill"></i>
    </a>
    <div class="collapse ps-3 pt-2" id="salesMenu">
        <a href="all_sales.php" class="text-white d-block mb-2"><i class="bi bi-receipt-cutoff"></i> All Sales</a>
        <a href="sales.php" class="text-white d-block mb-2"><i class="bi bi-cart-plus"></i> Add Sale</a>
        <a href="sales_return.php" class="text-white d-block mb-2"><i class="bi bi-arrow-counterclockwise"></i> Sales Return</a>
    </div>
</div>

 <div class="mb-2">
    <a class="text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#productMenu">
        <span><i class="bi bi-box-seam"></i> Products</span>
        <i class="bi bi-caret-down-fill"></i>
    </a>
    <div class="collapse ps-3 pt-2" id="productMenu">
        <a href="products.php" class="text-white d-block mb-2"><i class="bi bi-card-list"></i> List Products</a>
        <a href="add_product.php" class="text-white d-block mb-2"><i class="bi bi-plus-circle"></i> Add Product</a>
        <a href="product_returns.php" class="text-white d-block mb-2"><i class="bi bi-arrow-return-left"></i> Return Defective</a>
        <a href="return_received.php" class="text-white d-block mb-2"><i class="bi bi-box-arrow-in-down-left"></i> Return Received</a>
    </div>
</div>


        <a href="customers.php"><i class="bi bi-people"></i> Customers</a>
        <div class="mb-2">
            <a class="text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#supplierMenu">
                <span><i class="bi bi-truck"></i> Suppliers</span>
                <i class="bi bi-caret-down-fill"></i>
            </a>
            <div class="collapse ps-3 pt-2" id="supplierMenu">
                <a href="suppliers.php" class="text-white d-block mb-2"><i class="bi bi-list-ul"></i> Supplier List</a>
                <a href="add_supplier.php" class="text-white d-block mb-2"><i class="bi bi-plus-circle"></i> Add Supplier</a>
                <a href="supplier_payments.php" class="text-white d-block mb-2"><i class="bi bi-cash-coin"></i> Supplier Payments</a>
            </div>
        </div>
        <!-- User Management -->
<div class="mb-2">
    <a class="text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#userMenu">
        <span><i class="bi bi-person-gear"></i> User Management</span>
        <i class="bi bi-caret-down-fill"></i>
    </a>
    <div class="collapse ps-3 pt-2" id="userMenu">
        <a href="add_user.php" class="text-white d-block mb-2"><i class="bi  bi-card-list"></i> Add User</a>
        <a href="users.php" class="text-white d-block mb-2"><i class="bi bi-people-fill"></i> User List</a>
    </div>
</div>


<div class="mb-2">
  <a class="text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#reportMenu">
        
  <span><i class="bi bi-graph-up"></i> Report</span>

        <i class="bi bi-caret-down-fill"></i>
            </a>
              <div class="collapse ps-3 pt-2" id="reportMenu">
        <a href="expiry_report.php" class="text-white d-block mb-2"><i class="bi bi-item-plus"></i> Expired Items</a>
        <a href="users.php" class="text-white d-block mb-2"><i class="bi bi-people-fill"></i> User List</a>
    </div>
  
  </div>     
  
  
        <!--  <a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a> -->
    </div>

    <div class="container-fluid p-4">
        <div class="dashboard-box mb-4">
            <div class="dashboard-heading">
                <span id="greeting"></span>, <?= htmlspecialchars($user['full_name']) ?> <small class="text-muted">(<?= ucfirst($user['role']) ?>)</small>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="alert alert-info text-center">
                    <h6>💰 Total Sales</h6>
                    <strong>₨ <?= number_format($total_sales, 2) ?></strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-success text-center">
                    <h6>🧾 Total Invoices</h6>
                    <strong><?= $total_invoices ?></strong>
                </div>
            </div>
            <?php if ($user['role'] === 'admin'): ?>
            <div class="col-md-4">
                <div class="alert alert-secondary text-center">
                    <h6>📦 Products | 👥 Customers | 🏢 Suppliers</h6>
                    <strong><?= $total_products ?> | <?= $total_customers ?> | <?= $total_suppliers ?></strong>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="dashboard-box text-center">
                    <h6>📦 Products</h6>
                    <h4><?= $total_products ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-box text-center">
                    <h6>👥 Customers</h6>
                    <h4><?= $total_customers ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-box text-center">
                    <h6>🏢 Suppliers</h6>
                    <h4><?= $total_suppliers ?></h4>
                </div>
            </div>
        </div>

    

        <!-- New Charts -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="dashboard-box">
                    <h6 class="text-center">📅 Monthly Sales</h6>
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="dashboard-box">
                    <h6 class="text-center">📈 Revenue Trend</h6>
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Dark mode
    const toggle = document.getElementById('darkToggle');
    const body = document.body;
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark-mode');
        toggle.checked = true;
    }
    toggle.addEventListener('change', () => {
        body.classList.toggle('dark-mode');
        localStorage.setItem('theme', toggle.checked ? 'dark' : 'light');
    });

    // Greeting
    const hour = new Date().getHours();
    const greet = (hour < 12) ? "Good Morning" : (hour < 18) ? "Good Afternoon" : "Good Evening";
    document.getElementById("greeting").textContent = greet;

    // Doughnut Chart
    new Chart(document.getElementById("salesChart"), {
        type: 'doughnut',
        data: {
            labels: ['Total Sales', 'Invoices'],
            datasets: [{
                data: [<?= $total_sales ?>, <?= $total_invoices ?>],
                backgroundColor: ['#0d6efd', '#198754'],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Monthly Sales Chart
    new Chart(document.getElementById("monthlySalesChart"), {
        type: "bar",
        data: {
            labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            datasets: [{
                label: "Monthly Sales",
                data: [12000, 15000, 10000, 18000, 22000, 25000, 21000, 23000, 19000, 27000, 30000, 32000],
                backgroundColor: "#0d6efd"
            }]
        }
    });

    // Revenue Trend Chart
    new Chart(document.getElementById("revenueTrendChart"), {
        type: "line",
        data: {
            labels: ["Week 1", "Week 2", "Week 3", "Week 4"],
            datasets: [{
                label: "Revenue",
                data: [4000, 5000, 7000, 8000],
                fill: false,
                borderColor: "#198754",
                tension: 0.3
            }]
        }
    });

    // Download Chart
    function downloadChart() {
        const canvas = document.getElementById("salesChart");
        const link = document.createElement("a");
        link.download = "sales_chart.png";
        link.href = canvas.toDataURL("image/png");
        link.click();
    }
</script>
</body>
<footer class="footer mt-5 text-center text-muted small py-3">
    <p class="mb-1">© <?= date('Y') ?> <strong>3Partners Company</strong>. All rights reserved.</p>
   
</footer>

</html>
