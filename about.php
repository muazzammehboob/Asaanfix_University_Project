<?php
/**
 * AsaanFix Pakistan - About Page
 */
$pageTitle = 'About Us';
require_once __DIR__ . '/includes/header.php';

$totalTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='approved'")['c'] ?? 0;
$totalBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings")['c'] ?? 0;
?>

<section class="page-header">
    <div class="container">
        <h1><i class="bi bi-info-circle me-2"></i>About AsaanFix Pakistan</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
            <li class="breadcrumb-item active">About</li>
        </ol></nav>
    </div>
</section>

<!-- Mission -->
<section class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <span class="section-label">Our Story</span>
                <h2 class="mb-3">Connecting Pakistan with Trusted Home Services</h2>
                <p class="text-secondary">AsaanFix Pakistan was founded with a simple mission — to bridge the gap between skilled technicians and the people who need them. In Pakistan, finding a reliable electrician, plumber, or mobile repair expert has always been a challenge.</p>
                <p class="text-secondary">We built this platform to solve that problem. Every technician on AsaanFix is verified, skilled, and committed to delivering quality work. Whether you need an AC repair in Karachi or a laptop fix in Islamabad, we've got you covered.</p>
                <div class="d-flex gap-4 mt-4">
                    <div><h3 class="text-primary fw-bold mb-0" data-counter="<?= max($totalTechs, 50) ?>" data-suffix="+">50+</h3><small class="text-muted">Verified Experts</small></div>
                    <div><h3 class="text-primary fw-bold mb-0" data-counter="<?= max($totalBookings, 500) ?>" data-suffix="+">500+</h3><small class="text-muted">Jobs Completed</small></div>
                    <div><h3 class="text-primary fw-bold mb-0" data-counter="15">15</h3><small class="text-muted">Cities Covered</small></div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="p-5 rounded-4 text-center" style="background:linear-gradient(135deg,rgba(37,99,235,0.06),rgba(124,58,237,0.06));">
                    <i class="bi bi-tools" style="font-size:8rem;color:var(--primary);opacity:0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values -->
<section class="section" style="background:var(--card);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Our Values</span>
            <h2>What Drives Us</h2>
        </div>
        <div class="row g-4">
            <?php
            $values = [
                ['bi-shield-check', 'Trust & Safety', 'Every professional is background-verified. Your safety is our top priority.'],
                ['bi-lightning-charge', 'Speed & Reliability', 'Fast response times with professionals you can count on.'],
                ['bi-hand-thumbs-up', 'Quality Work', '100% satisfaction guaranteed or we make it right.'],
                ['bi-cash-stack', 'Fair Pricing', 'Transparent pricing with no hidden charges. Ever.'],
            ];
            foreach ($values as $i => $v): ?>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $i*100 ?>">
                <div class="text-center p-4 rounded-3 hover-lift h-100" style="background:var(--surface);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:64px;height:64px;background:rgba(var(--primary-rgb),0.1);">
                        <i class="bi <?= $v[0] ?> fs-3 text-primary"></i>
                    </div>
                    <h5 class="fw-bold"><?= $v[1] ?></h5>
                    <p class="text-muted small mb-0"><?= $v[2] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container text-center" data-aos="zoom-in">
        <h2 class="mb-3">Join the AsaanFix Family</h2>
        <p class="text-white-50 mb-4">Whether you need a service or want to offer your skills</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= APP_URL ?>/services.php" class="btn btn-accent btn-lg px-5">Browse Services</a>
            <a href="<?= APP_URL ?>/auth/register.php" class="btn btn-outline-light btn-lg px-5">Become a Technician</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
