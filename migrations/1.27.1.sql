-- v1.27.1 - Performance: add index on users(id_ref, role)
-- Eliminates full table scan (BNL join) on siswa list page query

CREATE INDEX IF NOT EXISTS idx_users_ref_role ON users(id_ref, role);
