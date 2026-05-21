<?php $pageTitle = 'Privacy Policy'; require_once __DIR__ . '/includes/header.php'; ?>
<section class="page-header"><div class="container"><h1><i class="bi bi-shield-lock me-2"></i>Privacy Policy</h1></div></section>
<section class="section py-5"><div class="container" style="max-width:800px;">
<div class="card-custom p-5">
<p class="text-muted">Last updated: <?= date('F d, Y') ?></p>
<h4>1. Information We Collect</h4><p>We collect: name, email, phone number, city, address (for service delivery), profile photos, and booking history. For technicians, we additionally collect CNIC and skill information.</p>
<h4>2. How We Use Information</h4>
<ul><li>To facilitate service bookings between users and technicians</li><li>To send booking confirmations and status updates</li><li>To improve our platform and user experience</li><li>To ensure safety and prevent fraud</li><li>To send promotional communications (with consent)</li></ul>
<h4>3. Data Sharing</h4><p>We share your contact information with technicians only when a booking is confirmed. We do not sell your personal data to third parties. We may share anonymized analytics data.</p>
<h4>4. Data Security</h4><p>We implement industry-standard security measures including encrypted passwords, secure sessions, CSRF protection, and prepared database statements. However, no system is 100% secure.</p>
<h4>5. Cookies</h4><p>We use session cookies for authentication. These are essential for the platform to function. No third-party tracking cookies are used.</p>
<h4>6. Your Rights</h4><p>You can access, update, or delete your personal data through your profile settings. You may request complete account deletion by contacting support.</p>
<h4>7. Data Retention</h4><p>We retain your data as long as your account is active. Booking history is retained for 2 years after completion for dispute resolution purposes.</p>
<h4>8. Children's Privacy</h4><p>Our platform is not intended for users under 18. We do not knowingly collect data from minors.</p>
<h4>9. Contact</h4><p>For privacy concerns, email us at <a href="mailto:<?= APP_EMAIL ?>"><?= APP_EMAIL ?></a>.</p>
</div></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
