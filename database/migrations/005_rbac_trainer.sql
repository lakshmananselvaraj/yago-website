-- ============================================================================
-- Migration 005: RBAC (roles/permissions) + Trainer portal support
--
-- Route-level dashboard access stays on the existing users.role ENUM check
-- (already proven, zero regression risk) — these tables add fine-grained
-- FEATURE permission checks on top, so a future role (e.g. Receptionist) can
-- be granted a subset of admin features via data alone, no code change.
--
-- role slugs are kept identical to the existing users.role ENUM values
-- (admin/instructor/client) so lookups need no translation layer; `name` is
-- the human-facing label the client's spec uses ("Super Admin", "Trainer").
-- ============================================================================

USE vipasa_yoga;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(60) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(60) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

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

-- Access matrix from the client's spec, mapped role-by-role via slug lookups
-- (no hardcoded ids, so re-running against a differently-ordered seed is safe).
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
WHERE r.slug = 'client' AND p.slug IN (
    'book_session', 'join_live_class'
);

-- Client-editable, trainer-readable (only for a client who has an actual
-- booking with that trainer — enforced in application code, not here).
ALTER TABLE client_profiles ADD COLUMN medical_notes TEXT NULL AFTER bio;

-- New pending-trainer-approval step between payment success and confirmed.
ALTER TABLE bookings MODIFY status
    ENUM('pending_payment','awaiting_trainer_approval','confirmed','completed','cancelled','rescheduled')
    NOT NULL DEFAULT 'pending_payment';

-- Trainer-uploaded resources per session. session_feedback (already in
-- schema.sql) already covers session_notes + attendance + a rating/feedback
-- pair for the trainer's private per-session record — only file uploads
-- needed a new table.
CREATE TABLE session_resources (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    instructor_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_session_resource_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_session_resource_instructor FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE CASCADE,
    INDEX idx_session_resource_booking (booking_id)
) ENGINE=InnoDB;
