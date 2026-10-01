<?php
require 'auth.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE business_settings SET business_name = ?, address = ?, phone = ?, email = ? WHERE id = 1");
    $stmt->execute([$_POST['business_name'], $_POST['address'], $_POST['phone'], $_POST['email']]);
    header("Location: settings.php?saved=1");
    exit;
}

$settings = $pdo->query("SELECT * FROM business_settings WHERE id = 1")->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Business Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="custom.css" rel="stylesheet">
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h1>Business Settings</h1>
        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success">Saved.</div>
        <?php endif; ?>
        <form method="POST" class="row g-3" style="max-width: 500px;">
            <div class="col-12">
                <label class="form-label" for="business_name">Business Name</label>
                <input type="text" id="business_name" name="business_name" class="form-control" value="<?= htmlspecialchars($settings['business_name'] ?? '') ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="address">Address</label>
                <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($settings['address'] ?? '') ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="phone">Phone</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($settings['phone'] ?? '') ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($settings['email'] ?? '') ?>">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>