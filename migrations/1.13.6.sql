-- Migration: 1.13.6.sql
-- FCM Server Key setting and Google Auth

-- Add FCM Server Key to pengaturan_aplikasi
INSERT INTO pengaturan_aplikasi (name, value, created_at)
SELECT 'fcm_server_key', '', NOW()
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM pengaturan_aplikasi WHERE name = 'fcm_server_key');

-- Add Google OAuth Client ID
INSERT INTO pengaturan_aplikasi (name, value, created_at)
SELECT 'google_client_id', '', NOW()
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM pengaturan_aplikasi WHERE name = 'google_client_id');

-- Add google_id to users for Google Sign-In
ALTER TABLE users ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) DEFAULT NULL AFTER password;
ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(500) DEFAULT NULL AFTER foto;

-- Index for google_id
CREATE INDEX IF NOT EXISTS idx_users_google_id ON users(google_id);
