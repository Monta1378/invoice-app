<?php
require 'db.php';
$clients = $pdo->query("SELECT * FROM clients ORDER By name")->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'];
    $estimate_number = $_POST['estimate_number'];
    $estimate_date = $_POST['estimate_date'];
    $expiry_date = $_POST['expiry_date'];
    $terms = $_POST['terms'];
$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO estimates (client_id, estimate_number, estimate_date, expiry_date, status, terms) VALUES (?, ?, ?, ?, 'draft', ?)");
$stmt->execute([$client_id, $estimate_number, $estimate_date, $expiry_date, $terms]);
$estimate_id = $pdo->lastInsertId();
$itemStmt = $pdo->prepare("INSERT INTO estimate_items (estimate_id, description, quantity, unit_price) VALUES (?, ?, ?, ?)");
foreach ($_POST['description'] as $i =>$desc) {
    if (trim($desc) === '') continue;
    $qty = $_POST['quantity'][$i];
    $price = $_POST['unit_price'][$i];
    $itemStmt->execute([$estimate_id, $desc, $qty, $price]);
}
$pdo->commit();
header("Location: view_estimate.php?id=$estimate_id");
exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Create Estimate</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <?php include 'nav.php'; ?>
        <div class="container">
            <h1>Create Estimate</h1>
            <form method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Client</label>
                        <select name="client_id" class="form-select" required>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                                <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estimate Number</label>
                        <input type="text" name="estimate_number" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Estimate Date</label>
                        <input type="date" name="estimate_date" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Valid Until</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                </div>
                <h3>Items</h3>
                <table id="items-table" class="table table-bordered">
                    <tr>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th></th>
                    </tr>
                    <tr>
                        <td><input type="text" name="description[]" class="form-control"></td>
                        <td><input type="number" name="quantity[]" class="form-control" value="1"></td>
                        <td><input type="number" step="0.01" name="unit_price[]" class="form-control"></td>
                        <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                    </tr>
                </table>
                <div class="mb-3">
                    <label class="form-label">Terms & Conditions</label>
                    <textarea name="terms" class="form-control" rows="4" placeholder="e.g. This estimate is valid for 14 days. A 50% deposit is required to begin work."></textarea>
                </div>
                <button type="button" class="btn btn-outline-secondary mb-3" onclick="addRow()">+ Add Item</button>
                <br>
                <button type="submit" class="btn btn-primary">Save Estimate</button>
            </form>
        </div>
        <script>
            function addRow() {
                const table = document.getElementById('items-table');
                const row= document.createElement('tr');
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
    </body>
</html>
