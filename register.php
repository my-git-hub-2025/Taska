<?php
/**
 * Taska – Registration page
 */
require_once __DIR__ . '/includes/auth.php';

$user = current_user();
if ($user) {
    redirect_dashboard($user);
}

$error   = '';
$success = '';

// Check if any user exists to determine if first registration
$isFirstUser = db_count('users.txt') === 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = $_POST['role'] ?? 'parent';

    if (!$name || !$email || !$password || !$confirm) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['teacher', 'parent'], true) && !$isFirstUser) {
        $error = 'Invalid role selected.';
    } else {
        $result = register($name, $email, $password, $isFirstUser ? 'admin' : $role);
        if (is_string($result)) {
            $error = $result;
        } else {
            // Auto-login after registration
            login($email, $password);
            redirect_dashboard($result);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – Taska</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card" style="max-width:480px">
        <div class="text-center mb-4">
            <div class="auth-logo mb-2"><i class="fa-solid fa-baby"></i></div>
            <h2 class="fw-bold text-primary">Create Account</h2>
            <?php if ($isFirstUser): ?>
            <div class="alert alert-info py-2 small mt-2">
                <i class="fa-solid fa-crown me-1"></i>
                You are the first user – you will be assigned the <strong>Admin</strong> role.
            </div>
            <?php else: ?>
            <p class="text-muted small">Join Taska Baby &amp; Kids Care</p>
            <?php endif; ?>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>
            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           placeholder="Your full name" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="you@example.com" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" class="form-control"
                           placeholder="Min. 6 characters" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="confirm_password" class="form-control"
                           placeholder="Repeat password" required>
                </div>
            </div>

            <?php if (!$isFirstUser): ?>
            <div class="mb-4">
                <label class="form-label fw-semibold">Register as</label>
                <select name="role" id="roleSelect" class="form-select">
                    <option value="parent" <?= (($_POST['role'] ?? '') === 'parent') ? 'selected' : '' ?>>
                        <i class="fa-solid fa-users"></i> Parent
                    </option>
                    <option value="teacher" <?= (($_POST['role'] ?? '') === 'teacher') ? 'selected' : '' ?>>
                        <i class="fa-solid fa-chalkboard-teacher"></i> Teacher
                    </option>
                </select>
                <div id="teacherNote" class="form-text text-info" style="display:none;">
                    <i class="fa-solid fa-info-circle"></i>
                    Teachers need to be assigned to a Taska by the Admin before they can post updates.
                </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="role" value="admin">
            <?php endif; ?>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fa-solid fa-user-plus me-2"></i>Create Account
            </button>
        </form>

        <hr>
        <p class="text-center mb-0 small">
            Already have an account?
            <a href="<?= base_url('index.php') ?>" class="text-primary fw-semibold">Sign In</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url('js/app.js') ?>"></script>
</body>
</html>
