<?php
require '../config.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
   <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">👤 User Management</h3>
    <a href="dashboard.php" class="btn btn-outline-dark">← Back</a>
</div>

<a href="add_user.php" class="btn btn-primary mb-3">➕ Add User</a>

    
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $i => $u): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($u['full_name']) ?></td>
                <td><?= $u['username'] ?></td>
                <td><?= ucfirst($u['role']) ?></td>
                <td><?= $u['status'] ? '🟢 Active' : '🔴 Inactive' ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
</body>
</html>
