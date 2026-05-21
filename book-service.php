<?php
/**
 * AsaanFix Pakistan - Book Service
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$serviceId = (int)($_GET['service'] ?? 0);
$techId = (int)($_GET['tech'] ?? 0);
$errors = [];

// Get service
$service = $serviceId ? dbQueryOne("SELECT s.*, c.name as category_name FROM services s JOIN categories c ON s.category_id = c.id WHERE s.id = ? AND s.is_active = 1", [$serviceId]) : null;

// Get available technicians for this service (or all approved)
if ($service) {
    $availTechs = dbQuery(
        "SELECT t.*, u.name, u.avatar, u.city, ts.custom_price 
         FROM technician_services ts JOIN technicians t ON ts.technician_id = t.id JOIN users u ON t.user_id = u.id 
         WHERE ts.service_id = ? AND t.status = 'approved' AND u.is_active = 1 ORDER BY t.avg_rating DESC",
        [$service['id']]
    );
} else {
    $availTechs = [];
}

// Process booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $serviceId   = (int)($_POST['service_id'] ?? 0);
    $techId      = (int)($_POST['technician_id'] ?? 0);
    $bookDate    = sanitize($_POST['booking_date'] ?? '');
    $bookTime    = sanitize($_POST['booking_time'] ?? '');
    $address     = sanitize($_POST['address'] ?? '');
    $city        = sanitize($_POST['city'] ?? '');
    $phone       = sanitize($_POST['phone'] ?? '');
    $description = sanitize($_POST['description'] ?? '');

    if (!$serviceId) $errors[] = 'Please select a service.';
    if (!$techId) $errors[] = 'Please select a technician.';
    if (empty($bookDate)) $errors[] = 'Please select a date.';
    if (empty($bookTime)) $errors[] = 'Please select a time.';
    if (empty($address)) $errors[] = 'Address is required.';
    if (empty($city)) $errors[] = 'City is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';
    if ($bookDate < date('Y-m-d')) $errors[] = 'Cannot book for a past date.';

    if (empty($errors)) {
        $svc = dbQueryOne("SELECT base_price FROM services WHERE id = ?", [$serviceId]);
        $ts = dbQueryOne("SELECT custom_price FROM technician_services WHERE technician_id = ? AND service_id = ?", [$techId, $serviceId]);
        $amount = $ts['custom_price'] ?? $svc['base_price'];
        $bookingNumber = generateBookingNumber();

        dbExecute(
            "INSERT INTO bookings (booking_number, user_id, technician_id, service_id, status, booking_date, booking_time, address, city, phone, description, total_amount) 
             VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?)",
            [$bookingNumber, getCurrentUserId(), $techId, $serviceId, $bookDate, $bookTime, $address, $city, $phone, $description, $amount]
        );
        $bookingId = dbLastId();

        // Status history
        dbExecute("INSERT INTO booking_status_history (booking_id, status, notes, changed_by) VALUES (?, 'pending', 'Booking created', ?)", [$bookingId, getCurrentUserId()]);

        // Payment record
        dbExecute("INSERT INTO payments (booking_id, amount, method, status) VALUES (?, ?, 'cash', 'pending')", [$bookingId, $amount]);

        // Notify technician
        $techUserId = dbQueryOne("SELECT user_id FROM technicians WHERE id = ?", [$techId])['user_id'];
        createNotification($techUserId, 'New Booking Request', "You have a new booking #{$bookingNumber}.", 'booking', '/technician/bookings.php');

        setFlash('success', "Booking #{$bookingNumber} created successfully!");
        redirect(APP_URL . '/user/booking-detail.php?id=' . $bookingId);
    }
}

$pageTitle = 'Book a Service';
$user = getCurrentUser();
require_once __DIR__ . '/includes/header.php';
$cities = ['Islamabad','Rawalpindi','Lahore','Karachi','Faisalabad','Peshawar','Quetta','Multan','Sialkot','Gujranwala','Hyderabad'];
?>

<section class="page-header">
    <div class="container">
        <h1><i class="bi bi-calendar-check me-2"></i>Book a Service</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
            <li class="breadcrumb-item active">Book Service</li>
        </ol></nav>
    </div>
</section>

<section class="section py-4">
    <div class="container" style="max-width:800px;">
        <?php if ($errors): ?>
        <div class="alert alert-danger"><?php foreach($errors as $e): ?><div class="small"><i class="bi bi-exclamation-circle me-1"></i><?= $e ?></div><?php endforeach; ?></div>
        <?php endif; ?>

        <div class="card-custom p-4" data-aos="fade-up">
            <form method="POST" class="needs-validation" novalidate>
                <?= csrfField() ?>

                <!-- Service Info -->
                <?php if ($service): ?>
                <div class="p-3 rounded-3 mb-4 d-flex align-items-center gap-3" style="background:var(--surface);">
                    <div class="d-flex align-items-center justify-content-center rounded-2" style="width:48px;height:48px;background:rgba(var(--primary-rgb),0.1);">
                        <i class="bi bi-tools text-primary fs-5"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold"><?= sanitize($service['name']) ?></h6>
                        <small class="text-muted"><?= sanitize($service['category_name']) ?> • Starting <?= formatPrice($service['base_price']) ?></small>
                    </div>
                    <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                </div>
                <?php else: ?>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Select Service *</label>
                    <select class="form-select" name="service_id" required>
                        <option value="">Choose a service...</option>
                        <?php $allServices = dbQuery("SELECT s.*, c.name as cat FROM services s JOIN categories c ON s.category_id = c.id WHERE s.is_active = 1 ORDER BY c.name, s.name");
                        foreach ($allServices as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= sanitize($s['cat']) ?> → <?= sanitize($s['name']) ?> (<?= formatPrice($s['base_price']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Technician Selection -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Select Technician *</label>
                    <?php if ($availTechs): ?>
                    <div class="row g-2">
                        <?php foreach ($availTechs as $at): ?>
                        <div class="col-md-6">
                            <input type="radio" class="btn-check" name="technician_id" id="tech<?= $at['id'] ?>" value="<?= $at['id'] ?>" <?= $techId == $at['id'] ? 'checked' : '' ?> required>
                            <label class="btn btn-outline-secondary w-100 text-start p-3" for="tech<?= $at['id'] ?>">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= getAvatarUrl($at['avatar'], $at['name'] ?? '') ?>" width="36" height="36" class="rounded-circle" style="object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold small"><?= sanitize($at['name']) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem;">⭐ <?= $at['avg_rating'] ?> • <?= sanitize($at['city']) ?> • <?= formatPrice($at['custom_price'] ?? $service['base_price']) ?></div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <select class="form-select" name="technician_id" required>
                        <option value="">Choose a technician...</option>
                        <?php $allTechs = dbQuery("SELECT t.*, u.name, u.city FROM technicians t JOIN users u ON t.user_id = u.id WHERE t.status = 'approved'");
                        foreach ($allTechs as $at): ?>
                        <option value="<?= $at['id'] ?>" <?= $techId == $at['id'] ? 'selected' : '' ?>><?= sanitize($at['name']) ?> (<?= sanitize($at['city']) ?>) — ⭐ <?= $at['avg_rating'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                </div>

                <!-- Date & Time -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Preferred Date *</label>
                        <input type="date" class="form-control" name="booking_date" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Preferred Time *</label>
                        <select class="form-select" name="booking_time" required>
                            <option value="">Select time...</option>
                            <?php for ($h = 9; $h <= 20; $h++): ?>
                            <option value="<?= sprintf('%02d:00', $h) ?>"><?= date('g:i A', strtotime("$h:00")) ?></option>
                            <option value="<?= sprintf('%02d:30', $h) ?>"><?= date('g:i A', strtotime("$h:30")) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <!-- Address -->
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Service Address *</label>
                        <textarea class="form-control" name="address" rows="2" required placeholder="House/Flat number, Street, Area"><?= sanitize($user['address'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">City *</label>
                        <select class="form-select" name="city" required>
                            <?php foreach ($cities as $c): ?>
                            <option value="<?= $c ?>" <?= ($user['city'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Phone *</label>
                        <input type="tel" class="form-control" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>" required placeholder="03001234567">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Problem Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Briefly describe the issue..."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-calendar-check me-2"></i>Confirm Booking
                </button>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
