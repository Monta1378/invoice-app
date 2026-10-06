<?php
session_start();
require 'db.php';

$step = 'question';
$error = '';

// Step 2: verifying the answer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['reset_admin_id']]);
    $admin = $stmt->fetch();

    $submitted = strtolower(trim($_POST['answer']));

    if ($admin && password_verify($submitted, $admin['security_answer'])) {
        $_SESSION['reset_verified'] = true;
        $step = 'reset';
    } else {
        $error = "That answer doesn't match. Try again.";
        $step = 'question';
    }
}

// Step 3: setting the new password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    if (empty($_SESSION['reset_verified']) || empty($_SESSION['reset_admin_id'])) {
        die("Session expired. Please start over.");
    }

    if (strlen($_POST['new_password']) < 8) {
        $error = "Password must be at least 8 characters.";
        $step = 'reset';
    } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
        $error = "Passwords don't match.";
        $step = 'reset';
    } else {
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $_SESSION['reset_admin_id']]);

        unset($_SESSION['reset_admin_id'], $_SESSION['reset_verified']);
        header("Location: login.php?reset=1");
        exit;
    }
}

// Step 1: looking up the username to get their question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $admin = $stmt->fetch();

    if ($admin && !empty($admin['security_question'])) {
        $_SESSION['reset_admin_id'] = $admin['id'];
        $_SESSION['reset_question'] = $admin['security_question'];
        $step = 'answer';
    } else {
        $error = "No account found with a security question set up.";
        $step = 'username';
    }
}

if (!isset($step) || $step === 'question') {
    $step = isset($_SESSION['reset_verified']) ? 'reset' : (isset($_SESSION['reset_question']) ? 'answer' : 'username');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="custom.css" rel="stylesheet">
</head>
<body>
    <div class="container login-wrap">
        <div class="card p-4">
            <h1 class="mb-3">Reset Password</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($step === 'username'): ?>
                <p class="login-intro">Enter your username to begin.</p>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Continue</button>
                </form>

            <?php elseif ($step === 'answer'): ?>
                <p class="login-intro"><?= htmlspecialchars($_SESSION['reset_question']) ?></p>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label" for="answer">Your Answer</label>
                        <input type="text" id="answer" name="answer" class="form-control" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Verify</button>
                </form>

            <?php elseif ($step === 'reset'): ?>
                <p class="login-intro">Choose a new password.</p>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label" for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Set New Password</button>
                </form>
            <?php endif; ?>

            <p class="mt-3"><a href="login.php">&larr; Back to login</a></p>
        </div>
    </div>
</body>
</html>