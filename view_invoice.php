<?php
require 'auth.php';
require 'db.php';
$id = $_GET['id'];
// Handle creating / turning off the share link
if (isset($_GET['share'])) {
    checkCsrf();
    if ($_GET['share'] === 'on') {
        $token = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare("UPDATE invoices SET share_token = ? WHERE id = ? AND share_token IS NULL");
        $stmt->execute([$token, $id]);
    } elseif ($_GET['share'] === 'off') {
        $stmt = $pdo->prepare("UPDATE invoices SET share_token = NULL WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: view_invoice.php?id=$id");
    exit;
}

// Handle recordeing a new payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $notes = $_POST['notes'];
    $stmt = $pdo->prepare("INSERT INTO payments (invoice_id, amount, payment_date, notes) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id, $amount, $payment_date, $notes]);

// Recalculate status based on total paid so far
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) AS total_paid FROM payments WHERE invoice_id = ?");
$stmt->execute([$id]);
$totalPaid = $stmt->fetch()['total_paid'];
$stmt = $pdo->prepare("
SELECT COALESCE(SUM(quantity * unit_price), 0) AS invoice_total
FROM invoice_items WHERE invoice_id = ? ");
$stmt->execute([$id]);
$invoiceTotal = $stmt->fetch()['invoice_total'];
if ($totalPaid >= $invoiceTotal) {
    $newStatus = 'paid';
} elseif ($totalPaid > 0) {
    $newStatus = 'partial';
} else {
    $newStatus = 'unpaid';
}
$stmt = $pdo->prepare("UPDATE invoices SET status = ? WHERE id = ?");
$stmt->execute([$newStatus, $id]);
header("Location: view_invoice.php?id=$id");
exit;
}

//Get the invoice + client info together
$stmt = $pdo->prepare("
SELECT i.*, c.name AS client_name, c.email AS client_email, c.address AS client_address
FROM invoices i
JOIN clients c ON i.client_id = c.id
WHERE i.id = ?
");
$stmt->execute([$id]);
$invoice = $stmt->fetch();

//Get all items for this invoice
$stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

//Calculate the grand total
$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['unit_price'];
}

// Get all payments for this invoice
$stmt = $pdo->prepare("SELECT * FROM payments WHERE invoice_id = ? ORDER BY payment_date");
$stmt->execute([$id]);
$payments = $stmt->fetchAll();
$totalPaid = 0;
foreach ($payments as $p) {
    $totalPaid += $p['amount'];
}
$balance = $total -$totalPaid;
$shareUrl = '';
if (!empty($invoice['share_token'])) {
    $shareUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/public_invoice.php?t=' . $invoice['share_token'];
}
$settings = $pdo->query("SELECT * FROM business_settings WHERE id = 1")->fetch();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Invoice <?= htmlspecialchars($invoice['invoice_number']) ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="custom.css" rel="stylesheet">
    </head>
    <body>
        <?php include 'nav.php'; ?>
        <div class="container">
            <div class="card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1>Invoice <?= htmlspecialchars($invoice['invoice_number']) ?></h1>
                    <?php if (!empty($settings['business_name'])): ?>
                        <div class="mb-3">
                            <strong><?= htmlspecialchars($settings['business_name']) ?></strong><br>
                            <?php if (!empty($settings['address'])): ?><?= htmlspecialchars($settings['address']) ?><br><?php endif; ?>
                                <?php if (!empty($settings['phone'])): ?><?= htmlspecialchars($settings['phone']) ?><br><?php endif; ?>
                                    <?php if (!empty($settings['email'])): ?><?= htmlspecialchars($settings['email']) ?><?php endif; ?>
                        </div>
                        <hr>
                        <?php endif; ?>
                    <a href="invoice_pdf.php?id=<?= $invoice['id'] ?>" class="btn btn-primary">Download PDF</a>
                </div>
                <p><strong>Client:</strong><?= htmlspecialchars($invoice['client_name']) ?></p>
                <p><strong>Email:</strong><?= htmlspecialchars($invoice['client_email']) ?></p>
                <p><strong>Address:</strong><?= htmlspecialchars($invoice['client_address']) ?></p>
                <p><strong>Invoice Date:</strong><?= htmlspecialchars($invoice['invoice_date']) ?></p>
                <p><strong>Due Date:</strong><?= htmlspecialchars($invoice['due_date']) ?></p>
                <p><strong>Status:</strong>
            <?php if ($invoice['status'] === 'paid'): ?>
            <span class="pill pill-success">Paid</span>
        <?php elseif ($invoice['status'] === 'partial'): ?>
            <span class="pill pill-info text-dark">Partially paid</span>
            <?php else: ?>
        <span class="pill pill-warning text-dark">Unpaid</span>
    <?php endif; ?>
</p>
        <table class="table table-bordered mt-3">
            <tr>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit price</th>
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
        <?php if (!empty($invoice['terms'])): ?>
            <hr>
            <h5>Terms &amp; Conditions</h5>
            <p class="text-muted"><?= nl2br(htmlspecialchars($invoice['terms'])) ?></p>
            <?php endif; ?>
        </div>
        <div class="card p-4">
            <h3>Payments</h3>
            <?php if (empty($payments)): ?>
                <p class="text-muted">No payments recorded yet.</p>
                <?php else: ?>
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Notes</th>
                        </tr>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['payment_date']) ?></td>
                                <td><?= number_format($p['amount'], 2) ?></td>
                                <td><?= htmlspecialchars($p['notes']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                    <p><strong>Total Paid:</strong> <?= number_format($totalPaid, 2) ?></p>
                    <p><strong>Balance remaining:</strong> <?= number_format($balance, 2) ?></p>
                    <?php if ($balance > 0): ?>
                        <form method="POST" class="row g-2 mt-3">
                            <div class="col-md-3">
                                <label class="form-label">Amount</label>
                                <input type="number" step="0.01" name="amount" class="form-control" max="<?= $balance ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="payment_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Notes</label>
                                <input type="text" name="notes" class="form-control" placeholder="e.g. M-Pesa, bank transfer">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100">Record Payment</button>
                            </div>
                        </form>
                        <?php endif; ?>
        </div>
        <div class="card p-4 mt-4">
            <h3>Share Invoice</h3>
            <?php if ($shareUrl === ''): ?>
                <p class="text-muted">This invoice isn't shared yet.</p>
                <a href="view_invoice.php?id=<?= $id ?>&share=on&csrf=<?= $_SESSION['csrf_token'] ?>" class="btn btn-outline-primary" style="width: fit-content;">Create share link</a>
                <?php else: ?>
                    <div class="input-group mb-2">
                        <input type="text" id="share-link" class="form-control" value="<?= htmlspecialchars($shareUrl) ?>" readonly>
                        <button type="button" class="btn btn-primary" onclick="copyLink()">Copy</button>
                    </div>
                    <a href="view_invoice.php?id=<?= $id ?>&share=off&csrf=<?= $_SESSION['csrf_token'] ?>" class="btn btn-sm btn-outline-danger" style="width: fit-content;"
                    onclick="return confirm('Turn off sharing? The old link will stop working.')">Turn off sharing</a>
                    <?php endif; ?>
            </div>
            <script>
            function copyLink() {
                const input = document.getElementById('share-link');
                navigator.clipboard.writeText(input.value);
                }
            </script>
        </div>
        <?php include 'footer.php'; ?>
    </body>
</html>