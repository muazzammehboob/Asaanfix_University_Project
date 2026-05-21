<?php
/**
 * AsaanFix Pakistan - Notifications API
 */
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);

$userId = getCurrentUserId();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'list') {
    $notifications = dbQuery("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10", [$userId]);
    jsonResponse(['success' => true, 'notifications' => $notifications]);
}

if ($action === 'mark_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        dbExecute("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$id, $userId]);
    } else {
        dbExecute("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$userId]);
    }
    jsonResponse(['success' => true]);
}

if ($action === 'count') {
    $count = getUnreadNotificationCount($userId);
    jsonResponse(['success' => true, 'count' => $count]);
}

jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
