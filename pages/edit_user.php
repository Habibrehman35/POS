<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("User ID required.");
}
$id = $_GET['id'];

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $new_password = $_POST['password'];

    if (!empty($new_password)) {
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET full_name=?, username=?, password=?, role=?, status=? WHERE id=?");
        $stmt->execute([$full_name, $username, $hashed, $role, $status, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name=?, username=?, role=?, status=? WHERE id=?");
        $stmt->execute([$full_name, $username, $role, $status, $id]);
    }

    header("Location: users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - POS</title>
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
                        <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Edit User</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-person-fill"></i> Full Name</label>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-person-badge-fill"></i> Username</label>
                                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-shield-lock-fill"></i> New Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-person-gear"></i> Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="cashier" <?= $user['role'] === 'cashier' ? 'selected' : '' ?>>Cashier</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-toggle-on"></i> Status</label>
                                <select name="status" class="form-select text-white 
                                    <?= $user['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>" required>
                                    <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="disabled" <?= $user['status'] === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-dark"><i class="bi bi-check-circle"></i> Update</button>
                                <a href="users.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle"></i> Cancel</a>
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
