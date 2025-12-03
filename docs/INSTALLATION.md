# 📦 Installation Guide

Complete installation guide for Roundcube Unified TOTP plugin.

---

## 📋 Prerequisites

Before installing, ensure your system meets these requirements:

### System Requirements

- **PHP**: 7.4 or higher (PHP 8.0+ recommended)
- **Roundcube**: 1.5.0 or higher
- **Database**: MySQL 5.7+ / MariaDB 10.2+ / PostgreSQL 10+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **HTTPS**: SSL/TLS certificate (required for production)

### PHP Extensions

```bash
# Check if required extensions are installed
php -m | grep -E 'hash|openssl'

# For Debian/Ubuntu
sudo apt-get install php-cli php-openssl

# For CentOS/RHEL
sudo yum install php-cli php-openssl

# Optional (for local QR code generation)
sudo apt-get install php-gd
# OR
sudo apt-get install php-imagick
```

---

## 🚀 Installation Methods

### Method 1: Git Clone (Recommended for Development)

```bash
# Navigate to Roundcube plugins directory
cd /var/www/html/roundcube/plugins/

# Clone the repository
git clone https://github.com/yourusername/roundcube-unified-totp.git totp_unified

# Set proper permissions
sudo chown -R www-data:www-data totp_unified
sudo chmod -R 755 totp_unified
```

### Method 2: Manual Download

```bash
# Download the latest release
cd /tmp
wget https://github.com/yourusername/roundcube-unified-totp/archive/refs/tags/v1.0.0.tar.gz

# Extract to plugins directory
tar -xzf v1.0.0.tar.gz
sudo mv roundcube-unified-totp-1.0.0 /var/www/html/roundcube/plugins/totp_unified

# Set permissions
sudo chown -R www-data:www-data /var/www/html/roundcube/plugins/totp_unified
sudo chmod -R 755 /var/www/html/roundcube/plugins/totp_unified
```

### Method 3: Composer (Future Support)

```bash
# Will be available in future versions
cd /var/www/html/roundcube
composer require roundcube/unified-totp
```

---

## 🗄️ Database Setup

### MySQL/MariaDB

```bash
# Login to MySQL
mysql -u root -p

# Create database if it doesn't exist (usually Roundcube database already exists)
# CREATE DATABASE roundcube;

# Use the Roundcube database
USE roundcube;

# Import the schema
SOURCE /var/www/html/roundcube/plugins/totp_unified/SQL/mysql.initial.sql;

# Verify tables were created
SHOW TABLES LIKE 'totp_%';

# Exit MySQL
EXIT;
```

**Alternative method using command line:**

```bash
mysql -u root -p roundcube < /var/www/html/roundcube/plugins/totp_unified/SQL/mysql.initial.sql
```

### PostgreSQL

```bash
# Login to PostgreSQL
sudo -u postgres psql

# Switch to Roundcube database
\c roundcube

# Import the schema
\i /var/www/html/roundcube/plugins/totp_unified/SQL/postgres.initial.sql

# Verify tables were created
\dt totp_*

# Exit PostgreSQL
\q
```

**Alternative method using command line:**

```bash
sudo -u postgres psql -d roundcube -f /var/www/html/roundcube/plugins/totp_unified/SQL/postgres.initial.sql
```

---

## ⚙️ Configuration

### Step 1: Copy Configuration Template

```bash
cd /var/www/html/roundcube/plugins/totp_unified
cp config.inc.php.dist config.inc.php
```

### Step 2: Generate Encryption Key

**CRITICAL**: Generate a strong encryption key for securing TOTP secrets:

```bash
openssl rand -base64 32
```

Example output:
```
T8kZGvN2p7qR5sH9wX3cB6mL4nK1jV8f
```

### Step 3: Edit Configuration

```bash
nano config.inc.php
```

**Minimum required configuration:**

```php
<?php

// Issuer name (appears in authenticator apps)
$config['totp_issuer'] = 'My Mail Server';

// CRITICAL: Paste your generated encryption key here
$config['totp_encryption_key'] = 'T8kZGvN2p7qR5sH9wX3cB6mL4nK1jV8f';

// Time tolerance (recommended: 1)
$config['totp_tolerance'] = 1;

// Enable audit logging
$config['totp_logging'] = true;

?>
```

### Step 4: Secure Configuration File

```bash
# Set restrictive permissions
sudo chmod 600 config.inc.php
sudo chown www-data:www-data config.inc.php
```

---

## 🔌 Enable Plugin in Roundcube

### Edit Roundcube Configuration

```bash
sudo nano /var/www/html/roundcube/config/config.inc.php
```

### Add Plugin to Configuration

Find the `$config['plugins']` array and add `'totp_unified'`:

```php
$config['plugins'] = array(
    'archive',
    'zipdownload',
    'totp_unified',  // Add this line
    // ... other plugins
);
```

**Complete example:**

```php
<?php

/* Local configuration for Roundcube Webmail */

// Database connection
$config['db_dsnw'] = 'mysql://roundcube:password@localhost/roundcube';

// Plugins
$config['plugins'] = array(
    'archive',
    'zipdownload',
    'totp_unified',
);

// Other configurations...
?>
```

---

## 🌐 Web Server Configuration

### Apache

If using Apache with `.htaccess`, no changes needed.

**Optional**: Add security headers in Apache config:

```apache
<Directory /var/www/html/roundcube/plugins/totp_unified>
    Options -Indexes
    AllowOverride None
    Require all granted
    
    # Protect config file
    <Files "config.inc.php">
        Require all denied
    </Files>
</Directory>
```

### Nginx

Add this to your Roundcube Nginx configuration:

```nginx
location ~ /plugins/totp_unified/config\.inc\.php$ {
    deny all;
}

location /plugins/totp_unified/ {
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### Restart Web Server

```bash
# Apache
sudo systemctl restart apache2

# Nginx + PHP-FPM
sudo systemctl restart nginx
sudo systemctl restart php7.4-fpm
```

---

## ✅ Verify Installation

### Step 1: Check Plugin Status

1. Login to Roundcube as administrator
2. Go to **Settings** → **About**
3. Look for `totp_unified` in the plugins list

### Step 2: Test Database Connection

```bash
# MySQL
mysql -u root -p -e "USE roundcube; SELECT COUNT(*) FROM totp_secrets;"

# PostgreSQL
sudo -u postgres psql -d roundcube -c "SELECT COUNT(*) FROM totp_secrets;"
```

Expected output: `0` (no users have enabled 2FA yet)

### Step 3: Check PHP Logs

```bash
# Check for any errors
sudo tail -f /var/log/apache2/error.log
# OR
sudo tail -f /var/log/nginx/error.log
```

### Step 4: Test 2FA Setup

1. Login to Roundcube with a test account
2. Go to **Settings** → **Server Settings**
3. Find **Two-Factor Authentication** section
4. Click **"Enable 2FA"**
5. QR code should appear
6. Scan with authenticator app
7. Enter verification code
8. Should show "2FA enabled successfully"

---

## 🔧 Post-Installation Configuration

### Optional: Setup Scheduled Tasks

For database cleanup and maintenance:

**Cron job (MySQL/MariaDB):**

```bash
sudo crontab -e
```

Add these lines:

```cron
# Clean old audit logs (keep 90 days) - runs daily at 2 AM
0 2 * * * mysql -u root -p'password' roundcube -e "DELETE FROM totp_audit_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL 90 DAY);" > /dev/null 2>&1

# Clean expired verification cache - runs hourly
0 * * * * mysql -u root -p'password' roundcube -e "DELETE FROM totp_verification_cache WHERE expires_at < NOW();" > /dev/null 2>&1

# Clean expired sessions - runs every 15 minutes
*/15 * * * * mysql -u root -p'password' roundcube -e "UPDATE totp_sessions SET active = 0 WHERE expires_at < NOW() AND active = 1;" > /dev/null 2>&1
```

**PostgreSQL with pg_cron:**

```sql
-- Enable pg_cron extension
CREATE EXTENSION IF NOT EXISTS pg_cron;

-- Schedule cleanup tasks
SELECT cron.schedule('cleanup-audit-logs', '0 2 * * *', 
    'DELETE FROM totp_audit_log WHERE timestamp < NOW() - INTERVAL ''90 days''');

SELECT cron.schedule('cleanup-cache', '0 * * * *', 
    'DELETE FROM totp_verification_cache WHERE expires_at < NOW()');

SELECT cron.schedule('cleanup-sessions', '*/15 * * * *', 
    'UPDATE totp_sessions SET active = 0 WHERE expires_at < NOW() AND active = 1');
```

### Optional: Setup Backup

**Backup TOTP secrets table:**

```bash
# MySQL
mysqldump -u root -p roundcube totp_secrets > totp_secrets_backup.sql

# PostgreSQL
sudo -u postgres pg_dump -t totp_secrets roundcube > totp_secrets_backup.sql
```

**Automate daily backups:**

```bash
#!/bin/bash
# Save as /usr/local/bin/backup_totp.sh
BACKUP_DIR="/var/backups/roundcube/totp"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# MySQL
mysqldump -u root -p'password' roundcube totp_secrets totp_backup_codes totp_audit_log | gzip > $BACKUP_DIR/totp_backup_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "totp_backup_*.sql.gz" -mtime +30 -delete

echo "Backup completed: totp_backup_$DATE.sql.gz"
```

Make it executable and add to cron:

```bash
sudo chmod +x /usr/local/bin/backup_totp.sh
sudo crontab -e
```

Add:
```cron
0 3 * * * /usr/local/bin/backup_totp.sh >> /var/log/totp_backup.log 2>&1
```

---

## 🐛 Troubleshooting Installation

### Issue: Plugin doesn't appear in Roundcube

**Solution:**
```bash
# Check file permissions
ls -la /var/www/html/roundcube/plugins/totp_unified/

# Should show:
# -rw-r--r-- totp_unified.php
# drwxr-xr-x SQL/
# etc.

# Fix if needed
sudo chown -R www-data:www-data /var/www/html/roundcube/plugins/totp_unified
sudo chmod 755 /var/www/html/roundcube/plugins/totp_unified
sudo chmod 644 /var/www/html/roundcube/plugins/totp_unified/*.php
```

### Issue: Database tables not created

**Solution:**
```bash
# Verify SQL file exists
ls -la /var/www/html/roundcube/plugins/totp_unified/SQL/

# Re-import manually
mysql -u root -p roundcube < SQL/mysql.initial.sql

# Check for errors
mysql -u root -p roundcube -e "SHOW TABLES LIKE 'totp_%';"
```

### Issue: QR code not displaying

**Solution 1**: Use Google Charts API (default)
```php
$config['totp_qr_method'] = 'google';
```

**Solution 2**: Install GD extension
```bash
sudo apt-get install php-gd
sudo systemctl restart apache2
```

### Issue: "Invalid encryption key" error

**Solution:**
```bash
# Generate new key
openssl rand -base64 32

# Update config.inc.php with new key
nano plugins/totp_unified/config.inc.php
```

---

## 📚 Next Steps

After successful installation:

1. **Read** [CONFIGURATION.md](CONFIGURATION.md) for advanced settings
2. **Review** [SECURITY.md](SECURITY.md) for security best practices
3. **Test** 2FA with multiple user accounts
4. **Enable** required domains if needed
5. **Setup** backup codes for users
6. **Monitor** audit logs for suspicious activity

---

## 🆘 Getting Help

If you encounter issues:

1. Check [Troubleshooting section](#-troubleshooting-installation)
2. Review Roundcube logs: `/var/log/apache2/error.log`
3. Check PHP logs: `/var/log/php7.4-fpm.log`
4. Open an issue: [GitHub Issues](https://github.com/yourusername/roundcube-unified-totp/issues)
5. Join discussions: [GitHub Discussions](https://github.com/yourusername/roundcube-unified-totp/discussions)

---

## ✅ Installation Checklist

- [ ] System requirements met
- [ ] Plugin files copied to correct location
- [ ] Database tables created
- [ ] Configuration file created and edited
- [ ] Encryption key generated and set
- [ ] Plugin enabled in Roundcube config
- [ ] Web server restarted
- [ ] File permissions set correctly
- [ ] Test account can enable 2FA
- [ ] QR code displays correctly
- [ ] Verification code works
- [ ] Scheduled cleanup tasks configured (optional)
- [ ] Backup strategy implemented (optional)

---

**Installation complete! 🎉**

Your Roundcube instance now has Two-Factor Authentication with unified TOTP support!