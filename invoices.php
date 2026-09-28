<?php
require 'db.php';
// Handle marking as paid
if (isset($_GET['mark_paid'])) {
    $id = $_GET['mark_paid'];
    $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid' WHERE id = ?");
    $stmt->execute([$id]);
    header ("Location: invoices.php");
    exit;
}
// Handle delete
if (isset($_GET['delete'])) {
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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <?php include 'nav.php'; ?>
        <div class="container">
        <h1>Invoices</h1>
        <table class="table table-striped table-bordered">
            <tr>
                <th>Invoice</th>
                <th>Client</th>
                <th>Date</th>
                <th>Due</th>
                <th>Status</th>
                <th>Total</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td><?= htmlspecialchars($inv['invoice_number']) ?></td>
                    <td><?= htmlspecialchars($inv['client_name']) ?></td>
                    <td><?= htmlspecialchars($inv['invoice_date']) ?></td>
                    <td><?= htmlspecialchars($inv['due_date']) ?></td>
                    <td><?php if ($inv['status'] === 'paid'): ?>
                        <span class="badge bg-success">Paid</span>
                        <?php elseif ($inv['status'] === 'partial'): ?>
                            <span class="badge bg-info text-dark">Partial</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Unpaid</span>
                            <?php endif; ?>
                    </td>
                    <td><?= number_format($inv['total'], 2) ?></td>
                    <td><a href="view_invoice.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                    <td><a href="edit_invoice.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a></td>
                    <td>
                        <?php if ($inv['status'] !== 'paid'): ?>
                        <a href="invoices.php?mark_paid=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Mark this invoice as paid?')">Mark as Paid</a>
                    <?php else: ?>
                        ✅ Paid
                    <?php endif; ?>
                    </td>
                    <td><a href="invoices.php?delete=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this invoice and all its items')">Delete</a></td>
                </tr>
                <?php endforeach; ?>
        </table>
        </div>
    </body>
</html>