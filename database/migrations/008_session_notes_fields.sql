-- Add Recommendations + Homework fields to trainer session notes --

ALTER TABLE session_feedback
    ADD COLUMN recommendation TEXT NULL AFTER session_notes,
    ADD COLUMN homework TEXT NULL AFTER recommendation;
