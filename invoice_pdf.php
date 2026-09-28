<?php
require 'db.php';
require 'vendor/autoload.php';
use Dompdf\Dompdf;
$id = $_GET['id'];
$stmt = $pdo->prepare("
SELECT i.*, c.name AS client_name, c.email AS client_email, c.address AS client_address
FROM invoices i
JOIN clients c ON i.client_id = c.id
WHERE i.id = ?
");
$stmt->execute([$id]);
$invoice = $stmt->fetch();
$stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();
$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['unit_price'];
}
// Build the HTML that will become the PDF
$html = '
<h1>Invoice ' . htmlspecialchars($invoice['invoice_number']) . '</h1>
<p><strong>Client:</strong> ' . htmlspecialchars($invoice['client_name']) . '</p>
<p><strong>Email:</strong> ' . htmlspecialchars($invoice['client_email']) . '</p>
<p><strong>Address:</strong> ' . htmlspecialchars($invoice['client_address']) . '</p>
<p><strong>Invoice Date:</strong> ' . htmlspecialchars($invoice['invoice_date']) . '</p>
<p><strong>Due Date:</strong> ' . htmlspecialchars($invoice['due_date']) . '</p>
<p><strong>Status:</strong> ' . htmlspecialchars($invoice['status']) . '</p>
<table border="1" cellpadding="8" style="width:100%; border-collapse: collapse;">
<tr>
<th>Description</th>
<th>Quantity</th>
<th>Unit Price</th>
<th>Line Total</th>
</tr>';
foreach ($items as $item) {
    $lineTotal= $item['quantity'] * $item['unit_price'];
    $html .= '
    <tr>
    <td>' . htmlspecialchars($item['description']) . '</td>
    <td>' . $item['quantity'] . '</td>
    <td>' . number_format($item['unit_price'], 2) . '</td>
    <td>' . number_format($lineTotal, 2) . '</td>
    </tr>';
}
$html .= '
</table>
<h2>Total: ' . number_format($total, 2) . '</h2> ';
if (!empty($invoice['terms'])) {
    $html .= '
    <hr>
    <h4>Terms &amp; Conditions</h4>
    <p>' . nl2br(htmlspecialchars($invoice['terms'])) . '</p>';
}
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'potrait');
$dompdf->render();
$dompdf->stream('invoice_' . $invoice['invoice_number'] . '.pdf', ['Attachment' => true]);