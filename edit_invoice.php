<?php
require 'auth.php';
require 'db.php';
$id = $_GET['id'];
$clients = $pdo->query("SELECT * FROM clients ORDER BY name")->fetchAll();
//Handle the update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'];
    $invoice_number = $_POST['invoice_number'];
    $invoice_date = $_POST['invoice_date'];
    $due_date = $_POST['due_date'];
    $terms = $_POST['terms'];
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE invoices SET client_id = ?, invoice_number = ?, invoice_date = ?, due_date = ?, terms = ? WHERE id = ?");
    $stmt->execute([$client_id, $invoice_number, $invoice_date, $due_date, $terms, $id]);
    // Wipe existing items and re-insert fresh ones
    $stmt = $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
    $stmt->execute([$id]);
    $itemStmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price) VALUES (?, ?, ?, ?)");
    foreach ($_POST['description'] as $i => $desc) {
        if (trim($desc) === '') continue;
        $qty = $_POST['quantity'][$i];
        $price = $_POST['unit_price'][$i];
        $itemStmt->execute([$id, $desc, $qty, $price]);
    }
    $pdo->commit();
    header("Location: view_invoice.php?id=$id");
    exit;
}
// Load the existing invoice
$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->execute([$id]);
$invoice = $stmt->fetch();
if (!$invoice) {
    die("Invoice not found.");
}
// Load its existing items
$stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Edit Invoice</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="custom.css" rel="stylesheet">
    </head>
    <body>
        <?php include 'nav.php'; ?>
        <div class="container">
            <h1>Edit Invoice</h1>
            <form method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Client</label>
                        <select name="client_id" class="form-select" required>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>" <?= $client['id'] == $invoice['client_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($client['name']) ?>
                                </option>
                                <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="invoice_number">Invoice Number</label>
                        <input type="text" id="invoice_number" name="invoice_number" class="form-control" value="<?= htmlspecialchars($invoice['invoice_number']) ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="invoice_date">Invoice Date</label>
                        <input type="date" id="invoice_date" name="invoice_date" class="form-control" value="<?= htmlspecialchars($invoice['invoice_date']) ?>" required>
                    </div>
                    <div class="col-md-2">
                    <label class="form-label" for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" class="form-control" value="<?= htmlspecialchars($invoice['due_date']) ?>">
                    </div>
                </div>
                <h3>Items</h3>
                <table id="items-table" class="table table-bordered">
                    <tr>
                        <th scope="col">Description</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Unit Price</th>
                        <th scope="col"></th>
                    </tr>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><input type="text" name="description[]" class="form-control" value="<?= htmlspecialchars($item['description']) ?>"></td>
                            <td><input type="number" name="quantity[]" class="form-control" value="<?= htmlspecialchars($item['quantity']) ?>"></td>
                            <td><input type="number" step="0.01" name="unit_price[]" class="form-control" value="<?= htmlspecialchars($item['unit_price']) ?>"></td>
                            <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                        </tr>
                        <?php endforeach; ?>
                </table>
                <div class="mb-3">
                    <label class="form-label">Terms & Conditions</label>
                    <textarea name="terms" class="form-control" rows="4"><?= htmlspecialchars($invoice['terms'] ?? '') ?></textarea>
                </div>
                <button type="button" class="btn btn-outline-secondary mb-3" onclick="addRow()">+ Add item</button>
                <br>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
            </div>
            <script>
                function addRow() {
                    const table = document.getElementById('items-table');
                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td><input type="text" name="description[]" class="form-control"></td>
                    <td><input type="number" name="quantity[]" class="form-control" value="1"></td>
                    <td><input type="number" step="0.01" name="unit_price[]" class="form-control"></td>
                    <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                    `;
                    table.appendChild(row);
                }
                function removeRow(button) {
                    const row = button.closest('tr');
                    row.remove();
                }
            </script>
            <?php include 'footer.php'; ?>
    </body>
</html>