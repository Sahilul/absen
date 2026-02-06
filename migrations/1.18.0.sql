-- Migration for Version 1.18.0
-- Adding max_attempts to wa_message_queue if not exists

SET @dbname = DATABASE();
SET @tablename = "wa_message_queue";
SET @columnname = "max_attempts";

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE wa_message_queue ADD COLUMN max_attempts INT DEFAULT 3;"
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
