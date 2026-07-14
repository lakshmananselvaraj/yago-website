-- Trainer certificate file/image uploads (distinct from the existing comma-separated
-- certificates text list on instructors.certificates) --

CREATE TABLE instructor_certificate_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instructor_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_instructor_cert_file_instructor FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE CASCADE,
    INDEX idx_instructor_cert_file_instructor (instructor_id)
) ENGINE=InnoDB;
