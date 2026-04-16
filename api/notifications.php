<?php
/**
 * Taska API – Notifications (mark as read, count)
 */
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorised']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'mark_read') {
    $id    = $_POST['id'] ?? '';
    $notif = db_find('notifications.txt', 'id', $id);
    if ($notif && $notif['user_id'] === $user['id']) {
        db_update('notifications.txt', $id, ['is_read' => true]);
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

echo json_encode(['error' => 'Unknown action']);
