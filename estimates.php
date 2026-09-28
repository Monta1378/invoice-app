<?php
require 'db.php';

// Handle delete
if (isset($_GET['delete'])) {
    $delId = $_GET['delete'];
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("DELETE FROM estimate_items WHERE estimate_id = ?");
    $stmt->execute([$delId]);
    $stmt = $pdo->prepare("DELETE FROM estimates WHERE id = ?");
    $stmt->execute([$delId]);
    $pdo->commit();
    header("Location: estimates.php");
    exit;
}

$sql = "
    SELECT e.id, e.estimate_number, e.estimate_date, e.expiry_date, e.status,
           c.name AS client_name,
           COALESCE(SUM(ei.quantity * ei.unit_price), 0) AS total
    FROM estimates e
    JOIN clients c ON e.client_id = c.id
    LEFT JOIN estimate_items ei ON ei.estimate_id = e.id
    GROUP BY e.id
    ORDER BY e.estimate_date DESC
";
$estimates = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Estimates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1>Estimates</h1>
        <table class="table table-striped table-bordered">
            <tr>
                <th>Estimate</th>
                <th>Client</th>
                <th>Date</th>
                <th>Valid Until</th>
                <th>Status</th>
                <th>Total</th>
                <th></th>
                <th></th>
            </tr>
            <?php foreach ($estimates as $est): ?>
            <tr>
                <td><?= htmlspecialchars($est['estimate_number']) ?></td>
                <td><?= htmlspecialchars($est['client_name']) ?></td>
                <td><?= htmlspecialchars($est['estimate_date']) ?></td>
                <td><?= htmlspecialchars($est['expiry_date']) ?></td>
                <td>
                    <?php if ($est['status'] === 'accepted'): ?>
                        <span class="badge bg-success">Accepted</span>
                    <?php elseif ($est['status'] === 'rejected'): ?>
                        <span class="badge bg-danger">Rejected</span>
                    <?php elseif ($est['status'] === 'sent'): ?>
                        <span class="badge bg-info text-dark">Sent</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Draft</span>
                    <?php endif; ?>
                </td>
                <td><?= number_format($est['total'], 2) ?></td>
                <td><a href="view_estimate.php?id=<?= $est['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                <td><a href="estimates.php?delete=<?= $est['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this estimate and all its items?')">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>