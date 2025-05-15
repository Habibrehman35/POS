<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $contact_person = trim($_POST['contact_person']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);

    if ($name !== '') {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $contact_person, $phone, $email, $address]);
        $success = "✅ Supplier added successfully!";
    } else {
        $error = "❌ Supplier name is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f1f5f9;
        }
        .container {
            max-width: 720px;
            margin-top: 60px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.05);
        }
        h3 {
            font-weight: 700;
        }
        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary mb-0">➕ Add New Supplier</h3>
            <a href="suppliers.php" class="btn btn-outline-secondary">← Back</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success shadow-sm"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger shadow-sm"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">🏢 Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required placeholder="e.g., ABC Supplies Ltd.">
                </div>

                <div class="col-md-6">
                    <label for="contact_person" class="form-label">👤 Contact Person</label>
                    <input type="text" name="contact_person" id="contact_person" class="form-control" placeholder="e.g., John Doe">
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">📞 Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-control" placeholder="e.g., +123456789">
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">📧 Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="e.g., info@abc.com">
                </div>

                <div class="col-12">
                    <label for="address" class="form-label">📍 Address</label>
                    <textarea name="address" id="address" class="form-control" rows="2" placeholder="e.g., Street #, City, ZIP"></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-start gap-3">
                <button type="submit" class="btn btn-primary px-4">💾 Save Supplier</button>
                <a href="suppliers.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
