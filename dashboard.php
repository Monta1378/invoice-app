<?php
require 'auth.php';
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
    // Invoices marked "paid" with the old button have no payment records, so treat a paid invoice as fully collected
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
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="custom.css" rel="stylesheet">
    </head>
    <body>
        <?php include 'nav.php'; ?>
        <div class="container">
            <h1 class="page-title">Dashboard</h1>
            <div class="stat-row">
                <div class="stat-card">
                    <div class="stat-label">Total Invoiced</div>
                    <div class="stat-value"><?= number_format($totalInvoiced, 2) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Collected</div>
                    <div class="stat-value accent"><?= number_format($totalCollected, 2) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Outstanding</div>
                    <div class="stat-value warn"><?= number_format($totalOutstanding, 2) ?></div>
                </div>
            </div>
            <div class="stat-row">
                <div class="stat-card">
                    <span class="pill pill-warn">Unpaid</span>
                    <div class="stat-value-sm"><?= $counts['unpaid'] ?> invoices</div>
                </div>
                <div class="stat-card">
                    <span class="pill pill-info">Partial</span>
                    <div class="stat-value-sm"><?= $counts['partial'] ?> invoices</div>
                </div>
                <div class="stat-card">
                    <span class="pill pill-accent">Paid</span>
                    <div class="stat-value-sm"><?= $counts['paid'] ?> invoices</div>
                </div>
            </div>
            <h2 class="section-title">Overdue Invoices</h2>
            <?php if (empty($overdue)): ?>
                <div class="card p-4">You're all caught up &mdash; nothing overdue.</div>
                <?php else: ?>
                    <div class="list-card">
                        <?php foreach ($overdue as $o): ?>
                            <a href="view_invoice.php?id=<?= $o['id'] ?>" class="list-row">
                                <div class="list-row-main">
                                    <div class="list-row-title"><?= htmlspecialchars($o['invoice_number']) ?></div>
                                    <div class="list-row-sub"><?= htmlspecialchars($o['client_name']) ?> &middot; due <?= htmlspecialchars($o['due_date']) ?></div>
                                </div>
                                <div class="list-row-amount"><?= number_format($o['balance'], 2) ?></div>
                            </a>
                            <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
        </div>
    </body>
</html>