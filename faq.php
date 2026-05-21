<?php
/**
 * AsaanFix Pakistan - FAQ Page
 */
$pageTitle = 'Frequently Asked Questions';
require_once __DIR__ . '/includes/header.php';

$faqs = [
    ['General' => [
        ['How does AsaanFix work?', 'AsaanFix connects you with verified local technicians. Browse services, pick a professional, book a time slot, and the technician arrives at your doorstep. You pay after the work is done.'],
        ['Is AsaanFix available in my city?', 'We currently operate in major cities including Islamabad, Rawalpindi, Lahore, Karachi, Faisalabad, Peshawar, and more. We are expanding rapidly across Pakistan.'],
        ['Are the technicians verified?', 'Yes! Every technician goes through a thorough verification process including CNIC verification, skill assessment, and background checks before being approved.'],
    ]],
    ['Booking & Payment' => [
        ['How do I book a service?', 'Select a service category, choose a technician, pick your preferred date and time, provide your address, and confirm the booking. It takes less than 2 minutes!'],
        ['What payment methods are accepted?', 'We accept Cash on Delivery (COD), JazzCash, EasyPaisa, and bank transfers. Payment is collected after the service is completed.'],
        ['Can I cancel a booking?', 'Yes, you can cancel a booking before the technician starts the work. Cancellations made at least 2 hours before the scheduled time are free of charge.'],
        ['How is pricing determined?', 'Each service has a base price. The final price may vary depending on the complexity of the work, parts required, and the technician\'s rates.'],
    ]],
    ['For Technicians' => [
        ['How can I join as a technician?', 'Register on AsaanFix, select "Work as Tech" during signup, complete your profile, and submit for verification. Once approved, you can start receiving bookings.'],
        ['How do I get paid?', 'You receive payment directly from customers after completing the job. AsaanFix charges a small commission on each completed booking.'],
        ['Can I set my own availability?', 'Yes! You have full control over your schedule, available hours, working days, and service areas through your technician dashboard.'],
    ]],
];
?>

<section class="page-header">
    <div class="container">
        <h1><i class="bi bi-question-circle me-2"></i>FAQ</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Home</a></li>
            <li class="breadcrumb-item active">FAQ</li>
        </ol></nav>
    </div>
</section>

<section class="section py-5">
    <div class="container" style="max-width:800px;">
        <?php $accordionId = 0; foreach ($faqs as $group): ?>
        <?php foreach ($group as $title => $items): ?>
        <h4 class="fw-bold mt-4 mb-3" data-aos="fade-up"><?= $title ?></h4>
        <div class="accordion mb-4" id="faq<?= $accordionId ?>" data-aos="fade-up">
            <?php foreach ($items as $j => $item): $colId = "faq{$accordionId}q{$j}"; ?>
            <div class="accordion-item border rounded-3 mb-2">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $colId ?>">
                        <?= sanitize($item[0]) ?>
                    </button>
                </h2>
                <div id="<?= $colId ?>" class="accordion-collapse collapse" data-bs-parent="#faq<?= $accordionId ?>">
                    <div class="accordion-body text-secondary"><?= sanitize($item[1]) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php $accordionId++; endforeach; endforeach; ?>

        <div class="text-center mt-5 p-4 rounded-3" style="background:var(--surface);" data-aos="fade-up">
            <h5 class="fw-bold">Still have questions?</h5>
            <p class="text-muted">Can't find the answer you're looking for? Contact our support team.</p>
            <a href="<?= APP_URL ?>/contact.php" class="btn btn-primary px-4"><i class="bi bi-envelope me-2"></i>Contact Us</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
