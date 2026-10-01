<?php
require 'auth.php';
require 'db.php';
// Handle marking as paid
if (isset($_GET['mark_paid'])) {
    checkCsrf();
    $id = $_GET['mark_paid'];
    $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = ?");
    $stmt->execute([$id]);
    header ("Location: invoices.php");
    exit;
}
// Handle delete
if (isset($_GET['delete'])) {
    checkCsrf();
    $id = $_GET['delete'];
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
    $stmt->execute([$id]);
    $stmt = $pdo->prepare("DELETE FROM invoices WHERE id = ?");
    $stmt->execute([$id]);
    $pdo->commit();
    header("Location: invoices.php");
    exit;
}
$sql = "
SELECT i.id, i.invoice_number, i.invoice_date, i.due_date, i.status,
c.name AS client_name,
COALESCE(SUM(ii.quantity * ii.unit_price), 0 ) AS total
FROM invoices i
JOIN clients c ON i.client_id = c.id
LEFT JOIN invoice_items ii ON ii.invoice_id = i.id
GROUP BY i.id
ORDER BY i.invoice_date DESC
";
$invoices = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Invoices</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="custom.css" rel="stylesheet">
    </head>
    <body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1 class="page-title">Invoices</h1>

        <div class="list-card" style="overflow-x: auto;">
            <table class="table mb-0" style="background: transparent;">
                <tr>
                    <th scope="col">Invoice</th>
                    <th scope="col">Client</th>
                    <th scope="col">Date</th>
                    <th scope="col">Due</th>
                    <th scope="col">Status</th>
                    <th scope="col">Total</th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                </tr>
                <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td><?= htmlspecialchars($inv['invoice_number']) ?></td>
                    <td><?= htmlspecialchars($inv['client_name']) ?></td>
                    <td><?= htmlspecialchars($inv['invoice_date']) ?></td>
                    <td><?= htmlspecialchars($inv['due_date']) ?></td>
                    <td>
                        <?php if ($inv['status'] === 'paid'): ?>
                            <span class="pill pill-accent">Paid</span>
                        <?php elseif ($inv['status'] === 'partial'): ?>
                            <span class="pill pill-info">Partial</span>
                        <?php else: ?>
                            <span class="pill pill-warn">Unpaid</span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format($inv['total'], 2) ?></td>
                    <td><a href="view_invoice.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                    <td><a href="edit_invoice.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                    <td>
                        <?php if ($inv['status'] !== 'paid'): ?>
                            <a href="invoices.php?mark_paid=<?= $inv['id'] ?>&csrf=<?= $_SESSION['csrf_token'] ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Mark this invoice as paid?')">Mark Paid</a>
                        <?php else: ?>
                            <span class="pill pill-accent">✓</span>
                        <?php endif; ?>
                    </td>
                    <td><a href="invoices.php?delete=<?= $inv['id'] ?>&csrf=<?= $_SESSION['csrf_token'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this invoice and all its items?')">Delete</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>