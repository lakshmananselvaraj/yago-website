-- Client-initiated reschedule requests, approved/declined by the trainer.
-- Kept as its own table (not overloading bookings.status again) so the
-- booking's current status/slot stays intact while a request is pending.

CREATE TABLE booking_reschedule_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    requested_slot_date DATE NOT NULL,
    requested_start_time TIME NOT NULL,
    status ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reschedule_request_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_reschedule_request_booking (booking_id)
) ENGINE=InnoDB;
