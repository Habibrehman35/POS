<?php
session_start();
require '../config.php';

// Remember Me Logic
if (isset($_COOKIE['remember_username'])) {
    $saved_username = $_COOKIE['remember_username'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($remember) {
            setcookie('remember_username', $username, time() + (86400 * 30), "/"); // 30 days
        } else {
            setcookie('remember_username', '', time() - 3600, "/");
        }

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>POS Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
body {
    background: url('../assets/images/login.png') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Segoe UI', sans-serif;
    height: 100vh;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-container {
    /*background: rgba(255, 255, 255, 0.9);  Light blur effect */
  
    border-radius: 20px;
    padding: 40px;
    max-width: 420px;
    width: 100%;
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    animation: fadeIn 0.7s ease-out forwards;
    opacity: 0;
    margin-left: 220px;
    padding-top: 20px;
    height: 300px;
}
@keyframes fadeIn {
    to { opacity: 1; }
}



        .login-title {
            font-weight: bold;
            color: #0d6efd;
            text-align: center;
        }

        .form-icon {
            position: absolute;
            left: 12px;
            top: 10px;
            font-size: 1rem;
            color: #6c757d;
        }

        .input-group .form-control {
            padding-left: 35px;
        }

        .btn-primary {
            background-color: #0d6efd;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #084298;
        }

        .form-check-label {
            cursor: pointer;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .shadow-light {
            box-shadow: 0 3px 10px rgba(0,0,0,0);
        }
        .footer-custom {
   

    position: absolute;
    bottom: 150px;
    left: 200px;
    right: 0;
    font-size: 0.85rem;
}

    </style>
</head>
<body>

<div class="login-container shadow-light">
    <h4 class="login-title mb-4">💼 POS System Login</h4>

    <form method="POST">
        <div class="mb-3 position-relative input-group">
            <span class="form-icon"><i class="bi bi-person"></i></span>
            <input type="text" name="username" class="form-control" placeholder="Username"
                   value="<?= htmlspecialchars($saved_username ?? '') ?>" required>
        </div>

        <div class="mb-3 position-relative input-group">
            <span class="form-icon"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>

      <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="form-check d-flex align-items-center m-0">
        <input type="checkbox" name="remember" id="remember" class="form-check-input me-2"
               <?= isset($saved_username) ? 'checked' : '' ?>>
        <label for="remember" class="form-check-label mb-0">Remember Me</label>
    </div>
    <a href="#" class="text-decoration-none text-muted small">Forgot Password?</a>
</div>


        <button type="submit" class="btn btn-primary w-100 shadow-sm">🔐 Login</button>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger mt-3 text-center"><?= $error ?></div>
        <?php endif; ?>
    </form>
</div>


</body>
<footer class="footer-custom text-center text-white small">
    <p class="mb-1">© <?= date('Y') ?> <strong>3Partners Company</strong>. All rights reserved.</p>
   
</footer>


</html>
