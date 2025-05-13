<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch all suppliers
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
        }
        .container {
            max-width: 1000px;
            margin-top: 40px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .search-input {
            font-size: 16px;
            padding: 10px;
        }
        .back-btn {
            margin-top: 20px;
        }
        .table th {
            background-color: #e3f2fd;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="text-primary mb-0">📋 Supplier List</h4>
            <a href="dashboard.php" class="btn btn-outline-secondary">← Back</a>
        </div>

        <input type="text" id="searchInput" class="form-control search-input mb-3" placeholder="🔍 Search supplier by name...">

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="supplierTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $index => $s): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['contact_person']) ?></td>
                            <td><?= htmlspecialchars($s['phone']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= htmlspecialchars($s['address']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const input = document.getElementById("searchInput");
    const rows = document.querySelectorAll("#supplierTable tbody tr");

    input.addEventListener("keyup", function () {
        const search = input.value.toLowerCase();

        rows.forEach(row => {
            const nameCell = row.cells[1].textContent.toLowerCase();
            row.style.display = nameCell.includes(search) ? "" : "none";
        });
    });
</script>

</body>
</html>
