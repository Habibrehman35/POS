<?php
require '../config.php';

$searchTerm = $_GET['q'] ?? '';

$stmt = $pdo->prepare("SELECT id, name, supplier_name FROM products WHERE name LIKE ? ORDER BY name LIMIT 20");
$stmt->execute(["%$searchTerm%"]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
