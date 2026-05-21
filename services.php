<?php
/**
 * AsaanFix Pakistan - Services Listing
 */
require_once __DIR__ . '/includes/functions.php';

$catSlug = sanitize($_GET['cat'] ?? '');
$search = sanitize($_GET['q'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'name');
$page = max(1, (int)($_GET['page'] ?? 1));

// Build query
$where = ["s.is_active = 1"];
$params = [];

if ($catSlug) {
    $where[] = "c.slug = ?";
    $params[] = $catSlug;
}
if ($search) {
    $where[] = "(s.name LIKE ? OR s.description LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = implode(' AND ', $where);

// Count total
$countRow = dbQueryOne("SELECT COUNT(*) as cnt FROM services s JOIN categories c ON s.category_id = c.id WHERE $whereSQL", $params);
$pagination = getPagination($countRow['cnt'], $page);

// Sort
$orderMap = ['price_low' => 's.base_price ASC', 'price_high' => 's.base_price DESC', 'name' => 's.name ASC'];
$orderSQL = $orderMap[$sort] ?? 's.name ASC';

// Fetch services
$services = dbQuery(
    "SELECT s.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon 
     FROM services s JOIN categories c ON s.category_id = c.id 
     WHERE $whereSQL ORDER BY $orderSQL LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}",
    $params
);

// All categories for filter
$categories = dbQuery("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC");

$activeCat = $catSlug ? dbQueryOne("SELECT name FROM categories WHERE slug = ?", [$catSlug]) : null;
$pageTitle = $activeCat ? $activeCat['name'] . ' Services' : ($search ? "Search: $search" : 'All Services');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1><i class="bi bi-grid me-2"></i><?= sanitize($pageTitle) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                <li class="breadcrumb-item active">Services</li>
            </ol>
        </nav>
    </div>
</section>

<section class="section py-4">
    <div class="container">
        <!-- Filter Bar -->
        <div class="filter-bar mb-4" data-aos="fade-up">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small fw-semibold">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" name="q" value="<?= sanitize($search) ?>" placeholder="Search services...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-semibold">Category</label>
                    <select class="form-select" name="cat">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['slug'] ?>" <?= $catSlug === $c['slug'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-semibold">Sort By</label>
                    <select class="form-select" name="sort">
                        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name (A-Z)</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
                </div>
            </form>
        </div>

        <!-- Category Pills -->
        <div class="d-flex flex-wrap gap-2 mb-4" data-aos="fade-up">
            <a href="<?= APP_URL ?>/services.php" class="btn btn-sm <?= !$catSlug ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">All</a>
            <?php foreach ($categories as $c): ?>
            <a href="<?= APP_URL ?>/services.php?cat=<?= $c['slug'] ?>" class="btn btn-sm <?= $catSlug === $c['slug'] ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">
                <i class="bi <?= $c['icon'] ?> me-1"></i><?= sanitize($c['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Results -->
        <?php if (empty($services)): ?>
        <div class="text-center py-5" data-aos="fade-up">
            <i class="bi bi-search display-1 text-muted"></i>
            <h4 class="mt-3">No services found</h4>
            <p class="text-muted">Try adjusting your search or filter criteria.</p>
            <a href="<?= APP_URL ?>/services.php" class="btn btn-primary">View All Services</a>
        </div>
        <?php else: ?>
        <p class="text-muted mb-3" data-aos="fade-up"><strong><?= $pagination['total'] ?></strong> services found</p>
        <div class="row g-4">
            <?php foreach ($services as $i => $svc): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i % 6) * 60 ?>">
                <div class="card-custom">
                    <div class="overflow-hidden">
                        <img class="card-img-top" src="<?= getServiceImageUrl($svc['category_slug'], $i) ?>" alt="<?= sanitize($svc['name']) ?>" loading="lazy" style="height:180px;object-fit:cover;" onerror="this.onerror=null;this.style.background='linear-gradient(135deg,rgba(37,99,235,0.08),rgba(124,58,237,0.08))';this.style.objectFit='none';this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22><text x=%2230%22 y=%2275%22 font-size=%2260%22 fill=%22%232563EB%22 opacity=%220.4%22>🔧</text></svg>'">
                    </div>
                    <div class="card-body">
                        <span class="badge bg-primary bg-opacity-10 text-primary mb-2" style="font-size:0.72rem;"><?= sanitize($svc['category_name']) ?></span>
                        <h5 class="card-title"><?= sanitize($svc['name']) ?></h5>
                        <p class="text-muted small truncate-2 mb-3"><?= sanitize($svc['description'] ?? '') ?></p>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <div>
                                <span class="fw-bold text-primary"><?= formatPrice($svc['base_price']) ?></span>
                                <span class="text-muted small ms-1">• <?= $svc['duration_minutes'] ?> min</span>
                            </div>
                            <a href="<?= APP_URL ?>/service-detail?slug=<?= $svc['slug'] ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <?= renderPagination($pagination, APP_URL . '/services.php?' . http_build_query(array_filter(['cat' => $catSlug, 'q' => $search, 'sort' => $sort]))) ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
