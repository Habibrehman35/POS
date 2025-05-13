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
    $new_password = $_POST['password'];

    if (!empty($new_password)) {
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET full_name=?, username=?, password=?, role=? WHERE id=?");
        $stmt->execute([$full_name, $username, $hashed, $role, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name=?, username=?, role=? WHERE id=?");
        $stmt->execute([$full_name, $username, $role, $id]);
    }

    header("Location: users.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>✏️ Edit User</h3>

    <form method="POST" class="mt-3">
        <div class="mb-3">
            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="form-control" placeholder="Full Name" required>
        </div>
        <div class="mb-3">
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="form-control" placeholder="Username" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="New Password (leave blank to keep old)">
        </div>
        <div class="mb-3">
            <select name="role" class="form-select" required>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="cashier" <?= $user['role'] === 'cashier' ? 'selected' : '' ?>>Cashier</option>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</body>
</html>
