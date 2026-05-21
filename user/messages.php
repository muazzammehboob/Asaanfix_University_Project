<?php
/**
 * AsaanFix Pakistan - Messages
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$userId = getCurrentUserId();

// Get conversations (unique contacts)
$conversations = dbQuery(
    "SELECT u.id, u.name, u.avatar, u.role,
            (SELECT message FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message,
            (SELECT created_at FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_time,
            (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
     FROM users u
     WHERE u.id IN (
         SELECT DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END 
         FROM messages WHERE sender_id = ? OR receiver_id = ?
     ) ORDER BY last_time DESC",
    [$userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId]
);

// Active chat
$chatWith = (int)($_GET['chat'] ?? 0);
$chatUser = null;
$chatMessages = [];
if ($chatWith) {
    $chatUser = dbQueryOne("SELECT id, name, avatar, role FROM users WHERE id = ?", [$chatWith]);
    if ($chatUser) {
        $chatMessages = dbQuery(
            "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC LIMIT 100",
            [$userId, $chatWith, $chatWith, $userId]
        );
        dbExecute("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0", [$chatWith, $userId]);
    }
}

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $chatUser) {
    requireCSRF();
    $msg = sanitize($_POST['message'] ?? '');
    if (!empty($msg)) {
        dbExecute("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)", [$userId, $chatWith, $msg]);
        createNotification($chatWith, 'New Message', getCurrentUserName() . ' sent you a message.', 'message', '/user/messages.php?chat=' . $userId);
        redirect(APP_URL . '/user/messages.php?chat=' . $chatWith);
    }
}

$pageTitle = 'Messages';
$bodyClass = 'dashboard-page';
$currentPage = 'messages';
$sidebarMenu = [
    'Main' => [
        ['page'=>'dashboard','url'=>APP_URL.'/user/dashboard.php','icon'=>'bi-speedometer2','label'=>'Dashboard'],
        ['page'=>'bookings','url'=>APP_URL.'/user/bookings.php','icon'=>'bi-calendar-check','label'=>'My Bookings'],
        ['page'=>'reviews','url'=>APP_URL.'/user/reviews.php','icon'=>'bi-star','label'=>'My Reviews'],
    ],
    'Account' => [
        ['page'=>'profile','url'=>APP_URL.'/user/profile.php','icon'=>'bi-person','label'=>'Profile'],
        ['page'=>'messages','url'=>APP_URL.'/user/messages.php','icon'=>'bi-chat-dots','label'=>'Messages'],
        ['page'=>'notifications','url'=>APP_URL.'/user/notifications.php','icon'=>'bi-bell','label'=>'Notifications'],
    ],
];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="dashboard-content">
    <button class="btn btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="bi bi-list me-1"></i>Menu</button>
    <h4 class="fw-bold mb-4">Messages</h4>

    <div class="row g-0" style="min-height:500px;">
        <!-- Conversation List -->
        <div class="col-md-4 border-end">
            <div class="p-3 border-bottom"><h6 class="fw-bold mb-0">Conversations</h6></div>
            <?php if (empty($conversations)): ?>
            <div class="p-4 text-center text-muted small">No conversations yet</div>
            <?php else: ?>
            <?php foreach ($conversations as $conv): ?>
            <a href="?chat=<?= $conv['id'] ?>" class="d-flex gap-2 p-3 text-decoration-none border-bottom <?= $chatWith == $conv['id'] ? 'bg-primary bg-opacity-10' : '' ?>" style="transition:var(--transition);">
                <img src="<?= getAvatarUrl($conv['avatar'], $conv['name'] ?? '') ?>" width="40" height="40" class="rounded-circle" style="object-fit:cover;">
                <div class="flex-grow-1 min-width-0">
                    <div class="d-flex justify-content-between">
                        <strong class="small text-dark"><?= sanitize($conv['name']) ?></strong>
                        <small class="text-muted"><?= $conv['last_time'] ? timeAgo($conv['last_time']) : '' ?></small>
                    </div>
                    <p class="text-muted small mb-0 text-truncate"><?= sanitize(substr($conv['last_message'] ?? '', 0, 50)) ?></p>
                </div>
                <?php if ($conv['unread_count'] > 0): ?>
                <span class="badge bg-primary rounded-pill align-self-center"><?= $conv['unread_count'] ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Chat Area -->
        <div class="col-md-8 d-flex flex-column">
            <?php if ($chatUser): ?>
            <div class="p-3 border-bottom d-flex align-items-center gap-2">
                <img src="<?= getAvatarUrl($chatUser['avatar'], $chatUser['name'] ?? '') ?>" width="36" height="36" class="rounded-circle" style="object-fit:cover;">
                <div>
                    <strong class="small"><?= sanitize($chatUser['name']) ?></strong>
                    <small class="text-muted d-block" style="font-size:0.7rem;"><?= ucfirst($chatUser['role']) ?></small>
                </div>
            </div>
            <div class="flex-grow-1 p-3 overflow-auto" style="max-height:400px;" id="chatBox">
                <?php foreach ($chatMessages as $m): $isMine = $m['sender_id'] == $userId; ?>
                <div class="d-flex mb-3 <?= $isMine ? 'justify-content-end' : '' ?>">
                    <div class="p-2 px-3 rounded-3 <?= $isMine ? 'bg-primary text-white' : '' ?>" style="max-width:70%;<?= $isMine ? '' : 'background:var(--surface);' ?>">
                        <p class="mb-0 small"><?= sanitize($m['message']) ?></p>
                        <small class="<?= $isMine ? 'text-white-50' : 'text-muted' ?>" style="font-size:0.7rem;"><?= formatDateTime($m['created_at']) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="p-3 border-top">
                <form method="POST" class="d-flex gap-2">
                    <?= csrfField() ?>
                    <input type="text" class="form-control" name="message" placeholder="Type a message..." required autofocus>
                    <button class="btn btn-primary"><i class="bi bi-send"></i></button>
                </form>
            </div>
            <?php else: ?>
            <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                <div class="text-center text-muted">
                    <i class="bi bi-chat-dots display-3 d-block mb-2"></i>
                    <p>Select a conversation to start chatting</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chatBox');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
