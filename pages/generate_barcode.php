<?php
require '../config.php';
require '../vendor/autoload.php'; // For barcode
require '../lib/fpdf.php';        // Your custom FPDF class

use Picqer\Barcode\BarcodeGeneratorPNG;

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('❌ Invalid product ID.');
}

$id = (int)$_GET['id'];

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    exit("❌ Product not found.");
}

// Generate barcode image
$generator = new BarcodeGeneratorPNG();
$barcode = $generator->getBarcode($product['barcode'], $generator::TYPE_CODE_128);
$tempFile = __DIR__ . "/barcode_temp.png";
file_put_contents($tempFile, $barcode);

// Send PDF headers
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="barcode_' . $product['id'] . '.pdf"');

// Create PDF
$pdf = new FPDF('P', 'mm', array(60, 40)); // Label size
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Product Barcode', 0, 1, 'C');

// Barcode image
$pdf->Image($tempFile, 15, 15, 30, 15); // (x, y, w, h)

// Name + price
$pdf->SetFont('Arial', '', 9);
$pdf->SetY(32);
$pdf->Cell(0, 5, 'Name: ' . $product['name'], 0, 1, 'C');
$pdf->Cell(0, 5, 'Price: $' . number_format($product['price'], 2), 0, 1, 'C');

// Delete temp
unlink($tempFile);

// Output
$pdf->Output();
exit;
