-- ============================================================================
-- Demo seed data (service types, packages, instructors, coupons, admin)
-- Instructor login password for all seeded instructors: Instructor@123
-- Admin login password: Admin@12345
-- ============================================================================

USE vipasa_yoga;

-- RBAC: role/permission catalog + the access matrix from the client's spec.
INSERT INTO roles (slug, name) VALUES
('admin', 'Super Admin'),
('instructor', 'Trainer'),
('client', 'Client');

INSERT INTO permissions (slug, name, category) VALUES
('manage_trainers', 'Manage trainers', 'admin'),
('manage_clients', 'Manage clients', 'admin'),
('book_session', 'Book a session', 'client'),
('manage_packages', 'Manage packages', 'admin'),
('manage_calendar', 'Manage calendar', 'scheduling'),
('manage_own_availability', 'Manage own availability', 'scheduling'),
('accept_booking', 'Accept or reject bookings', 'scheduling'),
('join_live_class', 'Join live class', 'video'),
('conduct_live_class', 'Conduct live class', 'video'),
('view_reports', 'View reports', 'reporting'),
('view_revenue', 'View revenue', 'reporting'),
('manage_payment_settings', 'Manage payment gateway settings', 'admin'),
('manage_website_settings', 'Manage website settings', 'admin'),
('view_activity_logs', 'View activity logs', 'admin'),
('manage_gallery', 'Manage gallery images', 'content');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug = 'admin' AND p.slug IN (
    'manage_trainers', 'manage_clients', 'manage_packages', 'manage_calendar',
    'join_live_class', 'view_reports', 'view_revenue', 'manage_payment_settings',
    'manage_website_settings', 'view_activity_logs', 'manage_gallery'
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug = 'instructor' AND p.slug IN (
    'manage_calendar', 'manage_own_availability', 'accept_booking',
    'join_live_class', 'conduct_live_class', 'view_reports', 'view_revenue',
    'manage_gallery'
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug = 'client' AND p.slug IN ('book_session', 'join_live_class');

INSERT INTO users (name, email, password_hash, role, status, email_verified_at) VALUES
('Vipasa Admin', 'admin@vipasa.demo', '$2y$10$Z1nErVE8dSNWjpJszb2U4utmpyvD04.QvizW0qyOB9QNMxmIZWHai', 'admin', 'active', NOW());

INSERT INTO service_types (slug, name, description, icon, sort_order) VALUES
('one-to-one', 'One to One Session', 'Private personalized yoga session with a dedicated instructor.', 'user', 1),
('group', 'Group Session', 'Live group class with other practitioners.', 'users', 2),
('weekly', 'Weekly Package', 'A structured weekly plan of recurring sessions.', 'calendar-week', 3),
('monthly', 'Monthly Package', 'A full month of guided practice.', 'calendar-month', 4),
('custom', 'Custom Package', 'Tailored session count and duration, built around your goals.', 'sliders', 5);

-- Only the first 3 (is_featured=1) show on the services page initially; the rest
-- stay available for later admin surfacing without needing to re-seed anything.
INSERT INTO packages (service_type_id, name, description, sessions_count, duration_minutes, max_participants, price, currency, is_featured) VALUES
(1, 'Single Session', 'One-on-one 60 minute session.', 1, 60, 1, 1499.00, 'INR', 1),
(1, 'Extended Private Session', 'One-on-one 90 minute deep-dive session.', 1, 90, 1, 2199.00, 'INR', 0),
(2, 'Group Drop-in', 'Single group class, up to 12 participants.', 1, 60, 12, 599.00, 'INR', 0),
(3, 'Weekly Package', '3 sessions per week for one week.', 3, 60, 1, 3999.00, 'INR', 1),
(4, 'Monthly Package', '12 sessions across one month.', 12, 60, 1, 13999.00, 'INR', 1),
(5, 'Custom Wellness Plan', 'Sessions and duration tailored after a consultation.', 4, 60, 1, 5999.00, 'INR', 0);

-- Sample instructors (each backed by a user with role=instructor). Seeded
-- 'inactive' so a fresh install doesn't show fictional teachers publicly —
-- flip to 'active' if you want demo data to browse/book against.
INSERT INTO users (name, email, phone, password_hash, role, status, email_verified_at, phone_verified_at) VALUES
('Ananya Iyer', 'ananya.iyer@vipasa.demo', '+919810000001', '$2y$10$jFWFOehUSdjsD0Amd6yxyem2PRINUVBZJAoQf/hEIJ5QfawM3RoEu', 'instructor', 'active', NOW(), NOW()),
('Rahul Menon', 'rahul.menon@vipasa.demo', '+919810000002', '$2y$10$jFWFOehUSdjsD0Amd6yxyem2PRINUVBZJAoQf/hEIJ5QfawM3RoEu', 'instructor', 'active', NOW(), NOW()),
('Sarah Thomas', 'sarah.thomas@vipasa.demo', '+919810000003', '$2y$10$jFWFOehUSdjsD0Amd6yxyem2PRINUVBZJAoQf/hEIJ5QfawM3RoEu', 'instructor', 'active', NOW(), NOW()),
('Vijaya Parameswaran', 'vijaya.parameswaran@vipasayoga.demo', NULL, '$2y$10$jFWFOehUSdjsD0Amd6yxyem2PRINUVBZJAoQf/hEIJ5QfawM3RoEu', 'instructor', 'active', NOW(), NULL);

INSERT INTO instructors (user_id, headline, bio, experience_years, certificates, specialties, timezone, rating_avg, rating_count, avatar_path, status)
SELECT id, 'Certified Hatha & Vinyasa Instructor', 'Ananya has been teaching yoga for over a decade, blending traditional Hatha with modern Vinyasa flow.', 11,
       JSON_ARRAY('RYT-500', 'Yoga Alliance Certified'), JSON_ARRAY('Hatha', 'Vinyasa', 'Prenatal'), 'Asia/Kolkata', 4.8, 132, '/assets/img/instructors/ananya.jpg', 'inactive'
FROM users WHERE email = 'ananya.iyer@vipasa.demo';

INSERT INTO instructors (user_id, headline, bio, experience_years, certificates, specialties, timezone, rating_avg, rating_count, avatar_path, status)
SELECT id, 'Power Yoga & Strength Coach', 'Rahul specializes in power yoga and strength-focused sequences for athletes and beginners alike.', 7,
       JSON_ARRAY('RYT-200', 'Power Yoga Specialist'), JSON_ARRAY('Power Yoga', 'Core Strength'), 'Asia/Kolkata', 4.6, 84, '/assets/img/instructors/rahul.jpg', 'inactive'
FROM users WHERE email = 'rahul.menon@vipasa.demo';

INSERT INTO instructors (user_id, headline, bio, experience_years, certificates, specialties, timezone, rating_avg, rating_count, avatar_path, status)
SELECT id, 'Restorative & Mindfulness Guide', 'Sarah focuses on restorative yoga, breathwork, and mindfulness practices for stress relief.', 9,
       JSON_ARRAY('RYT-300', 'Mindfulness Based Stress Reduction'), JSON_ARRAY('Restorative', 'Breathwork', 'Meditation'), 'Europe/London', 4.9, 201, '/assets/img/instructors/sarah.jpg', 'inactive'
FROM users WHERE email = 'sarah.thomas@vipasa.demo';

-- The studio's real instructor. Experience-years/rating left at 0 rather than
-- fabricated; adjust once real numbers are known. Availability below is a
-- reasonable starting default — change via the admin Instructors/Calendar
-- panels once her actual schedule is confirmed.
INSERT INTO instructors (user_id, headline, bio, experience_years, certificates, specialties, timezone, rating_avg, rating_count, avatar_path, status)
SELECT id, 'Certified Yoga Teacher & Evaluator',
       'Vijaya is a certified Yoga Teacher and Evaluator, accredited by the Yoga Certification Board under the Certification Scheme for Yoga Professionals, Ministry of AYUSH, Government of India. Her practice blends steady, breath-led sequencing with real attention to how each student is actually doing that day.',
       0, JSON_ARRAY('Yoga Teacher & Evaluator — Yoga Certification Board (Ministry of AYUSH, Govt. of India)'),
       JSON_ARRAY('Hatha Yoga', 'Vinyasa Flow', 'Restorative Yoga'), 'Asia/Kolkata', 0.00, 0, '/assets/img/client/instructor-vijaya.webp', 'active'
FROM users WHERE email = 'vijaya.parameswaran@vipasayoga.demo';

-- Offer every package through every instructor for demo purposes
INSERT INTO instructor_services (instructor_id, package_id)
SELECT i.id, p.id FROM instructors i CROSS JOIN packages p;

-- Recurring weekday availability, Mon-Fri 06:00-11:00 and 16:00-20:00, for every instructor
INSERT INTO instructor_availability (instructor_id, day_of_week, start_time, end_time, is_recurring)
SELECT i.id, d.dow, t.start_time, t.end_time, 1
FROM instructors i
CROSS JOIN (SELECT 1 AS dow UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) d
CROSS JOIN (SELECT '06:00:00' AS start_time, '11:00:00' AS end_time UNION SELECT '16:00:00', '20:00:00') t;

-- Weekend availability, Sat 08:00-12:00
INSERT INTO instructor_availability (instructor_id, day_of_week, start_time, end_time, is_recurring)
SELECT i.id, 6, '08:00:00', '12:00:00', 1 FROM instructors i;

-- A platform-wide holiday (no bookings anywhere on this date) + a per-instructor block
INSERT INTO instructor_blocked_dates (instructor_id, blocked_date, reason) VALUES
(NULL, DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Public Holiday');

INSERT INTO coupons (code, discount_type, discount_value, max_uses, min_order_amount, valid_from, valid_to, is_active) VALUES
('WELCOME10', 'percent', 10.00, 500, 0, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), 1),
('FLAT200', 'flat', 200.00, 200, 1000, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), 1);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Vipasa Yoga'),
('tax_percent', '5'),
('default_currency', 'INR'),
('default_timezone', 'Asia/Kolkata');
