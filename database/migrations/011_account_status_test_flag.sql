-- Adds a 'test' status value so demo/QA accounts can be hidden from public
-- pages and default admin list views without ever deleting data. Existing
-- rows are unaffected (ALTER ... MODIFY on an ENUM keeps current values);
-- specific test-account rows are flagged separately, not by this migration.

ALTER TABLE users
    MODIFY COLUMN status ENUM('active','suspended','pending','test') NOT NULL DEFAULT 'pending';

ALTER TABLE instructors
    MODIFY COLUMN status ENUM('active','inactive','test') NOT NULL DEFAULT 'active';
