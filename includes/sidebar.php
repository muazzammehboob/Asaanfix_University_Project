<?php
/**
 * AsaanFix Pakistan - Dashboard Sidebar Template
 * Reusable sidebar for user/technician/admin dashboards
 * Required: $sidebarMenu array, $currentPage string
 */
?>
<aside class="dashboard-sidebar" id="dashboardSidebar">
    <div class="px-3 pb-3 mb-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <img src="<?= getAvatarUrl($_SESSION['user_avatar'] ?? null) ?>" 
                 class="rounded-circle" width="44" height="44" style="object-fit:cover;" alt="">
            <div>
                <h6 class="mb-0 fw-bold" style="font-size:0.9rem;"><?= sanitize($_SESSION['user_name'] ?? 'User') ?></h6>
                <small class="text-muted text-uppercase" style="font-size:0.7rem;"><?= getCurrentUserRole() ?></small>
            </div>
        </div>
    </div>
    <ul class="sidebar-nav">
        <?php foreach ($sidebarMenu as $section => $items): ?>
            <?php if (!is_numeric($section)): ?>
            <li class="sidebar-section-title"><?= $section ?></li>
            <?php endif; ?>
            <?php foreach ($items as $item): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage ?? '') === $item['page'] ? 'active' : '' ?>" href="<?= $item['url'] ?>">
                    <i class="bi <?= $item['icon'] ?>"></i>
                    <span><?= $item['label'] ?></span>
                    <?php if (!empty($item['badge'])): ?>
                    <span class="badge bg-danger ms-auto"><?= $item['badge'] ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </ul>
</aside>
