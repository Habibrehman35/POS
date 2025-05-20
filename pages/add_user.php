<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $error = "❌ Username already taken.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $username, $password, $role, $status]);
        $success = "✅ User added successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New User - POS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0"><i class="bi bi-person-plus-fill"></i> Add New User</h4>
                    </div>
                    <div class="card-body">

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?></div>
                        <?php elseif ($success): ?>
                            <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= $success ?></div>
                        <?php endif; ?>

                        <form method="POST" class="mt-3">
                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-person-fill"></i> Full Name</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-person-badge-fill"></i> Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-shield-lock-fill"></i> Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-person-gear"></i> Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="cashier">Cashier</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-toggle-on"></i> Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="">Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="disabled">Disabled</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-dark"><i class="bi bi-check-circle"></i> Save</button>
                                <a href="users.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle"></i> Back to List</a>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-muted small text-center">
                        © <?= date('Y') ?> <strong>3Partners POS</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
