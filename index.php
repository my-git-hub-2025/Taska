<?php
/**
 * Taska – Login page (index.php)
 */
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
$user = current_user();
if ($user) {
    redirect_dashboard($user);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $loggedIn = login($email, $password);
        if ($loggedIn) {
            redirect_dashboard($loggedIn);
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Taska</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="auth-logo mb-2"><i class="fa-solid fa-baby"></i></div>
            <h2 class="fw-bold text-primary">Taska</h2>
            <p class="text-muted small">Baby &amp; Kids Care System</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST" action="index.php" novalidate>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="you@example.com" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" class="form-control"
                           placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
            </button>
        </form>

        <hr>
        <p class="text-center mb-0 small">
            Don't have an account?
            <a href="<?= base_url('register.php') ?>" class="text-primary fw-semibold">Register</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
