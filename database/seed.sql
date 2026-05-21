-- FixIt Hub Pakistan - Seed Data
USE `fixithub_pakistan`;

-- Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'FixIt Hub Pakistan', 'general'),
('site_email', 'info@fixithub.pk', 'general'),
('site_phone', '+92 300 1234567', 'general'),
('site_address', 'Blue Area, Islamabad, Pakistan', 'general'),
('currency', 'PKR', 'general'),
('commission_rate', '15', 'payment'),
('min_booking_hours', '2', 'booking'),
('max_booking_days_ahead', '30', 'booking'),
('maintenance_mode', '0', 'system'),
('email_notifications', '1', 'notification');

-- Admin User (password: Admin@123)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`, `city`, `is_active`, `email_verified_at`) VALUES
('Admin User', 'admin@fixithub.pk', '+923001234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Islamabad', 1, NOW());

-- Sample Users (password: User@123)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`, `avatar`, `address`, `city`, `is_active`, `email_verified_at`) VALUES
('Ahmed Khan', 'ahmed@example.com', '+923011111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'default-avatar.png', 'F-8 Markaz, Islamabad', 'Islamabad', 1, NOW()),
('Sara Ali', 'sara@example.com', '+923022222222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'default-avatar.png', 'DHA Phase 5, Lahore', 'Lahore', 1, NOW()),
('Bilal Hussain', 'bilal@example.com', '+923033333333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'default-avatar.png', 'Clifton Block 9, Karachi', 'Karachi', 1, NOW());

-- Technician Users (password: Tech@123)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`, `avatar`, `address`, `city`, `is_active`, `email_verified_at`) VALUES
('Usman Electrician', 'usman@example.com', '+923044444444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician', 'default-avatar.png', 'G-9, Islamabad', 'Islamabad', 1, NOW()),
('Kamran AC Expert', 'kamran@example.com', '+923055555555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician', 'default-avatar.png', 'Gulberg III, Lahore', 'Lahore', 1, NOW()),
('Tariq Plumber', 'tariq@example.com', '+923066666666', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician', 'default-avatar.png', 'North Nazimabad, Karachi', 'Karachi', 1, NOW()),
('Ali Mobile Tech', 'ali.tech@example.com', '+923077777777', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician', 'default-avatar.png', 'Saddar, Rawalpindi', 'Rawalpindi', 1, NOW()),
('Hassan IT Pro', 'hassan@example.com', '+923088888888', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician', 'default-avatar.png', 'Blue Area, Islamabad', 'Islamabad', 1, NOW());

-- Categories
INSERT INTO `categories` (`name`, `slug`, `icon`, `description`, `sort_order`, `is_active`) VALUES
('Mobile Repair', 'mobile-repair', 'bi-phone', 'Professional mobile phone repair services including screen replacement, battery change, and software fixes.', 1, 1),
('Laptop Repair', 'laptop-repair', 'bi-laptop', 'Expert laptop repair and maintenance services for all brands.', 2, 1),
('Electrician', 'electrician', 'bi-lightning-charge', 'Certified electricians for wiring, installation, and electrical repairs.', 3, 1),
('Plumbing', 'plumbing', 'bi-droplet', 'Professional plumbing services for leaks, installations, and maintenance.', 4, 1),
('AC Services', 'ac-services', 'bi-snow', 'AC installation, repair, gas refill, and maintenance services.', 5, 1),
('Appliance Repair', 'appliance-repair', 'bi-gear', 'Repair services for washing machines, refrigerators, microwaves and more.', 6, 1),
('Home Maintenance', 'home-maintenance', 'bi-house-gear', 'General home maintenance including painting, carpentry, and cleaning.', 7, 1),
('IT Support', 'it-support', 'bi-pc-display', 'Technical IT support, network setup, and computer services.', 8, 1);

-- Services
INSERT INTO `services` (`category_id`, `name`, `slug`, `description`, `base_price`, `duration_minutes`, `is_active`) VALUES
(1, 'Screen Replacement', 'screen-replacement', 'Professional screen replacement for all mobile brands.', 2500.00, 60, 1),
(1, 'Battery Replacement', 'battery-replacement', 'Original battery replacement with warranty.', 1500.00, 30, 1),
(1, 'Software Fix', 'software-fix', 'OS reinstall, virus removal, and software troubleshooting.', 1000.00, 45, 1),
(2, 'Laptop Screen Repair', 'laptop-screen-repair', 'LCD/LED screen replacement for all laptop brands.', 5000.00, 90, 1),
(2, 'Laptop Cleaning', 'laptop-cleaning', 'Deep cleaning, thermal paste change, and fan cleaning.', 2000.00, 60, 1),
(2, 'Hardware Upgrade', 'hardware-upgrade', 'RAM, SSD, and hardware upgrade services.', 1500.00, 60, 1),
(3, 'Wiring Installation', 'wiring-installation', 'New wiring installation for homes and offices.', 3000.00, 120, 1),
(3, 'Switch & Socket Repair', 'switch-socket-repair', 'Repair and replacement of switches and sockets.', 800.00, 30, 1),
(3, 'Generator Repair', 'generator-repair', 'Generator installation and repair services.', 4000.00, 120, 1),
(4, 'Pipe Leak Repair', 'pipe-leak-repair', 'Fix leaking pipes and water connections.', 1500.00, 60, 1),
(4, 'Toilet Repair', 'toilet-repair', 'Toilet installation and repair services.', 2000.00, 60, 1),
(4, 'Water Tank Cleaning', 'water-tank-cleaning', 'Professional water tank cleaning service.', 3000.00, 120, 1),
(5, 'AC Installation', 'ac-installation', 'Split and window AC installation service.', 3500.00, 120, 1),
(5, 'AC Gas Refill', 'ac-gas-refill', 'AC gas charging and refill service.', 2500.00, 60, 1),
(5, 'AC Repair', 'ac-repair', 'AC compressor and general repair services.', 4000.00, 90, 1),
(6, 'Washing Machine Repair', 'washing-machine-repair', 'All types of washing machine repairs.', 2500.00, 90, 1),
(6, 'Refrigerator Repair', 'refrigerator-repair', 'Fridge and freezer repair services.', 3000.00, 90, 1),
(6, 'Microwave Repair', 'microwave-repair', 'Microwave oven repair and maintenance.', 1500.00, 60, 1),
(7, 'House Painting', 'house-painting', 'Interior and exterior painting services.', 5000.00, 480, 1),
(7, 'Carpentry Work', 'carpentry-work', 'Furniture repair, door fitting, and woodwork.', 2500.00, 120, 1),
(7, 'Deep Cleaning', 'deep-cleaning', 'Professional deep cleaning for homes.', 4000.00, 240, 1),
(8, 'Network Setup', 'network-setup', 'Wi-Fi and LAN network setup and configuration.', 3000.00, 120, 1),
(8, 'Computer Repair', 'computer-repair', 'Desktop computer repair and troubleshooting.', 2000.00, 60, 1),
(8, 'Data Recovery', 'data-recovery', 'Hard drive and SSD data recovery services.', 5000.00, 120, 1);

-- Technician Profiles
INSERT INTO `technicians` (`user_id`, `bio`, `cnic`, `skills`, `hourly_rate`, `experience_years`, `status`, `avg_rating`, `total_reviews`, `total_jobs`, `is_available`, `service_areas`) VALUES
(5, 'Certified electrician with 8+ years of experience in residential and commercial wiring.', '3520112345678', 'Wiring, Circuit Breakers, Generator, Solar Panel', 800.00, 8, 'approved', 4.80, 120, 250, 1, 'Islamabad, Rawalpindi'),
(6, 'Professional AC technician specializing in all major brands.', '3520198765432', 'AC Installation, Gas Refill, Compressor, Maintenance', 1000.00, 6, 'approved', 4.60, 85, 180, 1, 'Lahore, Sheikhupura'),
(7, 'Master plumber with expertise in all plumbing solutions.', '4220112233445', 'Pipe Fitting, Leak Repair, Bathroom, Kitchen', 700.00, 10, 'approved', 4.70, 95, 200, 1, 'Karachi, Hyderabad'),
(8, 'Mobile repair specialist with expertise in all smartphone brands.', '3740156789012', 'Screen, Battery, Motherboard, Software', 600.00, 5, 'approved', 4.50, 60, 120, 1, 'Rawalpindi, Islamabad'),
(9, 'IT professional offering network, hardware, and software solutions.', '3520145678901', 'Networking, Hardware, Software, Data Recovery', 1200.00, 7, 'approved', 4.90, 45, 90, 1, 'Islamabad');

-- Technician-Service Links
INSERT INTO `technician_services` (`technician_id`, `service_id`, `custom_price`) VALUES
(1, 7, 3500.00), (1, 8, 1000.00), (1, 9, 4500.00),
(2, 13, 4000.00), (2, 14, 3000.00), (2, 15, 4500.00),
(3, 10, 1800.00), (3, 11, 2500.00), (3, 12, 3500.00),
(4, 1, 3000.00), (4, 2, 1800.00), (4, 3, 1200.00),
(5, 22, 3500.00), (5, 23, 2500.00), (5, 24, 6000.00);

-- Sample Bookings
INSERT INTO `bookings` (`booking_number`, `user_id`, `technician_id`, `service_id`, `status`, `booking_date`, `booking_time`, `address`, `city`, `phone`, `description`, `total_amount`) VALUES
('FH-2026-0001', 2, 1, 7, 'completed', '2026-05-10', '10:00:00', 'House 15, F-8, Islamabad', 'Islamabad', '+923011111111', 'Need complete house wiring for 3 rooms.', 3500.00),
('FH-2026-0002', 3, 2, 14, 'completed', '2026-05-11', '14:00:00', 'DHA Phase 5, House 42, Lahore', 'Lahore', '+923022222222', 'Split AC gas refill needed.', 3000.00),
('FH-2026-0003', 4, 3, 10, 'in_progress', '2026-05-15', '11:00:00', 'Clifton Block 9, Flat 302, Karachi', 'Karachi', '+923033333333', 'Kitchen pipe leaking badly.', 1800.00),
('FH-2026-0004', 2, 4, 1, 'confirmed', '2026-05-16', '09:00:00', 'House 15, F-8, Islamabad', 'Islamabad', '+923011111111', 'iPhone 14 screen cracked.', 3000.00),
('FH-2026-0005', 3, 5, 22, 'pending', '2026-05-17', '15:00:00', 'DHA Phase 5, House 42, Lahore', 'Lahore', '+923022222222', 'Setup home Wi-Fi network for 3 floors.', 3500.00);

-- Sample Reviews
INSERT INTO `reviews` (`booking_id`, `user_id`, `technician_id`, `rating`, `comment`, `reply`, `reply_at`) VALUES
(1, 2, 1, 5, 'Excellent work! Usman completed the wiring perfectly and on time. Very professional.', 'Thank you Ahmed! It was a pleasure working with you.', NOW()),
(2, 3, 2, 4, 'Kamran did a great job with the AC. Quick and efficient service.', 'Thanks Sara! Happy to help anytime.', NOW());

-- Sample Notifications
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `link`) VALUES
(2, 'Booking Confirmed', 'Your booking FH-2026-0004 has been confirmed by the technician.', 'booking', '/user/booking-detail.php?id=4'),
(5, 'New Booking Request', 'You have a new booking request from Ahmed Khan.', 'booking', '/technician/bookings.php'),
(1, 'New Technician Application', 'A new technician has applied for approval.', 'system', '/admin/technicians.php');
