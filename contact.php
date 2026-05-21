<?php
/**
 * AsaanFix Pakistan - Contact Page
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name))
        $errors[] = 'Name is required.';
    if (!isValidEmail($email))
        $errors[] = 'Valid email is required.';
    if (empty($subject))
        $errors[] = 'Subject is required.';
    if (empty($message) || strlen($message) < 10)
        $errors[] = 'Message must be at least 10 characters.';

    if (empty($errors)) {
        dbExecute(
            "INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)",
            [$name, $email, $phone, $subject, $message]
        );
        $success = true;
    }
}

$pageTitle = 'Contact Us';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1><i class="bi bi-envelope me-2"></i>Contact Us</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
                <li class="breadcrumb-item active">Contact</li>
            </ol>
        </nav>
    </div>
</section>

<section class="section py-5">
    <div class="container">
        <div class="row g-5 align-items-start">
            <!-- Contact Form -->
            <div class="col-lg-7" data-aos="fade-right">
                <h3 class="fw-bold mb-1">Get in Touch</h3>
                <p class="text-muted mb-4">Have a question or feedback? We'd love to hear from you.</p>

                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Your message has been sent
                        successfully! We'll get back to you soon.</div>
                <?php else: ?>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger py-2"><?php foreach ($errors as $e): ?>
                                <div class="small"><?= $e ?></div><?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <?= csrfField() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" placeholder="03001234567">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject *</label>
                                <input type="text" class="form-control" name="subject" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message *</label>
                                <textarea class="form-control" name="message" rows="5" required minlength="10"></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary btn-lg px-5"><i class="bi bi-send me-2"></i>Send
                                    Message</button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-5" data-aos="fade-left">
                <div class="card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-4">Contact Information</h5>
                    <?php
                    $info = [
                        ['bi-geo-alt-fill', 'Address', 'Blue Area, Jinnah Avenue, Islamabad, Pakistan'],
                        ['bi-telephone-fill', 'Phone', APP_PHONE],
                        ['bi-envelope-fill', 'Email', APP_EMAIL],
                        ['bi-clock-fill', 'Hours', 'Monday - Saturday: 9:00 AM - 8:00 PM'],
                    ];
                    foreach ($info as $item): ?>
                        <div class="d-flex gap-3 mb-3">
                            <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-2"
                                style="width:40px;height:40px;background:rgba(var(--primary-rgb),0.1);">
                                <i class="bi <?= $item[0] ?> text-primary"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block"><?= $item[1] ?></small>
                                <span class="fw-medium"><?= $item[2] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <hr>
                    <h6 class="fw-bold mb-3">Follow Us</h6>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-primary rounded-circle"
                            style="width:38px;height:38px;"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-primary rounded-circle"
                            style="width:38px;height:38px;"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-primary rounded-circle"
                            style="width:38px;height:38px;"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-primary rounded-circle"
                            style="width:38px;height:38px;"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Map placeholder -->
        <div class="card-custom mt-3 overflow-hidden">
            <div class="d-flex align-items-center justify-content-center"
                style="height:250px;background:linear-gradient(135deg,rgba(37,99,235,0.06),rgba(124,58,237,0.06));">
                <div class="text-center text-muted">
                    <i class="bi bi-geo-alt display-3 d-block mb-2"></i>
                    <small>Islamabad, Pakistan</small>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>