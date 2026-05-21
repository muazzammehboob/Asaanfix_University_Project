<?php
/**
 * AsaanFix Pakistan - Technicians Listing
 */
require_once __DIR__ . '/includes/functions.php';

$search = sanitize($_GET['q'] ?? '');
$city = sanitize($_GET['city'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'rating');
$page = max(1, (int)($_GET['page'] ?? 1));

$where = ["t.status = 'approved'", "u.is_active = 1"];
$params = [];

if ($search) {
    $where[] = "(u.name LIKE ? OR t.skills LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($city) {
    $where[] = "u.city = ?";
    $params[] = $city;
}

$whereSQL = implode(' AND ', $where);
$countRow = dbQueryOne("SELECT COUNT(*) as cnt FROM technicians t JOIN users u ON t.user_id = u.id WHERE $whereSQL", $params);
$pagination = getPagination($countRow['cnt'], $page);

$orderMap = ['rating' => 't.avg_rating DESC', 'jobs' => 't.total_jobs DESC', 'experience' => 't.experience_years DESC', 'price_low' => 't.hourly_rate ASC'];
$orderSQL = $orderMap[$sort] ?? 't.avg_rating DESC';

$techs = dbQuery(
    "SELECT t.*, u.name, u.avatar, u.city, u.phone
     FROM technicians t JOIN users u ON t.user_id = u.id
     WHERE $whereSQL ORDER BY $orderSQL LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
    $params
);

$cities = dbQuery("SELECT DISTINCT city FROM users WHERE role='technician' AND city IS NOT NULL ORDER BY city ASC");

$pageTitle = 'Our Technicians';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1><i class="bi bi-people me-2"></i>Our Technicians</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
            <li class="breadcrumb-item active">Technicians</li>
        </ol></nav>
    </div>
</section>

<section class="section py-4">
    <div class="container">
        <div class="filter-bar mb-4" data-aos="fade-up">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" class="form-control" name="q" value="<?= sanitize($search) ?>" placeholder="Name or skill...">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-semibold">City</label>
                    <select class="form-select" name="city">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $c): ?>
                        <option value="<?= sanitize($c['city']) ?>" <?= $city === $c['city'] ? 'selected' : '' ?>><?= sanitize($c['city']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-semibold">Sort By</label>
                    <select class="form-select" name="sort">
                        <option value="rating" <?= $sort==='rating'?'selected':'' ?>>Highest Rated</option>
                        <option value="jobs" <?= $sort==='jobs'?'selected':'' ?>>Most Jobs</option>
                        <option value="experience" <?= $sort==='experience'?'selected':'' ?>>Experience</option>
                        <option value="price_low" <?= $sort==='price_low'?'selected':'' ?>>Lowest Rate</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <button class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
                </div>
            </form>
        </div>

        <?php if (empty($techs)): ?>
        <div class="text-center py-5"><i class="bi bi-person-slash display-1 text-muted"></i><h4 class="mt-3">No technicians found</h4></div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($techs as $i => $tech): ?>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i%8)*60 ?>">
                <div class="tech-card text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="<?= getAvatarUrl($tech['avatar'], $tech['name'] ?? '') ?>" alt="<?= sanitize($tech['name']) ?>" class="avatar-lg">
                        <?php if ($tech['is_available']): ?><span class="badge-status"></span><?php endif; ?>
                    </div>
                    <h6 class="fw-bold mb-1"><?= sanitize($tech['name']) ?></h6>
                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i><?= sanitize($tech['city']) ?></p>
                    <?= getStarRating($tech['avg_rating']) ?>
                    <div class="d-flex justify-content-center gap-3 my-2 small text-muted">
                        <span><strong class="text-dark"><?= $tech['total_jobs'] ?></strong> Jobs</span>
                        <span><strong class="text-dark"><?= $tech['experience_years'] ?></strong> Yrs</span>
                    </div>
                    <div class="fw-bold text-primary mb-2"><?= formatPrice($tech['hourly_rate']) ?>/hr</div>
                    <?php if ($tech['skills']): ?>
                    <div class="mb-3">
                        <?php foreach (array_slice(explode(',', $tech['skills']), 0, 3) as $skill): ?>
                        <span class="badge bg-light text-dark border" style="font-size:0.7rem;"><?= sanitize(trim($skill)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <a href="<?= APP_URL ?>/technician-profile.php?id=<?= $tech['id'] ?>" class="btn btn-outline-primary btn-sm w-100">View Profile</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-4"><?= renderPagination($pagination, APP_URL . '/technicians.php?' . http_build_query(array_filter(['q'=>$search,'city'=>$city,'sort'=>$sort]))) ?></div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
