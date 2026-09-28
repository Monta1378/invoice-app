<?php
require 'db.php';

$token = $_GET['t'] ?? '';

if ($token === '') {
    http_response_code(404);
    die("Invoice not found.");
}

$stmt = $pdo->prepare("
    SELECT i.*, c.name AS client_name, c.email AS client_email, c.address AS client_address
    FROM invoices i
    JOIN clients c ON i.client_id = c.id
    WHERE i.share_token = ?
");
$stmt->execute([$token]);
$invoice = $stmt->fetch();

if (!$invoice) {
    http_response_code(404);
    die("Invoice not found.");
}

$stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$stmt->execute([$invoice['id']]);
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['unit_price'];
}

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) AS paid FROM payments WHERE invoice_id = ?");
$stmt->execute([$invoice['id']]);
$totalPaid = $stmt->fetch()['paid'];
if ($invoice['status'] === 'paid' && $totalPaid < $total) {
    $totalPaid = $total; // marked paid with the old button, no payment records
}
$balance = $total - $totalPaid;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice <?= htmlspecialchars($invoice['invoice_number']) ?></title>
    <meta name="robots" content="noindex">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-4">
        <div class="card p-4">
            <h1>Invoice <?= htmlspecialchars($invoice['invoice_number']) ?></h1>

            <p><strong>Billed to:</strong> <?= htmlspecialchars($invoice['client_name']) ?></p>
            <p><strong>Invoice Date:</strong> <?= htmlspecialchars($invoice['invoice_date']) ?></p>
            <p><strong>Due Date:</strong> <?= htmlspecialchars($invoice['due_date']) ?></p>
            <p><strong>Status:</strong>
                <?php if ($invoice['status'] === 'paid'): ?>
                    <span class="badge bg-success">Paid</span>
                <?php elseif ($invoice['status'] === 'partial'): ?>
                    <span class="badge bg-info text-dark">Partially Paid</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Unpaid</span>
                <?php endif; ?>
            </p>

            <table class="table table-bordered mt-3">
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Line Total</th>
                </tr>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['unit_price'], 2) ?></td>
                    <td><?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <h2 class="text-end">Total: <?= number_format($total, 2) ?></h2>
            <p class="text-end mb-0">Paid: <?= number_format($totalPaid, 2) ?></p>
            <h4 class="text-end">Balance Due: <?= number_format($balance, 2) ?></h4>

            <?php if (!empty($invoice['terms'])): ?>
                <hr>
                <h5>Terms &amp; Conditions</h5>
                <p class="text-muted"><?= nl2br(htmlspecialchars($invoice['terms'])) ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>