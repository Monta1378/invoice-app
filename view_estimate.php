<?php
require 'db.php';
$id = $_GET['id'];

// Handle status change (sent / accepted/ rejected)
if (isset($_GET['status'])) {
    $newStatus = $_GET['status'];
    $stmt = $pdo->prepare("UPDATE estimates SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);
    header("Location: view_estimate.php?id=$id");
    exit;
}

// Handle conversation to invoice
if (isset($_GET['convert'])) {
    $pdo->beginTransaction();

    // Load the estimate
    $stmt = $pdo->prepare("SELECT * FROM estimates WHERE id = ?");
    $stmt->execute([$id]);
    $est = $stmt->fetch();

    // Create a new invoice from it
    $stmt = $pdo->prepare("INSERT INTO invoices (client_id, invoice_number, invoice_date, due_date, status, terms) VALUES (?, ?, ?, ?, 'unpaid', ?)");
    $stmt->execute([
        $est['client_id'],
        'INV-FROM-' . $est['estimate_number'],
        date('Y-m-d'),
        $est['expiry_date'],
        $est['terms']
    ]);
    $newInvoiceId = $pdo->lastInsertId();

    // Copy the estimate's items into the new invoice
    $stmt = $pdo->prepare("SELECT * FROM estimate_items WHERE estimate_id = ?");
    $stmt->execute([$id]);
    $estItems = $stmt->fetchAll();
    $itemStmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price) VALUES (?, ?, ?, ?)");
    foreach ($estItems as $item) {
        $itemStmt->execute([$newInvoiceId, $item['description'], $item['quantity'], $item['unit_price']]);
    }
    
    // Mark the estimate as accepted
    $stmt = $pdo->prepare("UPDATE estimates SET status = 'accepted' WHERE id = ?");
    $stmt->execute([$id]);
    $pdo->commit();
    header("Location: view_invoice.php?id=$newInvoiceId");
    exit;
    }
    
    //Load the estimate + client
    $stmt = $pdo->prepare("
    SELECT e.*, c.name AS client_name, c.email AS client_email, c.address AS client_address
    FROM estimates e
    JOIN clients c ON e.client_id = c.id
    WHERE e.id = ?
    ");
    $stmt->execute([$id]);
    $estimate = $stmt->fetch();
    if (!$estimate) {
        die("Estimate not found.");
    }
    
    // Load its items
    $stmt = $pdo->prepare("SELECT * FROM estimate_items WHERE estimate_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();
    $total = 0;
    foreach ($items as $item) {
        $total += $item['quantity'] * $item['unit_price'];
    }
    ?>

    <!DOCTYPE html>
    <html>
        <head>
            <title>Estimate <?= htmlspecialchars($estimate['estimate_number']) ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
            <?php include 'nav.php'; ?>
            <div class="container">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1>Estimate <?= htmlspecialchars($estimate['estimate_number']) ?></h1>
                        <?php if ($estimate['status'] !== 'accepted'): ?>
                            <a href="view_estimate.php?id=<?= $id ?>&convert=1" class="btn btn-success" onclick="return confirm('Convert this estimate into an invoice?')">Convert to Invoice</a>
                            <?php endif; ?>
                    </div>
                    <p><strong>Client:</strong> <?= htmlspecialchars($estimate['client_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($estimate['client_email']) ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($estimate['client_address']) ?></p>
                    <p><strong>Estimate Date:</strong> <?= htmlspecialchars($estimate['estimate_date']) ?></p>
                    <p><strong>Valid Until:</strong> <?= htmlspecialchars($estimate['expiry_date']) ?></p>
                    <p><strong>Status</strong>
                    <?php if ($estimate['status'] === 'accepted'): ?>
                        <span class="badge bg-success">Accepted</span>
                        <?php elseif ($estimate['status'] === 'rejected'): ?>
                            <span class="badge bg-danger">Rejected</span>
                            <?php elseif ($estimate['status'] === 'sent'): ?>
                                <span class="badge bg-info text-dark">Sent</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Draft</span>
                                    <?php endif; ?>   
                    </p>
                    <div class="mb-3">
                        <a href="view_estimate.php?id=<?= $id ?>&status=sent" class="btn btn-sm btn-outline-info">Mark Sent</a>
                        <a href="view_estimate.php?id=<?= $id ?>&status=rejected" class="btn btn-sm btn-outline-danger">Mark Rejected</a>
                    </div>
                    <table class="table table-bordered mt-3">
                        <tr>
                            <th>Description</th>
                            <th>Quantity<th>
                            <th>Unit Price</th>
                            <th>Line Total</th>
                        </tr>
                        <?php foreach($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['unit_price'], 2) ?>?></td>
                                <td><?= number_format($item['quantity'] * $item['unit_price'], 2) ?>?></td>
                            </tr>
                            <?php endforeach; ?>
                    </table>
                    <h2 class="text-end">Total: <?= number_format($total, 2) ?></h2>
                    <?php if (!empty($estimate['terms'])): ?>
                        <hr>
                        <h5>Terms &amp; Conditions</h5>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($estimate['terms'])) ?></p>
                        <?php endif; ?>
                </div>
            </div>
        </body>
    </html>