-- ========================================================================
-- Roundcube Unified TOTP Plugin - PostgreSQL Schema
-- ========================================================================
-- Version: 1.0.0
-- Description: Database schema for Two-Factor Authentication with 
--              unified TOTP support for alias domains
-- ========================================================================

-- ========================================================================
-- TOTP Secrets Table
-- Stores the Base32-encoded TOTP secrets for each username
-- ========================================================================

CREATE TABLE IF NOT EXISTS totp_secrets (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    secret VARCHAR(255) NOT NULL,
    enabled SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_used TIMESTAMP NULL DEFAULT NULL,
    failed_attempts INTEGER NOT NULL DEFAULT 0,
    locked_until TIMESTAMP NULL DEFAULT NULL,
    
    -- Additional metadata
    last_ip VARCHAR(45) NULL DEFAULT NULL,
    user_agent TEXT NULL DEFAULT NULL,
    
    CONSTRAINT totp_secrets_username_unique UNIQUE (username)
);

-- Indexes for performance
CREATE INDEX idx_totp_secrets_username ON totp_secrets(username);
CREATE INDEX idx_totp_secrets_enabled ON totp_secrets(enabled);
CREATE INDEX idx_totp_secrets_locked ON totp_secrets(locked_until) WHERE locked_until IS NOT NULL;

-- Comments
COMMENT ON TABLE totp_secrets IS 'Stores TOTP secrets keyed by username (without domain)';
COMMENT ON COLUMN totp_secrets.username IS 'Username extracted from email (e.g., "user" from "user@domain.com")';
COMMENT ON COLUMN totp_secrets.secret IS 'Base32-encoded TOTP secret (encrypted in application layer)';
COMMENT ON COLUMN totp_secrets.enabled IS '1 = enabled, 0 = disabled';
COMMENT ON COLUMN totp_secrets.failed_attempts IS 'Count of consecutive failed login attempts';
COMMENT ON COLUMN totp_secrets.locked_until IS 'Account locked until this timestamp (NULL = not locked)';


-- ========================================================================
-- Backup Codes Table
-- One-time use codes for account recovery
-- ========================================================================

CREATE TABLE IF NOT EXISTS totp_backup_codes (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    code VARCHAR(64) NOT NULL,
    used SMALLINT NOT NULL DEFAULT 0,
    used_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Additional metadata
    used_ip VARCHAR(45) NULL DEFAULT NULL,
    
    CONSTRAINT totp_backup_codes_unique UNIQUE (username, code)
);

-- Indexes
CREATE INDEX idx_backup_codes_username ON totp_backup_codes(username);
CREATE INDEX idx_backup_codes_code ON totp_backup_codes(code);
CREATE INDEX idx_backup_codes_used ON totp_backup_codes(used);

-- Foreign key relationship
ALTER TABLE totp_backup_codes 
    ADD CONSTRAINT fk_backup_codes_username 
    FOREIGN KEY (username) 
    REFERENCES totp_secrets(username) 
    ON DELETE CASCADE;

-- Comments
COMMENT ON TABLE totp_backup_codes IS 'One-time use backup codes for account recovery';
COMMENT ON COLUMN totp_backup_codes.code IS 'Hashed backup code';
COMMENT ON COLUMN totp_backup_codes.used IS '1 = used, 0 = unused';


-- ========================================================================
-- Audit Log Table
-- Tracks all 2FA-related events for security auditing
-- ========================================================================

CREATE TABLE IF NOT EXISTS totp_audit_log (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    action VARCHAR(50) NOT NULL,
    success SMALLINT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    details TEXT NULL,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for querying logs
CREATE INDEX idx_audit_log_username ON totp_audit_log(username);
CREATE INDEX idx_audit_log_email ON totp_audit_log(email);
CREATE INDEX idx_audit_log_action ON totp_audit_log(action);
CREATE INDEX idx_audit_log_timestamp ON totp_audit_log(timestamp DESC);
CREATE INDEX idx_audit_log_ip ON totp_audit_log(ip_address);

-- Comments
COMMENT ON TABLE totp_audit_log IS 'Audit trail of all 2FA-related events';
COMMENT ON COLUMN totp_audit_log.action IS 'Event type: enable, disable, login_success, login_fail, etc.';
COMMENT ON COLUMN totp_audit_log.success IS '1 = successful, 0 = failed';
COMMENT ON COLUMN totp_audit_log.details IS 'Additional JSON-encoded details about the event';


-- ========================================================================
-- Verification Cache Table (Optional)
-- Prevents replay attacks by caching recently used codes
-- ========================================================================

CREATE TABLE IF NOT EXISTS totp_verification_cache (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    code VARCHAR(6) NOT NULL,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    
    CONSTRAINT totp_cache_unique UNIQUE (username, code, timestamp)
);

-- Indexes
CREATE INDEX idx_verification_cache_username ON totp_verification_cache(username);
CREATE INDEX idx_verification_cache_expires ON totp_verification_cache(expires_at);

-- Comments
COMMENT ON TABLE totp_verification_cache IS 'Caches recently verified codes to prevent replay attacks';


-- ========================================================================
-- Session Tracking Table (Optional)
-- Tracks active 2FA sessions for additional security
-- ========================================================================

CREATE TABLE IF NOT EXISTS totp_sessions (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    session_id VARCHAR(128) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    verified_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    last_activity TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    active SMALLINT NOT NULL DEFAULT 1
);

-- Indexes
CREATE INDEX idx_totp_sessions_username ON totp_sessions(username);
CREATE INDEX idx_totp_sessions_session_id ON totp_sessions(session_id);
CREATE INDEX idx_totp_sessions_expires ON totp_sessions(expires_at);
CREATE INDEX idx_totp_sessions_active ON totp_sessions(active);

-- Comments
COMMENT ON TABLE totp_sessions IS 'Tracks authenticated 2FA sessions';


-- ========================================================================
-- Functions and Triggers
-- ========================================================================

-- Function to clean up expired verification cache entries
CREATE OR REPLACE FUNCTION cleanup_verification_cache()
RETURNS void AS $$
BEGIN
    DELETE FROM totp_verification_cache
    WHERE expires_at < CURRENT_TIMESTAMP;
END;
$$ LANGUAGE plpgsql;

-- Function to clean up old audit logs
CREATE OR REPLACE FUNCTION cleanup_old_audit_logs(retention_days INTEGER)
RETURNS void AS $$
BEGIN
    DELETE FROM totp_audit_log
    WHERE timestamp < CURRENT_TIMESTAMP - (retention_days || ' days')::INTERVAL;
END;
$$ LANGUAGE plpgsql;

-- Function to clean up expired sessions
CREATE OR REPLACE FUNCTION cleanup_expired_sessions()
RETURNS void AS $$
BEGIN
    UPDATE totp_sessions
    SET active = 0
    WHERE expires_at IS NOT NULL 
    AND expires_at < CURRENT_TIMESTAMP
    AND active = 1;
END;
$$ LANGUAGE plpgsql;

-- Trigger to update last_activity on session access
CREATE OR REPLACE FUNCTION update_session_activity()
RETURNS TRIGGER AS $$
BEGIN
    NEW.last_activity = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_update_session_activity
    BEFORE UPDATE ON totp_sessions
    FOR EACH ROW
    EXECUTE FUNCTION update_session_activity();


-- ========================================================================
-- Views for easier querying
-- ========================================================================

-- Active users with 2FA enabled
CREATE OR REPLACE VIEW v_totp_active_users AS
SELECT 
    username,
    created_at,
    last_used,
    failed_attempts,
    locked_until,
    CASE 
        WHEN locked_until IS NOT NULL AND locked_until > CURRENT_TIMESTAMP 
        THEN 'locked'
        ELSE 'active'
    END as status
FROM totp_secrets
WHERE enabled = 1;

-- Audit log summary
CREATE OR REPLACE VIEW v_totp_audit_summary AS
SELECT 
    username,
    action,
    COUNT(*) as event_count,
    MAX(timestamp) as last_event,
    SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success_count,
    SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failure_count
FROM totp_audit_log
WHERE timestamp > CURRENT_TIMESTAMP - INTERVAL '30 days'
GROUP BY username, action
ORDER BY username, action;

-- Backup codes status
CREATE OR REPLACE VIEW v_totp_backup_codes_status AS
SELECT 
    username,
    COUNT(*) as total_codes,
    SUM(CASE WHEN used = 0 THEN 1 ELSE 0 END) as unused_codes,
    SUM(CASE WHEN used = 1 THEN 1 ELSE 0 END) as used_codes
FROM totp_backup_codes
GROUP BY username;


-- ========================================================================
-- Sample Data (for testing only - remove in production)
-- ========================================================================

-- Uncomment to insert test data
/*
INSERT INTO totp_secrets (username, secret, enabled) VALUES
    ('testuser1', 'JBSWY3DPEHPK3PXP', 1),
    ('testuser2', 'MFRGGZDFMZTWQ2LK', 1);

INSERT INTO totp_audit_log (username, email, action, success, ip_address) VALUES
    ('testuser1', 'testuser1@example.com', 'enable', 1, '192.168.1.100'),
    ('testuser1', 'testuser1@example.com', 'login_success', 1, '192.168.1.100');
*/


-- ========================================================================
-- Grants (adjust according to your database user)
-- ========================================================================

-- Grant permissions to roundcube database user
-- Replace 'roundcube_user' with your actual database user
/*
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO roundcube_user;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO roundcube_user;
GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO roundcube_user;
*/


-- ========================================================================
-- Maintenance
-- ========================================================================

-- Create a scheduled job to clean up old data (example using pg_cron)
/*
-- Install pg_cron extension first: CREATE EXTENSION pg_cron;

-- Clean verification cache every hour
SELECT cron.schedule('cleanup-verification-cache', '0 * * * *', 
    'SELECT cleanup_verification_cache()');

-- Clean old audit logs daily (keep 90 days)
SELECT cron.schedule('cleanup-audit-logs', '0 2 * * *', 
    'SELECT cleanup_old_audit_logs(90)');

-- Clean expired sessions every 15 minutes
SELECT cron.schedule('cleanup-expired-sessions', '*/15 * * * *', 
    'SELECT cleanup_expired_sessions()');
*/


-- ========================================================================
-- Notes
-- ========================================================================

/*
 * IMPORTANT NOTES:
 * 
 * 1. The 'secret' column should be encrypted at the application level
 *    before storing in the database.
 * 
 * 2. The 'username' is extracted from the email address (part before @)
 *    and normalized (usually lowercase) for consistency.
 * 
 * 3. Backup codes should be hashed before storing for security.
 * 
 * 4. Regular maintenance tasks should be scheduled to prevent
 *    database bloat from logs and cache entries.
 * 
 * 5. Consider adding row-level security (RLS) if using PostgreSQL 9.5+
 *    for multi-tenant environments.
 * 
 * 6. Monitor the totp_audit_log table size and implement log rotation.
 * 
 * 7. Create appropriate backups of the totp_secrets table as losing
 *    this data will lock users out of their accounts.
 */

-- ========================================================================
-- Version History
-- ========================================================================

/*
 * 1.0.0 (2024-12-03)
 * - Initial schema creation
 * - Core tables: totp_secrets, totp_backup_codes, totp_audit_log
 * - Optional tables: totp_verification_cache, totp_sessions
 * - Maintenance functions and views
 */