-- Adds reminder-sent tracking to bookings so the reminder-email CLI script
-- (bin/send-reminders.php) doesn't re-send a reminder for the same booking
-- on every run. Already merged into database/schema.sql for fresh installs;
-- this file is only needed to bring an existing dev database up to date.

ALTER TABLE bookings ADD COLUMN reminder_sent_at DATETIME NULL AFTER notes;
