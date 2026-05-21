<?php
/**
 * AsaanFix Pakistan - Homepage
 */
$pageTitle = 'Home - Book Trusted Technicians';
$pageDescription = 'AsaanFix Pakistan - Book verified technicians for mobile repair, electrician, plumbing, AC services across Pakistan. Fast, reliable, affordable.';
require_once __DIR__ . '/includes/header.php';

// Fetch active categories
$categories = dbQuery("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 8");

// Fetch top-rated technicians
$topTechs = dbQuery(
    "SELECT t.*, u.name, u.avatar, u.city 
     FROM technicians t 
     JOIN users u ON t.user_id = u.id 
     WHERE t.status = 'approved' AND u.is_active = 1 
     ORDER BY t.avg_rating DESC, t.total_reviews DESC 
     LIMIT 4"
);

// Fetch popular services
$popularServices = dbQuery(
    "SELECT s.*, c.name as category_name, c.icon as category_icon 
     FROM services s 
     JOIN categories c ON s.category_id = c.id 
     WHERE s.is_active = 1 
     ORDER BY s.id ASC 
     LIMIT 6"
);

// Stats
$totalTechs = dbQueryOne("SELECT COUNT(*) as cnt FROM technicians WHERE status='approved'")['cnt'] ?? 0;
$totalBookings = dbQueryOne("SELECT COUNT(*) as cnt FROM bookings WHERE status='completed'")['cnt'] ?? 0;
$totalUsers = dbQueryOne("SELECT COUNT(*) as cnt FROM users WHERE role='user' AND is_active=1")['cnt'] ?? 0;
?>

<!-- ═══════ HERO SECTION ═══════ -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7" data-aos="fade-right">
                <div class="hero-badge">
                    <i class="bi bi-shield-check"></i>
                    <span>Verified & Trusted Professionals</span>
                </div>
                <h1>
                    Your Trusted Partner for<br>
                    <span class="text-gradient">Home Services</span> in Pakistan
                </h1>
                <p class="mb-0">From mobile repair to plumbing — book skilled, verified technicians at your doorstep. Fast response, quality work, guaranteed satisfaction.</p>

                <!-- Hero Search -->
                <div class="hero-search">
                    <form action="<?= APP_URL ?>/services.php" method="GET" class="d-flex align-items-center">
                        <i class="bi bi-search text-white ms-3 me-2 fs-5"></i>
                        <input type="text" name="q" class="form-control" placeholder="What service do you need?" autocomplete="off">
                        <button type="submit" class="btn btn-accent btn-lg ms-2 px-4">Search</button>
                    </form>
                </div>

                <!-- Hero Stats -->
                <div class="hero-stats d-flex mt-4">
                    <div class="hero-stat">
                        <h3 data-counter="<?= $totalTechs ?>"><?= $totalTechs ?></h3>
                        <p>Expert Technicians</p>
                    </div>
                    <div class="hero-stat">
                        <h3 data-counter="<?= max($totalBookings, 500) ?>" data-suffix="+"><?= max($totalBookings, 500) ?>+</h3>
                        <p>Jobs Completed</p>
                    </div>
                    <div class="hero-stat">
                        <h3 data-counter="<?= max($totalUsers, 200) ?>" data-suffix="+"><?= max($totalUsers, 200) ?>+</h3>
                        <p>Happy Customers</p>
                    </div>
                    <div class="hero-stat">
                        <h3>4.8<i class="bi bi-star-fill text-warning ms-1" style="font-size:1rem"></i></h3>
                        <p>Average Rating</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block" data-aos="fade-left">
                <div class="text-center position-relative">
                    <!-- Hero illustration -->
                    <div class="p-4 hero-illustration-wrapper">
                        <div class="hero-icon-container mb-3 position-relative">
                            <!-- Premium Glow Effect -->
                            <div class="glow-ring"></div>
                            <div class="glow-ring delay-1"></div>
                            
                            <!-- The Main Animated Icon -->
                            <div class="tool-icon-wrapper d-inline-flex align-items-center justify-content-center rounded-circle shadow-lg" style="width:220px;height:220px;background:linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.02));border:1px solid rgba(255,255,255,0.2);backdrop-filter:blur(10px);z-index:2;position:relative;">
                                <!-- Hand/Tool SVG -->
                                <svg class="animated-wrench text-white" viewBox="0 0 512 512" width="100" height="100" fill="currentColor" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 0 15px rgba(255,255,255,0.3));">
                                    <path d="M482.9 87.2c-23.7-23.7-62.1-23.7-85.8 0L331.5 152.8c-10.2 10.2-10.2 26.8 0 37l16.2 16.2-192.1 192.1c-14.5-5.9-31.5-4.5-45 4.3L6.8 471.2c-7.9 7.9-7.9 20.6 0 28.5l5.5 5.5c7.9 7.9 20.6 7.9 28.5 0l68.8-68.8c8.8-13.5 10.2-30.5 4.3-45l192.1-192.1 16.2 16.2c10.2 10.2 26.8 10.2 37 0l65.6-65.6c23.7-23.7 23.7-62.1 0-85.8zM413 147l-35.3 35.3-24.8-24.8L388.2 122c4-4 10.5-4 14.5 0l10.3 10.3c4.1 4 4.1 10.5 0 14.7z"/>
                                </svg>
                            </div>
                        </div>
                        <!-- Floating badges -->
                        <div class="position-absolute bg-white rounded-3 shadow-lg p-2 px-3 d-flex align-items-center gap-2 floating-badge" style="top:5%;right:0;animation-delay:0s;z-index:3;">
                            <i class="bi bi-lightning-charge-fill text-warning fs-5"></i>
                            <small class="fw-bold text-dark">Quick Response</small>
                        </div>
                        <div class="position-absolute bg-white rounded-3 shadow-lg p-2 px-3 d-flex align-items-center gap-2 floating-badge" style="bottom:15%;left:-5%;animation-delay:1s;z-index:3;">
                            <i class="bi bi-shield-check text-success fs-5"></i>
                            <small class="fw-bold text-dark">100% Verified</small>
                        </div>
                        <div class="position-absolute bg-white rounded-3 shadow-lg p-2 px-3 d-flex align-items-center gap-2 floating-badge" style="bottom:2%;right:5%;animation-delay:2s;z-index:3;">
                            <i class="bi bi-star-fill text-warning fs-5"></i>
                            <small class="fw-bold text-dark">4.8 Rating</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .floating-badge {
            animation: floatBadge 4s ease-in-out infinite;
        }
        @keyframes floatBadge {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }
        .hero-icon-container {
            display: inline-block;
            perspective: 1000px;
        }
        .animated-wrench {
            animation: turnWrench 3s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            transform-origin: center;
        }
        @keyframes turnWrench {
            0%, 100% { transform: rotate(-15deg); }
            50% { transform: rotate(25deg) scale(1.1); }
        }
        .glow-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 220px;
            height: 220px;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            animation: pulseGlow 3s infinite;
            z-index: 1;
        }
        .glow-ring.delay-1 {
            animation-delay: 1.5s;
        }
        @keyframes pulseGlow {
            0% { transform: translate(-50%, -50%) scale(1); opacity: 0.8; }
            100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
        }
    </style>
</section>

<!-- ═══════ CATEGORIES SECTION ═══════ -->
<section class="section" style="background:var(--card);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Our Services</span>
            <h2>Browse by Category</h2>
            <p>Find the right professional for every home service need</p>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $i => $cat): ?>
            <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                <a href="<?= APP_URL ?>/services.php?cat=<?= $cat['slug'] ?>" class="text-decoration-none">
                    <div class="category-card">
                        <div class="icon-wrapper">
                            <i class="bi <?= sanitize($cat['icon']) ?>"></i>
                        </div>
                        <h5><?= sanitize($cat['name']) ?></h5>
                        <p class="truncate-2"><?= sanitize(substr($cat['description'] ?? '', 0, 60)) ?></p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════ HOW IT WORKS ═══════ -->
<section class="section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">How It Works</span>
            <h2>Book a Service in 3 Easy Steps</h2>
            <p>Getting help has never been easier</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-connector d-none d-md-block"></div>
                    <h5>Choose a Service</h5>
                    <p>Browse our categories and select the service you need for your home or office.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-connector d-none d-md-block"></div>
                    <h5>Book a Technician</h5>
                    <p>Pick a verified professional, choose your date & time, and confirm your booking.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h5>Get It Done</h5>
                    <p>The technician arrives at your doorstep. Pay after the job is completed to your satisfaction.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════ POPULAR SERVICES ═══════ -->
<section class="section" style="background:var(--card);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Popular Services</span>
            <h2>Most Booked Services</h2>
            <p>Our top-rated services trusted by thousands</p>
        </div>
        <div class="row g-4">
            <?php foreach ($popularServices as $i => $svc): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                <div class="card-custom">
                    <div class="overflow-hidden">
                        <?php if (!empty($svc['image'])): ?>
                            <img src="<?= APP_URL ?>/uploads/services/<?= sanitize($svc['image']) ?>" alt="<?= sanitize($svc['name']) ?>" class="card-img-top w-100" style="height:180px; object-fit:cover; transition: transform 0.3s ease;">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,rgba(37,99,235,0.08),rgba(124,58,237,0.08));height:180px;">
                                <i class="bi <?= sanitize($svc['category_icon']) ?>" style="font-size:3rem;color:var(--primary);"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:0.75rem;"><?= sanitize($svc['category_name']) ?></span>
                            <span class="text-muted small"><i class="bi bi-clock me-1"></i><?= $svc['duration_minutes'] ?> min</span>
                        </div>
                        <h5 class="card-title"><?= sanitize($svc['name']) ?></h5>
                        <p class="text-muted small truncate-2 mb-3"><?= sanitize($svc['description'] ?? '') ?></p>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-bold text-primary fs-5"><?= formatPrice($svc['base_price']) ?></span>
                                <small class="text-muted ms-1">starting</small>
                            </div>
                            <a href="<?= APP_URL ?>/service-detail.php?slug=<?= $svc['slug'] ?>" class="btn btn-sm btn-outline-primary">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4" data-aos="fade-up">
            <a href="<?= APP_URL ?>/services.php" class="btn btn-primary btn-lg px-5">View All Services <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
</section>

<!-- ═══════ TOP TECHNICIANS ═══════ -->
<section class="section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Top Professionals</span>
            <h2>Meet Our Best Technicians</h2>
            <p>Verified, skilled, and ready to help</p>
        </div>
        <div class="row g-4">
            <?php foreach ($topTechs as $i => $tech): ?>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="tech-card text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="<?= getAvatarUrl($tech['avatar'], $tech['name'] ?? '') ?>" alt="<?= sanitize($tech['name']) ?>" class="avatar-lg">
                        <?php if ($tech['is_available']): ?>
                        <span class="badge-status"></span>
                        <?php endif; ?>
                    </div>
                    <h6 class="fw-bold mb-1"><?= sanitize($tech['name']) ?></h6>
                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i><?= sanitize($tech['city']) ?></p>
                    <?= getStarRating($tech['avg_rating']) ?>
                    <p class="small text-muted mt-1"><?= $tech['total_jobs'] ?> jobs • <?= $tech['experience_years'] ?> yrs exp</p>
                    <a href="<?= APP_URL ?>/technician-profile.php?id=<?= $tech['id'] ?>" class="btn btn-sm btn-outline-primary w-100 mt-2">View Profile</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4" data-aos="fade-up">
            <a href="<?= APP_URL ?>/technicians.php" class="btn btn-outline-primary btn-lg px-5">Browse All Technicians</a>
        </div>
    </div>
</section>

<!-- ═══════ WHY CHOOSE US ═══════ -->
<section class="section" style="background:var(--card);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Why AsaanFix</span>
            <h2>Why Thousands Trust Us</h2>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['bi-shield-check', 'Verified Professionals', 'Every technician is background-checked and skill-verified before joining our platform.', '--success'],
                ['bi-clock-history', 'Fast Response Time', 'Get a technician at your doorstep within hours. Emergency services available 24/7.', '--primary'],
                ['bi-cash-stack', 'Transparent Pricing', 'No hidden charges. Know the cost upfront before you book any service.', '--accent'],
                ['bi-star-fill', 'Quality Guaranteed', 'Not satisfied? We offer free re-service or full refund on all bookings.', '--danger'],
                ['bi-headset', '24/7 Support', 'Our customer support team is always ready to help you with any queries.', '--info'],
                ['bi-credit-card', 'Secure Payments', 'Multiple payment options including Cash, JazzCash, EasyPaisa, and bank transfer.', '--secondary'],
            ];
            foreach ($features as $i => $f): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $i * 80 ?>">
                <div class="d-flex gap-3 p-3 rounded-3 hover-lift" style="background:var(--surface);">
                    <div class="flex-shrink-0">
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width:48px;height:48px;background:rgba(var(--primary-rgb),0.1);">
                            <i class="bi <?= $f[0] ?>" style="font-size:1.3rem;color:var(<?= $f[3] ?>);"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1"><?= $f[1] ?></h6>
                        <p class="text-muted small mb-0"><?= $f[2] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════ CTA SECTION ═══════ -->
<section class="cta-section">
    <div class="container text-center position-relative" data-aos="zoom-in">
        <h2 class="mb-3">Ready to Get Your Problem Fixed?</h2>
        <p class="text-white-50 mb-4 fs-5">Join thousands of satisfied customers across Pakistan</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= APP_URL ?>/services.php" class="btn btn-accent btn-lg px-5">
                <i class="bi bi-search me-2"></i>Browse Services
            </a>
            <?php if (!isLoggedIn()): ?>
            <a href="<?= APP_URL ?>/auth/register.php" class="btn btn-outline-light btn-lg px-5">
                <i class="bi bi-person-plus me-2"></i>Sign Up Free
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
