-- Trainer's own gallery (Trainer\ProfileController upload/delete) -----------

CREATE TABLE instructor_gallery_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instructor_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_instructor_gallery_instructor FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE CASCADE,
    INDEX idx_instructor_gallery_instructor (instructor_id)
) ENGINE=InnoDB;
