<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

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
            background-color: #f4f6f9;
        }
        .container {
            max-width: 1100px;
            margin-top: 50px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.05);
        }
        h4 {
            font-weight: bold;
        }
        .search-input {
            font-size: 16px;
            padding: 10px 14px;
            border-radius: 8px;
        }
        .table thead th {
            background-color: #007bff12;
            color: #0d6efd;
            font-weight: 600;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }
        .table tbody tr:hover {
            background-color: #e9f5ff;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-primary mb-0">📋 Supplier List</h4>
            <a href="dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
        </div>

        <input type="text" id="searchInput" class="form-control search-input mb-4 shadow-sm" placeholder="🔍 Type to search supplier name...">

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="supplierTable">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Supplier Name</th>
                        <th scope="col">Contact Person</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Email</th>
                        <th scope="col">Address</th>
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
