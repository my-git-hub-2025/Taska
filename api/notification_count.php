<?php
/**
 * Taska API – Notification count (for polling)
 */
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$user = current_user();
if (!$user) {
    echo json_encode(['count' => 0]);
    exit;
}

$unread = array_filter(
    db_find_all('notifications.txt', 'user_id', $user['id']),
    fn($n) => !$n['is_read']
);

echo json_encode(['count' => count($unread)]);
