</main>

<?php if (empty($bodyClass) || strpos($bodyClass, 'dashboard-page') === false): ?>
<!-- Footer -->
<footer class="site-footer mt-auto">
    <div class="footer-top">
        <div class="container">
            <div class="row g-4">
                <!-- Brand -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand">
                        <h4><i class="bi bi-tools me-2"></i>AsaanFix Pakistan</h4>
                        <p class="text-light-emphasis">Pakistan's most trusted platform for booking verified technicians and home services. Quality work, guaranteed satisfaction.</p>
                        <div class="social-links mt-3">
                            <a href="#" class="social-link" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="social-link" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                            <a href="#" class="social-link" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                            <a href="#" class="social-link" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Quick Links</h6>
                    <ul class="footer-links">
                        <li><a href="<?= APP_URL ?>/home">Home</a></li>
                        <li><a href="<?= APP_URL ?>/services">Services</a></li>
                        <li><a href="<?= APP_URL ?>/technicians">Technicians</a></li>
                        <li><a href="<?= APP_URL ?>/about">About Us</a></li>
                        <li><a href="<?= APP_URL ?>/contact">Contact</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Services</h6>
                    <ul class="footer-links">
                        <li><a href="<?= APP_URL ?>/services?cat=mobile-repair">Mobile Repair</a></li>
                        <li><a href="<?= APP_URL ?>/services?cat=electrician">Electrician</a></li>
                        <li><a href="<?= APP_URL ?>/services?cat=plumbing">Plumbing</a></li>
                        <li><a href="<?= APP_URL ?>/services?cat=ac-services">AC Services</a></li>
                        <li><a href="<?= APP_URL ?>/services?cat=it-support">IT Support</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="footer-heading">Contact Us</h6>
                    <ul class="footer-contact">
                        <li><i class="bi bi-geo-alt me-2"></i>Blue Area, Islamabad, Pakistan</li>
                        <li><i class="bi bi-telephone me-2"></i><?= APP_PHONE ?></li>
                        <li><i class="bi bi-envelope me-2"></i><?= APP_EMAIL ?></li>
                        <li><i class="bi bi-clock me-2"></i>Mon - Sat: 9:00 AM - 8:00 PM</li>
                    </ul>
                    <div class="mt-3">
                        <a href="<?= APP_URL ?>/faq" class="text-light-emphasis me-3"><small>FAQ</small></a>
                        <a href="<?= APP_URL ?>/terms" class="text-light-emphasis me-3"><small>Terms</small></a>
                        <a href="<?= APP_URL ?>/privacy" class="text-light-emphasis"><small>Privacy</small></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0"><small>Made with <i class="bi bi-heart-fill text-danger"></i> in Pakistan</small></p>
                </div>
            </div>
        </div>
    </div>
</footer>
<?php endif; ?>

<!-- Back to Top -->
<button id="backToTop" class="btn btn-primary btn-back-to-top" aria-label="Back to top">
    <i class="bi bi-chevron-up"></i>
</button>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS JS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Chart.js (loaded on admin pages) -->
<?php if (!empty($bodyClass) && strpos($bodyClass, 'dashboard-page') !== false): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<?php endif; ?>
<!-- Main JS -->
<script src="<?= ASSETS_URL ?>/js/app.js"></script>

<?php if (isset($extraJS)): ?>
<?php foreach ($extraJS as $js): ?>
<script src="<?= ASSETS_URL ?>/js/<?= $js ?>"></script>
<?php endforeach; ?>
<?php endif; ?>

</body>
</html>
