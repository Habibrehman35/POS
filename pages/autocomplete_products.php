<?php
require '../config.php';

$term = $_GET['term'] ?? '';

$stmt = $pdo->prepare("SELECT id, name, supplier_name, quantity FROM products WHERE name LIKE ? LIMIT 10");
$stmt->execute(["%$term%"]);

$results = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $results[] = [
        'label' => $row['name'],
        'value' => $row['name'],
        'id' => $row['id'],
        'supplier_name' => $row['supplier_name'] ?? 'N/A',
        'quantity' => $row['quantity'] ?? 0
    ];
}

echo json_encode($results);
