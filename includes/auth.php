<?php
/**
 * Taska - Authentication & session helpers
 */

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Return the currently logged-in user array, or null.
 */
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    return db_find('users.txt', 'id', $_SESSION['user_id']);
}

/**
 * Check if user is logged in; redirect to login if not.
 */
function require_login(): array {
    $user = current_user();
    if (!$user) {
        header('Location: ' . base_url('index.php'));
        exit;
    }
    return $user;
}

/**
 * Require a specific role. Redirects to their dashboard if wrong role.
 */
function require_role(string $role): array {
    $user = require_login();
    if ($user['role'] !== $role) {
        $destinations = [
            'admin'   => 'admin/index.php',
            'teacher' => 'teacher/index.php',
            'parent'  => 'parent/index.php',
        ];
        $dest = $destinations[$user['role']] ?? 'index.php';
        header('Location: ' . base_url($dest));
        exit;
    }
    return $user;
}

/**
 * Log the user in. Returns the user array or null on failure.
 */
function login(string $email, string $password): ?array {
    $user = db_find('users.txt', 'email', strtolower(trim($email)));
    if (!$user) return null;
    if (!password_verify($password, $user['password'])) return null;
    $_SESSION['user_id'] = $user['id'];
    return $user;
}

/**
 * Log the current user out.
 */
function logout(): void {
    $_SESSION = [];
    session_destroy();
}

/**
 * Register a new user. Returns the user array or an error string.
 */
function register(string $name, string $email, string $password, string $role = ''): array|string {
    $email = strtolower(trim($email));
    if (db_find('users.txt', 'email', $email)) {
        return 'Email already registered.';
    }
    // First user becomes admin
    $isFirst = db_count('users.txt') === 0;
    $assignedRole = $isFirst ? 'admin' : ($role ?: 'parent');
    $user = [
        'name'     => trim($name),
        'email'    => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role'     => $assignedRole,
        'avatar'   => '',
    ];
    return db_insert('users.txt', $user);
}

/**
 * Build an absolute URL path for redirect.
 */
function base_url(string $path = ''): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
              (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base   = rtrim(dirname(dirname(__FILE__)), '/\\');
    // Determine the web root relative path
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
    $baseWeb = str_replace($docRoot, '', $base);
    $baseWeb = str_replace('\\', '/', $baseWeb);
    return $scheme . '://' . $host . '/' . ltrim($baseWeb . '/' . ltrim($path, '/'), '/');
}

/**
 * Dashboard redirect after login.
 */
function redirect_dashboard(array $user): void {
    $destinations = [
        'admin'   => 'admin/index.php',
        'teacher' => 'teacher/index.php',
        'parent'  => 'parent/index.php',
    ];
    $dest = $destinations[$user['role']] ?? 'index.php';
    header('Location: ' . base_url($dest));
    exit;
}
