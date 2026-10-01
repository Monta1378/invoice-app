<?php
require 'auth.php';
require 'db.php';

//Handle delete
if (isset($_GET['delete'])) {
    checkCsrf();
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: clients.php");
        exit; 
    } catch (PDOException $e){
        $error = "Cannot delete this client - they still have invoices linked to them. Delete their invoices first.";
    }    
}

// Handle form submission (add client)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $address]);
    header("Location: clients.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM clients ORDER BY created_at DESc");
$clients = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Clients</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="custom.css" rel="stylesheet">
    </head>
    <body>
        <?php include 'nav.php'; ?>
        <div class="container mt-4">
            <h1>Add Client</h1>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST" class="row g-2 mb-4">
                    <div class="col-md-3">
                        <label class="form-label" for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Name" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Email">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="Phone">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="address">Address</label>
                        <input type="text" id="address" name="address" class="form-control" placeholder="Address">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Add</button>
                    </div>
                </form>
                <h1>Clients</h1>
                <table class="table table-striped table-bordered">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Address</th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                    </tr>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?= htmlspecialchars($client['name']) ?></td>
                            <td><?= htmlspecialchars($client['email']) ?></td>
                            <td><?= htmlspecialchars($client['phone']) ?></td>
                            <td><?= htmlspecialchars($client['address']) ?></td>
                            <td><a href="edit_client.php?id=<?= $client['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                            <td><a href="clients.php?delete=<?= $client['id'] ?>&csrf=<?= $_SESSION['csrf_token'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this client?')">Delete</a></td>
                        </tr>
                        <?php endforeach; ?>
                </table>
            </div>
            <?php include 'footer.php'; ?>
    </body>
</html>