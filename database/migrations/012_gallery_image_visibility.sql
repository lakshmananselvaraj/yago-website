-- Visibility flag for gallery photos, following the same non-destructive
-- pattern as migration 011: hide test/placeholder uploads from the public
-- gallery without ever deleting the row.

ALTER TABLE gallery_images
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER sort_order;
