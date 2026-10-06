<?php
require 'db.php';
$stmt = $pdo->prepare("SELECT profile_picture FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id'] ?? 0]);
$navAdmin = $stmt->fetch();
?>
<nav class="app-nav">
    <div class="app-nav-inner">
        <a class="app-logo" href="dashboard.php">
            <img src="logo.png" alt="OJO logo" class="nav-logo-img">Invoice App</a>
        <div class="app-nav-links">
            <a class="app-nav-link" href="dashboard.php">Dashboard</a>
            <a class="app-nav-link" href="clients.php">Clients</a>
            <a class="app-nav-link" href="invoices.php">Invoices</a>
            <a class="app-nav-link" href="create_invoice.php">+ New Invoice</a>
            <a class="app-nav-link" href="estimates.php">Estimates</a>
            <a class="app-nav-link" href="create_estimate.php">+ New Estimate</a>
            <a class="app-nav-link" href="reports.php">Reports</a>
            <a class="app-nav-link" href="settings.php">Settings</a>
            <a class="app-nav-link" href="logout.php">
                <?php if (!empty($navAdmin['profile_picture'])): ?>
                    <img src="uploads/<?= htmlspecialchars($navAdmin['profile_picture']) ?>" alt="" class="nav-avatar">
                    <?php endif; ?>
                    Logout
                </a>
        </div>
    </div>
</nav>