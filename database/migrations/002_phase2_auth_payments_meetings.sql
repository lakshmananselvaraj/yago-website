-- Phase 2: Google OAuth, simplified profile, featured packages, Razorpay, meeting links, admin.
USE vipasa_yoga;

-- Auth: Google sign-in support, password becomes optional.
ALTER TABLE users
    MODIFY password_hash VARCHAR(255) NULL,
    ADD COLUMN google_id VARCHAR(64) NULL UNIQUE AFTER phone;

-- Profile: drop the old wellness-intake fields, add the simplified spec's fields.
ALTER TABLE client_profiles
    DROP COLUMN yoga_experience,
    DROP COLUMN fitness_goals,
    DROP COLUMN health_conditions,
    DROP COLUMN emergency_contact_name,
    DROP COLUMN emergency_contact_phone,
    ADD COLUMN age TINYINT UNSIGNED NULL AFTER date_of_birth,
    ADD COLUMN country VARCHAR(100) NULL AFTER gender,
    ADD COLUMN bio VARCHAR(200) NULL AFTER country;

-- Packages: mark exactly 3 as featured (shown on the services page initially).
ALTER TABLE packages
    ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active;

UPDATE packages SET name = 'Single Session', is_featured = 1 WHERE name = 'Single Private Session';
UPDATE packages SET name = 'Weekly Package', is_featured = 1 WHERE name = 'Weekly Flow Package';
UPDATE packages SET name = 'Monthly Package', is_featured = 1 WHERE name = 'Monthly Transformation';

-- Payments: Razorpay needs both an order id (upfront) and a payment id (post-payment, already gateway_txn_id).
ALTER TABLE payments
    ADD COLUMN gateway_order_id VARCHAR(150) NULL AFTER booking_id;

-- Email verification via a clickable link (not OTP) — mirrors password_resets.
CREATE TABLE email_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_emailverify_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_emailverify_user (user_id)
) ENGINE=InnoDB;

-- Meeting links: admin pastes an externally-created Google Meet/Zoom link per booking.
CREATE TABLE meeting_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL UNIQUE,
    provider ENUM('google_meet','zoom') NOT NULL,
    url VARCHAR(500) NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_meetinglink_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_meetinglink_admin FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
