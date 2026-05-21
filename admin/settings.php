<?php
/**
 * AsaanFix Pakistan - Admin: Settings
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $fields = ['site_name','site_email','site_phone','site_address','currency','commission_rate','min_booking_hours','max_booking_days_ahead','maintenance_mode','email_notifications'];
    foreach ($fields as $f) {
        $val = sanitize($_POST[$f] ?? '');
        $existing = dbQueryOne("SELECT id FROM settings WHERE setting_key = ?", [$f]);
        if ($existing) { dbExecute("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$val, $f]); }
        else { dbExecute("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$f, $val]); }
    }
    logAdminAction('update_settings', 'settings', null, 'Updated system settings');
    setFlash('success', 'Settings saved.');
    redirect(APP_URL . '/admin/settings.php');
}

// Load settings
$settingsRaw = dbQuery("SELECT setting_key, setting_value FROM settings");
$settings = [];
foreach ($settingsRaw as $s) { $settings[$s['setting_key']] = $s['setting_value']; }

$pendingTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='pending'")['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('pending','confirmed','in_progress')")['c'];
$pageTitle = 'System Settings';
$bodyClass = 'dashboard-page';
$currentPage = 'settings';
$sidebarMenu = [
    'Overview' => [['page'=>'dashboard','url'=>APP_URL.'/admin/dashboard.php','icon'=>'bi-speedometer2','label'=>'Dashboard']],
    'Management' => [
        ['page'=>'users','url'=>APP_URL.'/admin/users.php','icon'=>'bi-people','label'=>'Users'],
        ['page'=>'technicians','url'=>APP_URL.'/admin/technicians.php','icon'=>'bi-person-badge','label'=>'Technicians','badge'=>$pendingTechs?:''],
        ['page'=>'bookings','url'=>APP_URL.'/admin/bookings.php','icon'=>'bi-calendar-check','label'=>'Bookings','badge'=>$activeBookings?:''],
        ['page'=>'services','url'=>APP_URL.'/admin/services.php','icon'=>'bi-grid','label'=>'Services'],
        ['page'=>'categories','url'=>APP_URL.'/admin/categories.php','icon'=>'bi-tag','label'=>'Categories'],
        ['page'=>'reviews','url'=>APP_URL.'/admin/reviews.php','icon'=>'bi-star','label'=>'Reviews'],
    ],
    'System' => [
        ['page'=>'reports','url'=>APP_URL.'/admin/reports.php','icon'=>'bi-graph-up','label'=>'Reports'],
        ['page'=>'logs','url'=>APP_URL.'/admin/logs.php','icon'=>'bi-journal-text','label'=>'Activity Logs'],
        ['page'=>'settings','url'=>APP_URL.'/admin/settings.php','icon'=>'bi-gear','label'=>'Settings'],
    ],
];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="dashboard-content">
    <button class="btn btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="bi bi-list me-1"></i>Menu</button>
    <h4 class="fw-bold mb-4">System Settings</h4>

    <form method="POST">
        <?= csrfField() ?>

        <div class="card-custom p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-building me-2"></i>General</h5>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Site Name</label><input type="text" class="form-control" name="site_name" value="<?= sanitize($settings['site_name'] ?? APP_NAME) ?>"></div>
                <div class="col-md-6"><label class="form-label">Site Email</label><input type="email" class="form-control" name="site_email" value="<?= sanitize($settings['site_email'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" class="form-control" name="site_phone" value="<?= sanitize($settings['site_phone'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Currency</label><input type="text" class="form-control" name="currency" value="<?= sanitize($settings['currency'] ?? 'PKR') ?>"></div>
                <div class="col-12"><label class="form-label">Address</label><input type="text" class="form-control" name="site_address" value="<?= sanitize($settings['site_address'] ?? '') ?>"></div>
            </div>
        </div>

        <div class="card-custom p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-sliders me-2"></i>Booking & Payment</h5>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Commission Rate (%)</label><input type="number" class="form-control" name="commission_rate" value="<?= sanitize($settings['commission_rate'] ?? '15') ?>" min="0" max="50"></div>
                <div class="col-md-4"><label class="form-label">Min Booking Hours</label><input type="number" class="form-control" name="min_booking_hours" value="<?= sanitize($settings['min_booking_hours'] ?? '2') ?>" min="1"></div>
                <div class="col-md-4"><label class="form-label">Max Days Ahead</label><input type="number" class="form-control" name="max_booking_days_ahead" value="<?= sanitize($settings['max_booking_days_ahead'] ?? '30') ?>" min="1"></div>
            </div>
        </div>

        <div class="card-custom p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-shield me-2"></i>System</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="maintenance_mode" value="1" id="maint" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="maint">Maintenance Mode</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="email_notifications" value="1" id="emailNotif" <?= ($settings['email_notifications'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="emailNotif">Email Notifications</label>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-2"></i>Save Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
