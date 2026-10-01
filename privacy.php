<?php
require 'db.php';
$settings = $pdo->query("SELECT * FROM business_settings WHERE id = 1")->fetch();
$businessName = !empty($settings['business_name']) ? htmlspecialchars($settings['business_name']) : '[Business Name]';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Privacy Policy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container" style="max-width: 800px;">
        <h1>Privacy Policy</h1>
        <p class="text-muted">Last updated: <?= date('F Y') ?></p>

        <p>This Privacy Policy explains how <?= $businessName ?> ("we", "us") collects, uses and protects
        information when you use this invoicing application ("the App").</p>

        <h4>Information We Collect</h4>
        <p>To issue and manage invoices, we store:</p>
        <ul>
            <li>Client details you enter: name, email, phone number and address</li>
            <li>Invoice and estimate details: line items, amounts, dates and payment records</li>
            <li>An administrator login (username and a securely hashed password)</li>
        </ul>
        <p>We do not collect analytics, tracking data, or any information beyond what's needed to
        create and manage invoices.</p>

        <h4>How We Use It</h4>
        <p>Client information is used solely to generate invoices and estimates, record payments, and
        communicate about them. It is not sold, rented, or shared with third parties for marketing
        purposes.</p>

        <h4>Shared Invoices</h4>
        <p>If an invoice is shared via a private link, anyone with that link can view the invoice details
        shown on it. Links can be deactivated at any time from within the App.</p>

        <h4>Third-Party Services</h4>
        <p>This App loads the Bootstrap and Chart.js libraries from a public content delivery network
        (CDN) to render its interface and charts. These are static files with no tracking or analytics
        attached.</p>

        <h4>Data Retention and Deletion</h4>
        <p>Client and invoice records are kept until manually deleted within the App. To request deletion
        of your information, contact us using the details below.</p>

        <h4>Contact</h4>
        <p>
            <?= $businessName ?><br>
            <?= !empty($settings['email']) ? htmlspecialchars($settings['email']) : '[contact email]' ?>
        </p>

        <div class="alert alert-secondary mt-4">
            <strong>Note:</strong> This page is provided as a template for a demo/learning project and is
            not legal advice. A live business handling real customer data should have this reviewed by a
            qualified professional for their jurisdiction.
        </div>
    </div>
</body>
</html>
