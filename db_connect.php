<?php
// Update these values with your actual database details
$host = 'localhost';
$dbname = 'pos_enterprise';  // your database name
$username = 'root';          // your DB username
$password = '';              // your DB password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}
?>
