-- Migration: 1.15.1 - Resize no_wa column for Legacy Group IDs
-- Date: 2026-01-20
-- Description: Increase column size to support long Group IDs (e.g. 628xxx-14xxx@g.us)

ALTER TABLE wa_message_queue MODIFY COLUMN no_wa VARCHAR(100) NOT NULL;
ALTER TABLE wa_message_log MODIFY COLUMN no_wa VARCHAR(100) NOT NULL;
