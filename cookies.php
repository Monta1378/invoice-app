<?php
require 'db.php';
$settings = $pdo->query("SELECT * FROM business_settings WHERE id = 1")->fetch();
$businessName = !empty($settings['business_name']) ? htmlspecialchars($settings['business_name']) : '[Business Name]';
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Cookie Policy</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <?php include 'nav.php'; ?>
        <div class="container" style="max-width: 800px;">
            <h1>Cookie Policy</h1>
            <p class="text-muted">Last updated: <?= date('F Y') ?></p>
            <p>This App uses a single cookie, described below. It does not use advertising, tracking, or analytics cookies of any kind.</p>
            <table class="table table-bordered mt-3">
                <tr>
                    <th scope="col">Cookie</th>
                    <th scope="col">Purpose</th>
                    <th scope="col">Duration</th>
                </tr>
                <tr>
                    <td>PHPSESSID</td>
                    <td>Keeps an administrator logged in between page loads. Strictly necessary for the App
                    to function &mdash; without it, you could not stay signed in.</td>
                    <td>Deleted when you log out or close your browser</td>
                </tr>
            </table>
            <p>Because this cookie is strictly necessary for the App to work and does not track you across
            other sites, it does not require a consent banner under most privacy regulations. You can still
            block or delete it through your browser's settings, though doing so will prevent you from staying
            logged in.</p>
            <h4>Contact</h4>
            <p>
                <?= $businessName ?><br>
                <?= !empty($settings['email']) ? htmlspecialchars($settings['email']) : '[contact email]' ?>
            </p>
            <div class="alert alert-secondary mt-4">
                <strong>Note:</strong> This page is provided as a template for a demo/learning project and is
                not legal advice.
            </div>
        </div>
    </body>
</html>
