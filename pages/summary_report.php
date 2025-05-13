<?php
session_start();
require '../config.php';

// Fetch total sales for today
$period = $_GET['period'] ?? 'today';
$total_sales = 0.00;
$transactions_count = 0;
$top_selling_products = [];

// Fetch sales data based on the period
switch ($period) {
    case 'today':
        // Sales for today
        $stmt = $pdo->prepare("SELECT SUM(total_amount) AS total_sales, COUNT(*) AS transactions_count FROM sales WHERE DATE(sale_date) = CURDATE()");
        break;
    case 'week':
        // Sales for the current week
        $stmt = $pdo->prepare("SELECT SUM(total_amount) AS total_sales, COUNT(*) AS transactions_count FROM sales WHERE YEARWEEK(sale_date, 1) = YEARWEEK(CURDATE(), 1)");
        break;
    case 'month':
        // Sales for the current month
        $stmt = $pdo->prepare("SELECT SUM(total_amount) AS total_sales, COUNT(*) AS transactions_count FROM sales WHERE MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())");
        break;
    default:
        $stmt = $pdo->prepare("SELECT SUM(total_amount) AS total_sales, COUNT(*) AS transactions_count FROM sales WHERE DATE(sale_date) = CURDATE()");
        break;
}

$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_sales = $result['total_sales'] ?? 0.00;
$transactions_count = $result['transactions_count'] ?? 0;

// Fetch top selling products (based on quantity sold)
$top_selling_stmt = $pdo->prepare("
    SELECT p.name, SUM(si.quantity) AS total_sold 
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    GROUP BY si.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");
$top_selling_stmt->execute();
$top_selling_products = $top_selling_stmt->fetchAll();
?>
