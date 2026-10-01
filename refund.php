<?php
require 'db.php';
$settings = $pdo->query("SELECT * FROM business_settings WHERE id = 1")->fetch();
$businessName = !empty($settings['business_name']) ? htmlspecialchars($settings['business_name']) : '[Business Name]';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Refund Policy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container" style="max-width: 800px;">
        <h1>Refund Policy</h1>
        <p class="text-muted">Last updated: <?= date('F Y') ?></p>

        <p>This policy covers refunds for payments made against invoices issued by <?= $businessName ?>.
        It does not apply to the invoicing software itself, which is not sold or licensed to clients.</p>

        <h4>Requesting a Refund</h4>
        <p>If you believe a payment was made in error, or a service or product on an invoice was not
        delivered as agreed, contact us using the details below within a reasonable time of the payment
        being made.</p>

        <h4>Processing</h4>
        <p>Approved refunds will be recorded against the relevant invoice and processed using the original
        payment method where possible. Processing times vary by payment method and are not guaranteed by
        this App.</p>

        <h4>Non-Refundable Items</h4>
        <p>Work or services already completed and accepted are generally not eligible for a refund, unless
        otherwise agreed in writing at the time of invoicing.</p>

        <h4>Contact</h4>
        <p>
            <?= $businessName ?><br>
            <?= !empty($settings['email']) ? htmlspecialchars($settings['email']) : '[contact email]' ?>
        </p>

        <div class="alert alert-secondary mt-4">
            <strong>Note:</strong> This page is provided as a template for a demo/learning project and is
            not legal advice. A live business should adapt and have this reviewed by a qualified
            professional for their jurisdiction.
        </div>
    </div>
</body>
</html>
