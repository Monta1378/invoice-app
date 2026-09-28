<?php
require 'db.php';

// Invoiced per month (by invoice date) -> ['2026-08' => 21000.00, ...]
$invoiced = $pdo->query("
    SELECT DATE_FORMAT(i.invoice_date, '%Y-%m') AS month,
           SUM(ii.quantity * ii.unit_price) AS total
    FROM invoices i
    JOIN invoice_items ii ON ii.invoice_id = i.id
    GROUP BY month
    ORDER BY month
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Collected per month (by payment date)
$collected = $pdo->query("
    SELECT DATE_FORMAT(payment_date, '%Y-%m') AS month,
           SUM(amount) AS total
    FROM payments
    GROUP BY month
    ORDER BY month
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Build one shared list of months, filling gaps with 0
$months = array_unique(array_merge(array_keys($invoiced), array_keys($collected)));
sort($months);
$invoicedData  = array_map(fn($m) => (float)($invoiced[$m] ?? 0), $months);
$collectedData = array_map(fn($m) => (float)($collected[$m] ?? 0), $months);

// Invoice counts by status
$statusCounts = $pdo->query("SELECT status, COUNT(*) FROM invoices GROUP BY status")
                    ->fetchAll(PDO::FETCH_KEY_PAIR);
$statusData = [
    (int)($statusCounts['unpaid'] ?? 0),
    (int)($statusCounts['partial'] ?? 0),
    (int)($statusCounts['paid'] ?? 0),
];

// Top clients by amount invoiced
$topClients = $pdo->query("
    SELECT c.name, SUM(ii.quantity * ii.unit_price) AS total
    FROM clients c
    JOIN invoices i ON i.client_id = c.id
    JOIN invoice_items ii ON ii.invoice_id = i.id
    GROUP BY c.id
    ORDER BY total DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1>Reports</h1>

        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="card p-3">
                    <h5>Invoiced vs Collected per Month</h5>
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <h5>Invoices by Status</h5>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <h3>Top Clients</h3>
        <table class="table table-striped table-bordered">
            <tr>
                <th>Client</th>
                <th>Total Invoiced</th>
            </tr>
            <?php foreach ($topClients as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><?= number_format($c['total'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <script>
        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [
                    { label: 'Invoiced', data: <?= json_encode($invoicedData) ?>, backgroundColor: '#0d6efd' },
                    { label: 'Collected', data: <?= json_encode($collectedData) ?>, backgroundColor: '#198754' }
                ]
            }
        });

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Unpaid', 'Partial', 'Paid'],
                datasets: [{
                    data: <?= json_encode($statusData) ?>,
                    backgroundColor: ['#ffc107', '#0dcaf0', '#198754']
                }]
            }
        });
    </script>
</body>
</html>