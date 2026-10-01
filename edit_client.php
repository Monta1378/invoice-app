<?php
require 'auth.php';
require 'db.php';
$id = $_GET['id'];

//Handle the update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $stmt = $pdo->prepare("UPDATE clients SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->execute([$name, $email, $phone, $address, $id]);
    header("Location: clients.php");
    exit;
}
//Load the existing client so we can pre-fill the form
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch();
if (!$client) {
    die("Client not found.");
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Edit Client</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="custom.css" rel="stylesheet">
    </head>
    <body>
        <?php include 'nav.php'; ?>
        <div class="container">
            <h1>Edit Client</h1>
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($client['name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($client['email']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="phone">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($client['phone']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="address">Address</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($client['address']) ?>">
                </div>
                <div class="col-12">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="clients.php" class="btn btn-outline-secondary">← Back to Clients</a>
                </div>
            </form>
        </div>
        <?php include 'footer.php'; ?>
    </body>
</html>