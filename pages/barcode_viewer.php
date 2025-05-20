<?php
$barcode = $_GET['barcode'] ?? '';
$qty = (int) ($_GET['qty'] ?? 1);
$name = $_GET['name'] ?? 'Product';

if (!$barcode || $qty < 1) {
    echo "Invalid barcode or quantity.";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Barcode Labels</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f8f9fa;
        }

        #controls {
            text-align: center;
            margin-bottom: 20px;
        }

        .label {
            width: 250px;
            height: 120px;
            border: 2px dashed #555;
            margin: 10px auto;
            padding: 10px;
            background: white;
            text-align: center;
            page-break-inside: avoid;
        }

        .label h4 {
            margin: 5px 0 10px 0;
            font-size: 16px;
        }

        .label img {
            height: 60px;
        }

        @media print {
            #controls { display: none; }
            body { background: white; }
        }

        @page {
            margin: 10mm;
        }
    </style>
</head>
<body>

<div id="controls">
    <button onclick="window.print()">🖨️ Print</button>
    <button onclick="downloadPDF()">⬇️ Download PDF</button>
</div>

<?php for ($i = 0; $i < $qty; $i++): ?>
    <div class="label">
       <h4><?= htmlspecialchars($name) ?></h4>
        <img src="barcode_image.php?code=<?= urlencode($barcode) ?>" alt="Barcode">
        <div><?= htmlspecialchars($barcode) ?></div>
    </div>
<?php endfor; ?>

<!-- jsPDF + html2canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
async function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');

    const labels = document.querySelectorAll('.label');
    for (let i = 0; i < labels.length; i++) {
        const canvas = await html2canvas(labels[i]);
        const imgData = canvas.toDataURL('image/png');
        const imgProps = doc.getImageProperties(imgData);
        const pdfWidth = 80;
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

        if (i > 0) doc.addPage();
        doc.addImage(imgData, 'PNG', 15, 20, pdfWidth, pdfHeight);
    }

    doc.save("barcodes_<?= htmlspecialchars($barcode) ?>.pdf");
}
</script>
<!-- jsPDF + html2canvas + html2pdf -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
function downloadPDF() {
    const element = document.querySelector('.labels-container');

    // Get product name from the first label
    const firstLabel = document.querySelector('.label h4');
    const name = firstLabel ? firstLabel.textContent.trim().replace(/\s+/g, '_') : 'barcode_labels';

    const opt = {
        margin:       5,
        filename:     `barcode_labels_${name}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'mm', format: 'auto', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save();
}
</script>

</body>
</html>


