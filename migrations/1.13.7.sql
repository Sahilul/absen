-- Migration: 1.13.7.sql
-- Firebase V1 API Configuration

-- Add Firebase Project ID (get from google-services.json -> project_id)
INSERT INTO pengaturan_aplikasi (name, value, created_at)
SELECT 'firebase_project_id', '', NOW()
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM pengaturan_aplikasi WHERE name = 'firebase_project_id');

-- Add Firebase Service Account path (optional, default: app/config/firebase-service-account.json)
INSERT INTO pengaturan_aplikasi (name, value, created_at)
SELECT 'firebase_service_account_path', '', NOW()
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM pengaturan_aplikasi WHERE name = 'firebase_service_account_path');
