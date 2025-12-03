# 🔒 Security Guide

Security best practices and guidelines for Roundcube Unified TOTP plugin.

---

## 🎯 Security Overview

Two-Factor Authentication (2FA) significantly enhances account security, but proper implementation and configuration are crucial. This guide covers security considerations, best practices, and recommendations.

---

## ⚠️ Critical Security Requirements

### 1. HTTPS Only - MANDATORY

**Never use 2FA over HTTP in production!**

```nginx
# Nginx: Force HTTPS redirect
server {
    listen 80;
    server_name mail.example.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name mail.example.com;
    
    ssl_certificate /etc/ssl/certs/mail.example.com.crt;
    ssl_certificate_key /etc/ssl/private/mail.example.com.key;
    
    # Strong SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256';
    ssl_prefer_server_ciphers on;
    
    # ... rest of config
}
```

```apache
# Apache: Force HTTPS redirect
<VirtualHost *:80>
    ServerName mail.example.com
    Redirect permanent / https://mail.example.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName mail.example.com
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/mail.example.com.crt
    SSLCertificateKeyFile /etc/ssl/private/mail.example.com.key
    
    # Strong SSL configuration
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5
    SSLHonorCipherOrder on
    
    # ... rest of config
</VirtualHost>
```

**Why HTTPS is critical:**
- TOTP secrets transmitted during setup must be encrypted
- Verification codes sent during login must be protected
- Session cookies must be secure
- Man-in-the-middle attacks can capture credentials

### 2. Strong Encryption Key

**Generate a cryptographically secure key:**

```bash
# Method 1: OpenSSL (recommended)
openssl rand -base64 32

# Method 2: /dev/urandom
head -c 32 /dev/urandom | base64

# Method 3: pwgen
pwgen -s 32 1
```

**In config.inc.php:**

```php
// ❌ NEVER use weak keys like this
$config['totp_encryption_key'] = 'password123';
$config['totp_encryption_key'] = 'secret';

// ✅ ALWAYS use strong random keys
$config['totp_encryption_key'] = 'T8kZGvN2p7qR5sH9wX3cB6mL4nK1jV8fQ9rS3tU6vW7x';
```

**Key management best practices:**
- Generate new key for each installation
- Never commit encryption key to version control
- Store key securely (consider using environment variables)
- Rotate keys periodically (requires re-encryption)
- Keep secure backups of the encryption key

### 3. File Permissions

**Secure configuration file:**

```bash
# Roundcube plugin config
sudo chmod 600 /var/www/html/roundcube/plugins/totp_unified/config.inc.php
sudo chown www-data:www-data /var/www/html/roundcube/plugins/totp_unified/config.inc.php

# Roundcube main config
sudo chmod 640 /var/www/html/roundcube/config/config.inc.php
sudo chown root:www-data /var/www/html/roundcube/config/config.inc.php
```

**Plugin directory permissions:**

```bash
# Plugin directory
sudo chmod 755 /var/www/html/roundcube/plugins/totp_unified

# PHP files
sudo chmod 644 /var/www/html/roundcube/plugins/totp_unified/*.php

# SQL directory (prevent web access)
sudo chmod 700 /var/www/html/roundcube/plugins/totp_unified/SQL
```

---

## 🛡️ Database Security

### 1. Encryption at Rest

**Encrypt TOTP secrets before storing:**

```php
// Example encryption function (application layer)
function encrypt_secret($secret, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($secret, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decrypt_secret($encrypted_data, $key) {
    list($encrypted, $iv) = explode('::', base64_decode($encrypted_data), 2);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}
```

### 2. Database User Permissions

**Create dedicated database user with minimal privileges:**

```sql
-- MySQL/MariaDB
CREATE USER 'roundcube_totp'@'localhost' IDENTIFIED BY 'strong_password';

-- Grant only necessary privileges
GRANT SELECT, INSERT, UPDATE, DELETE 
ON roundcube.totp_secrets 
TO 'roundcube_totp'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE 
ON roundcube.totp_audit_log 
TO 'roundcube_totp'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE 
ON roundcube.totp_backup_codes 
TO 'roundcube_totp'@'localhost';

FLUSH PRIVILEGES;
```

```sql
-- PostgreSQL
CREATE USER roundcube_totp WITH PASSWORD 'strong_password';

-- Grant minimal privileges
GRANT SELECT, INSERT, UPDATE, DELETE 
ON totp_secrets, totp_audit_log, totp_backup_codes 
TO roundcube_totp;

GRANT USAGE, SELECT ON SEQUENCE 
totp_secrets_id_seq, totp_audit_log_id_seq, totp_backup_codes_id_seq 
TO roundcube_totp;
```

### 3. Database Backups

**Secure backup strategy:**

```bash
#!/bin/bash
# /usr/local/bin/secure_totp_backup.sh

BACKUP_DIR="/var/backups/roundcube/totp"
DATE=$(date +%Y%m%d_%H%M%S)
ENCRYPTION_KEY="your-gpg-key-id"

mkdir -p $BACKUP_DIR

# Backup and encrypt
mysqldump -u root -p'password' roundcube \
    totp_secrets totp_backup_codes totp_audit_log \
    | gzip \
    | gpg --encrypt --recipient $ENCRYPTION_KEY \
    > $BACKUP_DIR/totp_backup_$DATE.sql.gz.gpg

# Set restrictive permissions
chmod 600 $BACKUP_DIR/totp_backup_$DATE.sql.gz.gpg

# Keep only last 30 days
find $BACKUP_DIR -name "totp_backup_*.sql.gz.gpg" -mtime +30 -delete

echo "Encrypted backup completed: totp_backup_$DATE.sql.gz.gpg"
```

---

## 🔐 Authentication Security

### 1. Rate Limiting

**Prevent brute force attacks:**

```php
// In config.inc.php
$config['totp_max_failed_attempts'] = 5;
$config['totp_lockout_duration'] = 900; // 15 minutes
```

**Implement progressive delays:**

```php
// Failed attempts -> Delay
// 1-2 attempts   -> No delay
// 3-4 attempts   -> 2 seconds
// 5+ attempts    -> Lock account for 15 minutes
```

### 2. Time-Based Lockouts

**Automatic lockout after repeated failures:**

```sql
-- Check locked accounts
SELECT username, failed_attempts, locked_until 
FROM totp_secrets 
WHERE locked_until > NOW();

-- Manually unlock account (admin use)
UPDATE totp_secrets 
SET failed_attempts = 0, locked_until = NULL 
WHERE username = 'user1';
```

### 3. Session Management

**Secure session configuration:**

```php
// In Roundcube config.inc.php
$config['session_lifetime'] = 30; // 30 minutes
$config['session_samesite'] = 'Strict';
$config['session_secure'] = true; // HTTPS only
$config['session_httponly'] = true; // No JavaScript access
```

**2FA session timeout:**

```php
// In plugin config
$config['totp_session_timeout'] = 3600; // Re-verify every hour
```

---

## 🚨 Monitoring & Auditing

### 1. Enable Comprehensive Logging

```php
$config['totp_logging'] = true;
$config['totp_log_level'] = 3; // Info, warnings, errors
```

### 2. Monitor Suspicious Activities

**Query suspicious login patterns:**

```sql
-- Failed login attempts in last 24 hours
SELECT username, COUNT(*) as failed_attempts, ip_address
FROM totp_audit_log
WHERE action = 'login_fail'
  AND timestamp > NOW() - INTERVAL 24 HOUR
GROUP BY username, ip_address
HAVING COUNT(*) >= 5
ORDER BY failed_attempts DESC;

-- Multiple IPs for same user
SELECT username, COUNT(DISTINCT ip_address) as ip_count
FROM totp_audit_log
WHERE timestamp > NOW() - INTERVAL 1 HOUR
GROUP BY username
HAVING COUNT(DISTINCT ip_address) > 3;

-- Login attempts outside business hours
SELECT username, ip_address, timestamp
FROM totp_audit_log
WHERE action LIKE 'login_%'
  AND HOUR(timestamp) NOT BETWEEN 6 AND 22
ORDER BY timestamp DESC;
```

### 3. Set Up Alerts

**Email alerts for critical events:**

```php
// In plugin configuration
$config['totp_notify_on_enable'] = true;
$config['totp_notify_on_disable'] = true;
$config['totp_notify_on_failed_attempts'] = true;
$config['totp_admin_notification_email'] = 'admin@example.com';
```

**System monitoring with script:**

```bash
#!/bin/bash
# /usr/local/bin/totp_monitor.sh

# Alert if more than 10 failed attempts in last hour
FAILED_COUNT=$(mysql -u root -p'password' -sN roundcube <<< \
    "SELECT COUNT(*) FROM totp_audit_log 
     WHERE action='login_fail' 
     AND timestamp > NOW() - INTERVAL 1 HOUR")

if [ "$FAILED_COUNT" -gt 10 ]; then
    echo "WARNING: $FAILED_COUNT failed 2FA attempts in last hour" | \
        mail -s "2FA Security Alert" admin@example.com
fi
```

---

## 🔍 Vulnerability Mitigation

### 1. Prevent Replay Attacks

**Use verification cache:**

```php
$config['totp_verification_cache_ttl'] = 30; // Cache for 30 seconds
```

**Cache implementation prevents:**
- Same code being used multiple times
- Replay attacks within time window
- Brute force with valid codes

### 2. Clock Skew Protection

**Limited time tolerance:**

```php
// Allow ±30 seconds only
$config['totp_tolerance'] = 1;

// Never use tolerance > 2 (±60 seconds)
```

**Server time synchronization:**

```bash
# Install NTP
sudo apt-get install ntp

# Configure NTP
sudo systemctl enable ntp
sudo systemctl start ntp

# Force immediate sync
sudo ntpdate -s pool.ntp.org

# Verify time
timedatectl status
```

### 3. Secret Generation Security

**Use cryptographically secure random number generation:**

```php
// ✅ CORRECT: Cryptographically secure
$secret = '';
for ($i = 0; $i < 32; $i++) {
    $secret .= $alphabet[random_int(0, strlen($alphabet) - 1)];
}

// ❌ WRONG: Not cryptographically secure
$secret = '';
for ($i = 0; $i < 32; $i++) {
    $secret .= $alphabet[rand(0, strlen($alphabet) - 1)];
}
```

### 4. SQL Injection Prevention

**Always use prepared statements:**

```php
// ✅ CORRECT: Prepared statement
$query = "SELECT secret FROM totp_secrets WHERE username = ?";
$result = $db->query($query, $username);

// ❌ WRONG: Direct concatenation
$query = "SELECT secret FROM totp_secrets WHERE username = '$username'";
$result = $db->query($query);
```

### 5. XSS Protection

**Escape all user input:**

```php
// In verification form
$username_display = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
echo "<p>Username: $username_display</p>";
```

---

## 🌐 Network Security

### 1. Firewall Rules

**Restrict database access:**

```bash
# UFW (Ubuntu)
sudo ufw allow from 127.0.0.1 to any port 3306 proto tcp

# iptables
sudo iptables -A INPUT -p tcp -s 127.0.0.1 --dport 3306 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 3306 -j DROP
```

### 2. Fail2ban Integration

**Protect against brute force:**

```bash
# /etc/fail2ban/filter.d/roundcube-totp.conf
[Definition]
failregex = TOTP authentication failed for user <HOST>
ignoreregex =
```

```bash
# /etc/fail2ban/jail.local
[roundcube-totp]
enabled = true
port = http,https
filter = roundcube-totp
logpath = /var/log/roundcube/totp.log
maxretry = 5
bantime = 3600
findtime = 600
```

---

## 📱 Backup Codes Security

### 1. Secure Generation

```php
// Generate cryptographically secure backup codes
function generate_backup_code() {
    $bytes = random_bytes(6);
    return strtoupper(bin2hex($bytes)); // 12 character hex
}
```

### 2. Hash Before Storage

```php
// Store hashed version only
function hash_backup_code($code) {
    return password_hash($code, PASSWORD_BCRYPT);
}

// Verify backup code
function verify_backup_code($code, $hash) {
    return password_verify($code, $hash);
}
```

### 3. One-Time Use

```sql
-- Mark as used
UPDATE totp_backup_codes 
SET used = 1, used_at = NOW(), used_ip = ? 
WHERE username = ? AND code = ?;
```

---

## 🔄 Incident Response

### 1. Security Breach Response

**If compromise suspected:**

```bash
# 1. Immediate actions
# - Disable affected accounts
# - Force password resets
# - Rotate encryption keys
# - Review audit logs

# 2. Disable user's 2FA
mysql -u root -p roundcube -e \
    "UPDATE totp_secrets SET enabled=0 WHERE username='compromised_user'"

# 3. Force re-enrollment
mysql -u root -p roundcube -e \
    "DELETE FROM totp_secrets WHERE username='compromised_user'"

# 4. Notify user
# Send email notification about security incident
```

### 2. Key Rotation Procedure

**When rotating encryption keys:**

```php
// 1. Generate new key
$new_key = openssl_rand(32);

// 2. Decrypt with old key, encrypt with new key
function rotate_secrets($old_key, $new_key) {
    $db = get_database_connection();
    $result = $db->query("SELECT username, secret FROM totp_secrets");
    
    while ($row = $result->fetch()) {
        $decrypted = decrypt_secret($row['secret'], $old_key);
        $re_encrypted = encrypt_secret($decrypted, $new_key);
        
        $db->query(
            "UPDATE totp_secrets SET secret = ? WHERE username = ?",
            $re_encrypted,
            $row['username']
        );
    }
}
```

---

## ✅ Security Checklist

### Initial Setup
- [ ] HTTPS configured with valid SSL certificate
- [ ] Strong encryption key generated
- [ ] File permissions set correctly (600 for config)
- [ ] Database user has minimal privileges
- [ ] Audit logging enabled

### Ongoing Maintenance
- [ ] Regular security audits
- [ ] Monitor failed login attempts
- [ ] Review audit logs weekly
- [ ] Keep software updated
- [ ] Test backup restoration
- [ ] Verify time synchronization

### User Education
- [ ] Provide 2FA setup instructions
- [ ] Explain backup codes importance
- [ ] Warn about phishing attempts
- [ ] Document recovery procedures
- [ ] Offer security awareness training

---

## 🚨 Reporting Security Issues

If you discover a security vulnerability:

1. **DO NOT** open a public GitHub issue
2. Email: security@example.com
3. Include:
   - Description of vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if any)

We will respond within 48 hours and work on a fix.

---

## 📚 Additional Resources

- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
- [RFC 6238 - TOTP Specification](https://tools.ietf.org/html/rfc6238)
- [NIST Digital Identity Guidelines](https://pages.nist.gov/800-63-3/)
- [CIS Security Benchmarks](https://www.cisecurity.org/cis-benchmarks/)

---

**Remember: Security is an ongoing process, not a one-time setup!**