<?php
require '../config.php';

if (!isset($_GET['sale_id'])) exit;

$sale_id = $_GET['sale_id'];

$stmt = $pdo->prepare("
    SELECT si.*, p.name 
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = ?
");
$stmt->execute([$sale_id]);
$items = $stmt->fetchAll();

foreach ($items as $item): ?>
<div class="row mb-3 align-items-center">
    <input type="hidden" name="product_id[]" value="<?= $item['product_id'] ?>">

    <div class="col-md-4">
        <input type="text" class="form-control" value="<?= htmlspecialchars($item['name']) ?>" readonly>
    </div>

    <div class="col-md-2">
        <input type="number" name="return_qty[]" class="form-control" min="0" max="<?= $item['quantity'] ?>" value="0">
        <small class="text-muted">Sold: <?= $item['quantity'] ?></small>
    </div>

    <div class="col-md-6">
        <input type="text" name="reason[]" class="form-control" placeholder="Reason (optional)">
    </div>
</div>
<?php endforeach; ?>
