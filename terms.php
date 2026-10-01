<?php
require 'db.php';
$settings = $pdo->query("SELECT * FROM business_settings WHERE id = 1")->fetch();
$businessName = !empty($settings['business_name']) ? htmlspecialchars($settings['business_name']) : '[Business Name]';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Terms of Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container" style="max-width: 800px;">
        <h1>Terms of Service</h1>
        <p class="text-muted">Last updated: <?= date('F Y') ?></p>

        <p>By using this invoicing application ("the App"), operated by <?= $businessName ?>, you agree to
        the following terms.</p>

        <h4>Use of the App</h4>
        <p>The App is provided for creating, managing and sharing invoices and estimates. Access is
        restricted to authorized administrators. You are responsible for keeping your login credentials
        confidential.</p>

        <h4>Accuracy of Information</h4>
        <p>Invoices and estimates are generated from information entered by the administrator. We make no
        guarantee as to the accuracy of amounts, dates or client details beyond what was entered.</p>

        <h4>Shared Invoice Links</h4>
        <p>Invoices shared via a private link are accessible to anyone who has that link. The administrator
        is responsible for sharing links only with intended recipients and deactivating them when no
        longer needed.</p>

        <h4>Availability</h4>
        <p>The App is provided "as is" without warranty of uninterrupted availability. We are not liable
        for any loss arising from downtime, data loss, or errors in generated documents.</p>

        <h4>Changes to These Terms</h4>
        <p>These terms may be updated from time to time. Continued use of the App after changes constitutes
        acceptance of the updated terms.</p>

        <h4>Contact</h4>
        <p>
            <?= $businessName ?><br>
            <?= !empty($settings['email']) ? htmlspecialchars($settings['email']) : '[contact email]' ?>
        </p>

        <div class="alert alert-secondary mt-4">
            <strong>Note:</strong> This page is provided as a template for a demo/learning project and is
            not legal advice. A live business should have this reviewed by a qualified professional for
            their jurisdiction.
        </div>
    </div>
</body>
</html>
