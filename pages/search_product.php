<?php
require '../config.php';

$term = $_GET['term'] ?? '';
$term = '%' . $term . '%';

$stmt = $pdo->prepare("SELECT id, name, barcode, image, quantity FROM products WHERE name LIKE ? LIMIT 10");
$stmt->execute([$term]);
$products = $stmt->fetchAll();

foreach ($products as $i => $p):
    $id = $p['id'];
    $img = $p['image'] ? "../uploads/{$p['image']}" : "https://via.placeholder.com/40x40";
    $qty = (int)$p['quantity'];  // Correct the quantity check here
    $is_out = $qty <= 0;
?>
<div class="d-flex align-items-center justify-content-between border-bottom py-3 px-3 <?= $is_out ? 'opacity-50' : '' ?>" style="border-radius: 8px; background-color: #f8f9fa; margin-bottom: 10px;">
    <div class="d-flex align-items-center">
        <img src="<?= $img ?>" alt="" width="50" height="50" class="me-3 rounded-circle border">
        <div>
            <strong><?= htmlspecialchars($p['name']) ?></strong>
            <?php if ($is_out): ?>
                <span class="ms-2 badge bg-danger">Out of Stock</span>
            <?php endif; ?>
            <div class="text-muted" style="font-size: 14px;">Available: <?= $qty ?> pcs</div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <input type="hidden" id="barcode_<?= $i ?>" value="<?= $p['barcode'] ?>">
        <input type="number" min="1" value="1" id="qty_<?= $i ?>" class="form-control form-control-sm" style="width: 70px;" <?= $is_out ? 'disabled' : '' ?> data-stock="<?= $qty ?>"> <!-- Adding stock value as attribute -->
        <button onclick="addToCart('barcode_<?= $i ?>','qty_<?= $i ?>')" class="btn btn-sm btn-primary" <?= $is_out ? 'disabled' : '' ?>>Add</button>
    </div>
</div>
<?php endforeach; ?>
