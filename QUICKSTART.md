# ⚡ Quick Start Guide

Get Roundcube Unified TOTP up and running in 5 minutes!

---

## 🚀 TL;DR - Super Quick Setup

```bash
# 1. Clone to plugins directory
cd /var/www/html/roundcube/plugins/
git clone https://github.com/yourusername/roundcube-unified-totp.git totp_unified

# 2. Create database tables
mysql -u root -p roundcube < totp_unified/SQL/mysql.initial.sql

# 3. Create config
cd totp_unified
cp config.inc.php.dist config.inc.php

# 4. Generate encryption key and edit config
openssl rand -base64 32
nano config.inc.php  # Paste the key into totp_encryption_key

# 5. Enable plugin
nano /var/www/html/roundcube/config/config.inc.php
# Add 'totp_unified' to $config['plugins'] array

# 6. Restart web server
sudo systemctl restart apache2  # or nginx + php-fpm

# Done! 🎉
```

---

## 📝 Detailed Steps

### Step 1: Download Plugin (30 seconds)

**Option A - Git:**
```bash
cd /var/www/html/roundcube/plugins/
git clone https://github.com/yourusername/roundcube-unified-totp.git totp_unified
```

**Option B - Download ZIP:**
```bash
wget https://github.com/yourusername/roundcube-unified-totp/archive/main.zip
unzip main.zip
mv roundcube-unified-totp-main /var/www/html/roundcube/plugins/totp_unified
```

### Step 2: Database Setup (1 minute)

**MySQL/MariaDB:**
```bash
mysql -u root -p roundcube < /var/www/html/roundcube/plugins/totp_unified/SQL/mysql.initial.sql
```

**PostgreSQL:**
```bash
sudo -u postgres psql -d roundcube -f /var/www/html/roundcube/plugins/totp_unified/SQL/postgres.initial.sql
```

### Step 3: Configuration (2 minutes)

```bash
cd /var/www/html/roundcube/plugins/totp_unified

# Copy config template
cp config.inc.php.dist config.inc.php

# Generate secure encryption key
openssl rand -base64 32

# Edit config
nano config.inc.php
```

**Minimum required config:**
```php
<?php
$config['totp_issuer'] = 'My Mail Server';
$config['totp_encryption_key'] = 'PASTE_YOUR_GENERATED_KEY_HERE';
$config['totp_tolerance'] = 1;
?>
```

**Secure the config file:**
```bash
chmod 600 config.inc.php
chown www-data:www-data config.inc.php
```

### Step 4: Enable Plugin (30 seconds)

```bash
nano /var/www/html/roundcube/config/config.inc.php
```

Add `'totp_unified'` to the plugins array:
```php
$config['plugins'] = array(
    'archive',
    'zipdownload',
    'totp_unified',  // Add this line
);
```

### Step 5: Restart & Test (1 minute)

```bash
# Apache
sudo systemctl restart apache2

# Nginx + PHP-FPM
sudo systemctl restart nginx php7.4-fpm
```

**Test it:**
1. Go to `https://mail.yourdomain.com`
2. Login with test account
3. Go to **Settings** → **Server Settings**
4. Find **Two-Factor Authentication** section
5. Click **"Enable 2FA"**
6. Scan QR code with authenticator app
7. Enter verification code
8. Success! ✓

---

## 📱 User Setup (3 minutes)

### For Users Enabling 2FA:

1. **Login** to Roundcube
2. Go to **Settings** (gear icon)
3. Click **Server Settings** tab
4. Find **Two-Factor Authentication** section
5. Click **Enable 2FA** button
6. **Install authenticator app** (if not already installed):
   - [Google Authenticator](https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2) (Android/iOS)
   - [Microsoft Authenticator](https://www.microsoft.com/en-us/account/authenticator) (Android/iOS)
   - [Authy](https://authy.com/download/) (Android/iOS/Desktop)
7. **Scan QR code** with your app
8. **Enter verification code** shown in app
9. **Save backup codes** (important!)
10. **Done!** Next login will require 2FA

### Login with 2FA:

1. Enter **email** and **password** as usual
2. On 2FA screen, open **authenticator app**
3. Find your email account entry
4. Enter the **6-digit code**
5. Click **Verify**
6. Logged in! ✓

---

## 🔥 Common Issues & Quick Fixes

### Issue: "Invalid verification code"

**Fix:** Time synchronization problem

```bash
# Sync server time
sudo ntpdate pool.ntp.org

# Install NTP for automatic sync
sudo apt-get install ntp
sudo systemctl enable ntp
```

### Issue: QR code not showing

**Fix 1:** Use Google Charts API (default)
```php
$config['totp_qr_method'] = 'google';
```

**Fix 2:** Install GD extension for local generation
```bash
sudo apt-get install php-gd
sudo systemctl restart apache2
```

### Issue: Plugin not appearing

**Fix:** Check file permissions
```bash
ls -la /var/www/html/roundcube/plugins/totp_unified/
# Should show: -rw-r--r-- for .php files

# Fix if needed
sudo chown -R www-data:www-data /var/www/html/roundcube/plugins/totp_unified
sudo chmod 755 /var/www/html/roundcube/plugins/totp_unified
```

### Issue: Database error

**Fix:** Verify tables exist
```bash
mysql -u root -p roundcube -e "SHOW TABLES LIKE 'totp_%';"
# Should show: totp_secrets, totp_backup_codes, totp_audit_log

# Re-import if missing
mysql -u root -p roundcube < SQL/mysql.initial.sql
```

---

## 🎯 Testing Checklist

After installation, verify:

- [ ] Plugin appears in Settings
- [ ] Can enable 2FA
- [ ] QR code displays
- [ ] Can scan QR code with app
- [ ] Verification code works
- [ ] Login requires 2FA after enabling
- [ ] Works with multiple email aliases
- [ ] Can disable 2FA

---

## 📚 Next Steps

- **Advanced Configuration**: See [INSTALLATION.md](docs/INSTALLATION.md)
- **Security Hardening**: See [SECURITY.md](docs/SECURITY.md)
- **Troubleshooting**: See [README.md](README.md#troubleshooting)

---

## 💡 Pro Tips

1. **Test with a non-admin account first**
2. **Always save backup codes** in secure location
3. **Enable 2FA for admin accounts** immediately
4. **Use HTTPS** (never HTTP in production)
5. **Keep encryption key secure** and backed up
6. **Monitor audit logs** regularly
7. **Setup scheduled cleanup tasks** for logs

---

## 🆘 Need Help?

- 📖 [Full Documentation](README.md)
- 🐛 [Report Issues](https://github.com/yourusername/roundcube-unified-totp/issues)
- 💬 [Discussions](https://github.com/yourusername/roundcube-unified-totp/discussions)
- 📧 Email: support@example.com

---

**Setup time: ~5 minutes | Your security: Priceless 🔒**