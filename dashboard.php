<?php
require 'db.php';

// One query: each invoice with its total and the payments received so far
$sql = "
    SELECT i.id, i.invoice_number, i.due_date, i.status,
           c.name AS client_name,
           COALESCE((SELECT SUM(quantity * unit_price) FROM invoice_items WHERE invoice_id = i.id), 0) AS total,
           COALESCE((SELECT SUM(amount) FROM payments WHERE invoice_id = i.id), 0) AS paid
    FROM invoices i
    JOIN clients c ON i.client_id = c.id
";
$invoices = $pdo->query($sql)->fetchAll();

$today = date('Y-m-d');
$totalInvoiced = 0;
$totalCollected = 0;
$counts = ['unpaid' => 0, 'partial' => 0, 'paid' => 0];
$overdue = [];

foreach ($invoices as $inv) {
    // Invoices marked "paid" with the old button have no payment records,
    // so treat a paid invoice as fully collected
    $paid = ($inv['status'] === 'paid') ? $inv['total'] : $inv['paid'];
    $balance = $inv['total'] - $paid;

    $totalInvoiced += $inv['total'];
    $totalCollected += $paid;

    if (isset($counts[$inv['status']])) {
        $counts[$inv['status']]++;
    }

    if ($balance > 0 && !empty($inv['due_date']) && $inv['due_date'] < $today) {
        $inv['balance'] = $balance;
        $overdue[] = $inv;
    }
}

$totalOutstanding = $totalInvoiced - $totalCollected;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1>Dashboard</h1>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card p-3">
                    <div class="text-muted">Total Invoiced</div>
                    <h3><?= number_format($totalInvoiced, 2) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <div class="text-muted">Collected</div>
                    <h3 class="text-success"><?= number_format($totalCollected, 2) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <div class="text-muted">Outstanding</div>
                    <h3 class="text-danger"><?= number_format($totalOutstanding, 2) ?></h3>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card p-3">
                    <span class="badge bg-warning text-dark mb-2" style="width: fit-content;">Unpaid</span>
                    <h4><?= $counts['unpaid'] ?> invoices</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <span class="badge bg-info text-dark mb-2" style="width: fit-content;">Partial</span>
                    <h4><?= $counts['partial'] ?> invoices</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <span class="badge bg-success mb-2" style="width: fit-content;">Paid</span>
                    <h4><?= $counts['paid'] ?> invoices</h4>
                </div>
            </div>
        </div>

        <h3>Overdue Invoices</h3>
        <?php if (empty($overdue)): ?>
            <div class="alert alert-success">Nothing overdue. You're all caught up.</div>
        <?php else: ?>
            <div class="alert alert-danger">
                <?= count($overdue) ?> invoice(s) are past their due date.
            </div>
            <table class="table table-striped table-bordered">
                <tr>
                    <th>Invoice</th>
                    <th>Client</th>
                    <th>Due Date</th>
                    <th>Balance</th>
                    <th></th>
                </tr>
                <?php foreach ($overdue as $o): ?>
                <tr>
                    <td><?= htmlspecialchars($o['invoice_number']) ?></td>
                    <td><?= htmlspecialchars($o['client_name']) ?></td>
                    <td><?= htmlspecialchars($o['due_date']) ?></td>
                    <td><?= number_format($o['balance'], 2) ?></td>
                    <td><a href="view_invoice.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>