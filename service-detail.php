<?php
/**
 * AsaanFix Pakistan - Service Detail Page
 */
require_once __DIR__ . '/includes/functions.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) { redirect(APP_URL . '/services.php'); }

$service = dbQueryOne(
    "SELECT s.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon 
     FROM services s JOIN categories c ON s.category_id = c.id 
     WHERE s.slug = ? AND s.is_active = 1", [$slug]
);
if (!$service) { setFlash('warning', 'Service not found.'); redirect(APP_URL . '/services.php'); }

// Technicians offering this service
$techs = dbQuery(
    "SELECT t.*, u.name, u.avatar, u.city, ts.custom_price 
     FROM technician_services ts 
     JOIN technicians t ON ts.technician_id = t.id 
     JOIN users u ON t.user_id = u.id 
     WHERE ts.service_id = ? AND t.status = 'approved' AND u.is_active = 1 
     ORDER BY t.avg_rating DESC", [$service['id']]
);

// Related services
$related = dbQuery(
    "SELECT s.*, c.icon as category_icon FROM services s JOIN categories c ON s.category_id = c.id 
     WHERE s.category_id = ? AND s.id != ? AND s.is_active = 1 LIMIT 3",
    [$service['category_id'], $service['id']]
);

$pageTitle = $service['name'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/services.php">Services</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/services.php?cat=<?= $service['category_slug'] ?>"><?= sanitize($service['category_name']) ?></a></li>
                <li class="breadcrumb-item active"><?= sanitize($service['name']) ?></li>
            </ol>
        </nav>
        <h1><?= sanitize($service['name']) ?></h1>
    </div>
</section>

<section class="section py-4">
    <div class="container">
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8" data-aos="fade-right">
                <div class="card-custom p-4 mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width:56px;height:56px;background:rgba(var(--primary-rgb),0.1);">
                            <i class="bi <?= sanitize($service['category_icon']) ?> fs-4 text-primary"></i>
                        </div>
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary mb-1"><?= sanitize($service['category_name']) ?></span>
                            <h4 class="mb-0"><?= sanitize($service['name']) ?></h4>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 text-center" style="background:var(--surface);">
                                <i class="bi bi-cash-stack text-primary fs-4 d-block mb-1"></i>
                                <small class="text-muted d-block">Starting from</small>
                                <strong class="text-primary fs-5"><?= formatPrice($service['base_price']) ?></strong>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 text-center" style="background:var(--surface);">
                                <i class="bi bi-clock text-info fs-4 d-block mb-1"></i>
                                <small class="text-muted d-block">Duration</small>
                                <strong><?= $service['duration_minutes'] ?> minutes</strong>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 text-center" style="background:var(--surface);">
                                <i class="bi bi-people text-success fs-4 d-block mb-1"></i>
                                <small class="text-muted d-block">Available</small>
                                <strong><?= count($techs) ?> Technicians</strong>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3">About This Service</h5>
                    <p class="text-secondary"><?= nl2br(sanitize($service['description'] ?? 'No description available.')) ?></p>
                </div>

                <!-- Available Technicians -->
                <h5 class="fw-bold mb-3"><i class="bi bi-people me-2"></i>Available Technicians (<?= count($techs) ?>)</h5>
                <?php if (empty($techs)): ?>
                <div class="card-custom p-4 text-center">
                    <i class="bi bi-person-slash display-4 text-muted"></i>
                    <p class="mt-2 text-muted">No technicians currently available for this service.</p>
                </div>
                <?php else: ?>
                <?php foreach ($techs as $tech): ?>
                <div class="card-custom p-3 mb-3 hover-lift">
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= getAvatarUrl($tech['avatar'], $tech['name'] ?? '') ?>" alt="" class="rounded-circle" width="56" height="56" style="object-fit:cover;">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold"><?= sanitize($tech['name']) ?></h6>
                            <div class="d-flex flex-wrap gap-2 align-items-center small">
                                <?= getStarRating($tech['avg_rating']) ?>
                                <span class="text-muted">• <?= $tech['total_jobs'] ?> jobs</span>
                                <span class="text-muted">• <i class="bi bi-geo-alt"></i> <?= sanitize($tech['city']) ?></span>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-primary"><?= formatPrice($tech['custom_price'] ?? $service['base_price']) ?></div>
                            <a href="<?= APP_URL ?>/book-service.php?service=<?= $service['id'] ?>&tech=<?= $tech['id'] ?>" class="btn btn-sm btn-primary mt-1">Book Now</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4" data-aos="fade-left">
                <!-- Booking CTA -->
                <div class="card-custom p-4 mb-4" style="border-color:var(--primary);border-width:2px;">
                    <h5 class="fw-bold text-center mb-3">Book This Service</h5>
                    <p class="text-center text-muted small mb-3">Choose a technician and schedule your booking</p>
                    <div class="text-center mb-3">
                        <span class="fs-3 fw-bold text-primary"><?= formatPrice($service['base_price']) ?></span>
                        <span class="text-muted"> starting</span>
                    </div>
                    <?php if (isLoggedIn()): ?>
                    <a href="<?= APP_URL ?>/book-service.php?service=<?= $service['id'] ?>" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-calendar-check me-2"></i>Book Now
                    </a>
                    <?php else: ?>
                    <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login to Book
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Related Services -->
                <?php if ($related): ?>
                <div class="card-custom p-4">
                    <h6 class="fw-bold mb-3">Related Services</h6>
                    <?php foreach ($related as $r): ?>
                    <a href="<?= APP_URL ?>/service-detail.php?slug=<?= $r['slug'] ?>" class="d-flex align-items-center gap-3 text-decoration-none mb-3">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-2" style="width:40px;height:40px;background:rgba(var(--primary-rgb),0.08);">
                            <i class="bi <?= $r['category_icon'] ?> text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small fw-semibold text-dark"><?= sanitize($r['name']) ?></h6>
                            <small class="text-primary fw-bold"><?= formatPrice($r['base_price']) ?></small>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
