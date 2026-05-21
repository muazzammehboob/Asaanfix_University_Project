<?php
/**
 * AsaanFix Pakistan - Public Technician Profile
 */
require_once __DIR__ . '/includes/functions.php';
$techId = (int)($_GET['id'] ?? 0);
if (!$techId) redirect(APP_URL . '/technicians.php');

$tech = dbQueryOne(
    "SELECT t.*, u.name, u.avatar, u.city, u.created_at as member_since
     FROM technicians t JOIN users u ON t.user_id = u.id
     WHERE t.id = ? AND t.status = 'approved' AND u.is_active = 1", [$techId]
);
if (!$tech) { setFlash('warning', 'Technician not found.'); redirect(APP_URL . '/technicians.php'); }

$services = dbQuery(
    "SELECT s.*, c.name as cat_name, ts.custom_price FROM technician_services ts
     JOIN services s ON ts.service_id = s.id JOIN categories c ON s.category_id = c.id
     WHERE ts.technician_id = ?", [$techId]
);

$reviews = dbQuery(
    "SELECT r.*, u.name as user_name, u.avatar as user_avatar FROM reviews r
     JOIN users u ON r.user_id = u.id WHERE r.technician_id = ? AND r.is_visible = 1
     ORDER BY r.created_at DESC LIMIT 10", [$techId]
);

$pageTitle = $tech['name'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container"><h1><i class="bi bi-person-badge me-2"></i><?= sanitize($tech['name']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li><li class="breadcrumb-item"><a href="<?= APP_URL ?>/technicians.php">Technicians</a></li><li class="breadcrumb-item active"><?= sanitize($tech['name']) ?></li></ol></nav></div>
</section>

<section class="section py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4" data-aos="fade-right">
                <div class="card-custom p-4 text-center">
                    <img src="<?= getAvatarUrl($tech['avatar'], $tech['name'] ?? '') ?>" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover;">
                    <h4 class="fw-bold mb-1"><?= sanitize($tech['name']) ?></h4>
                    <p class="text-muted mb-2"><i class="bi bi-geo-alt me-1"></i><?= sanitize($tech['city']) ?></p>
                    <?= getStarRating($tech['avg_rating']) ?>
                    <div class="d-flex justify-content-center gap-4 my-3 small">
                        <div><strong class="d-block text-primary fs-5"><?= $tech['total_jobs'] ?></strong>Jobs</div>
                        <div><strong class="d-block text-primary fs-5"><?= $tech['experience_years'] ?></strong>Yrs Exp</div>
                        <div><strong class="d-block text-primary fs-5"><?= $tech['total_reviews'] ?></strong>Reviews</div>
                    </div>
                    <div class="mb-3"><span class="badge bg-<?= $tech['is_available']?'success':'secondary' ?> px-3 py-2"><?= $tech['is_available']?'✅ Available':'Unavailable' ?></span></div>
                    <p class="small text-muted"><i class="bi bi-clock me-1"></i><?= formatTime($tech['available_from']) ?> - <?= formatTime($tech['available_to']) ?></p>
                    <hr>
                    <div class="fw-bold text-primary fs-5 mb-2"><?= formatPrice($tech['hourly_rate']) ?>/hr</div>
                    <p class="small text-muted mb-0">Member since <?= formatDate($tech['member_since']) ?></p>
                </div>
            </div>
            <div class="col-lg-8" data-aos="fade-left">
                <?php if ($tech['bio']): ?>
                <div class="card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-2">About</h5>
                    <p class="text-secondary mb-0"><?= nl2br(sanitize($tech['bio'])) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($tech['skills']): ?>
                <div class="card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3">Skills</h5>
                    <?php foreach(explode(',', $tech['skills']) as $sk): ?>
                    <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-1 px-3 py-2"><?= sanitize(trim($sk)) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if ($services): ?>
                <div class="card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3">Services Offered</h5>
                    <?php foreach ($services as $s): ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div><strong class="small"><?= sanitize($s['name']) ?></strong><br><small class="text-muted"><?= sanitize($s['cat_name']) ?></small></div>
                        <div class="text-end">
                            <span class="fw-bold text-primary"><?= formatPrice($s['custom_price'] ?? $s['base_price']) ?></span>
                            <br><a href="<?= APP_URL ?>/book-service.php?service=<?= $s['id'] ?>&tech=<?= $techId ?>" class="btn btn-sm btn-outline-primary mt-1">Book</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="card-custom p-4">
                    <h5 class="fw-bold mb-3">Reviews (<?= count($reviews) ?>)</h5>
                    <?php if (empty($reviews)): ?><p class="text-muted">No reviews yet.</p>
                    <?php else: foreach ($reviews as $r): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex gap-2 align-items-center mb-1">
                            <img src="<?= getAvatarUrl($r['user_avatar'], $r['user_name'] ?? '') ?>" width="32" height="32" class="rounded-circle" style="object-fit:cover;">
                            <strong class="small"><?= sanitize($r['user_name']) ?></strong>
                            <span class="text-muted small ms-auto"><?= formatDate($r['created_at']) ?></span>
                        </div>
                        <?= getStarRating($r['rating'], false) ?>
                        <p class="small mt-1 mb-0"><?= sanitize($r['comment']) ?></p>
                        <?php if ($r['reply']): ?><div class="mt-2 p-2 rounded-2 small" style="background:var(--surface);"><strong>Reply:</strong> <?= sanitize($r['reply']) ?></div><?php endif; ?>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
