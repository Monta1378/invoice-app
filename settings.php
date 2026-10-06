<?php
require 'auth.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form'] === 'business') {
    $stmt = $pdo->prepare("UPDATE business_settings SET business_name = ?, address = ?, phone = ?, email = ? WHERE id = 1");
    $stmt->execute([$_POST['business_name'], $_POST['address'], $_POST['phone'], $_POST['email']]);
    header("Location: settings.php?saved=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form'] === 'security') {
    $question = $_POST['security_question'];
    $answerHash = password_hash(strtolower(trim($_POST['security_answer'])), PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE admins SET security_question = ?, security_answer = ? WHERE id = ?");
    $stmt->execute([$question, $answerHash, $_SESSION['admin_id']]);
    header("Location: settings.php?saved=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form'] === 'profile_picture') {
    if (!empty($_FILES['profile_picture']['name'])) {
        $file = $_FILES['profile_picture'];

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $actualType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($actualType, $allowedTypes)) {
            $error = "Please upload a JPG, PNG, or WEBP image.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = "Image must be under 2MB.";
        } else {
            $ext = $actualType === 'image/png' ? 'png' : ($actualType === 'image/webp' ? 'webp' : 'jpg');
            $filename = 'admin_' . $_SESSION['admin_id'] . '_' . time() . '.' . $ext;

            move_uploaded_file($file['tmp_name'], 'uploads/' . $filename);

            $stmt = $pdo->prepare("UPDATE admins SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$filename, $_SESSION['admin_id']]);
            header("Location: settings.php?saved=1");
            exit;
        }
    }
}

$settings = $pdo->query("SELECT * FROM business_settings WHERE id = 1")->fetch();
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id=?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Business Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            <input type="hidden" name="form" value="business">
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

        <div class="card p-4 mt-4" style="max-width: 500px;">
            <h3>Security Question</h3>
            <p class="text-muted" style="font-size: 0.9rem;">
                Used to verify your identity if you ever forget your password.
                <?php if (!empty($admin['security_question'])): ?>
                    Currently set to: <strong><?= htmlspecialchars($admin['security_question']) ?></strong>
                <?php endif; ?>
            </p>
            <form method="POST" class="row g-3">
                <input type="hidden" name="form" value="security">
                <div class="col-12">
                    <label class="form-label" for="security_question">Question</label>
                    <input type="text" id="security_question" name="security_question" class="form-control"
                           placeholder="e.g. What was your first pet's name?" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="security_answer">Answer</label>
                    <input type="text" id="security_answer" name="security_answer" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save Security Question</button>
                </div>
            </form>
        </div>

        <div class="card p-4 mt-4" style="max-width: 500px;">
            <h3>Profile Picture</h3>
            <?php if (!empty($admin['profile_picture'])): ?>
                <img src="uploads/<?= htmlspecialchars($admin['profile_picture']) ?>" alt="Your profile picture" class="profile-pic-preview mb-3">
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="form" value="profile_picture">
                <div class="mb-3">
                    <label class="form-label" for="profile_picture">Upload New Picture</label>
                    <input type="file" id="profile_picture" name="profile_picture" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>