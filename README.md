# 🔐 Roundcube Unified TOTP plugin

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-8892BF.svg)](https://www.php.net/)
[![Roundcube](https://img.shields.io/badge/Roundcube-%3E%3D1.5-37BEFF.svg)](https://roundcube.net/)

**Two-Factor Authentication plugin για Roundcube με unified TOTP support για alias domains**

Το **Roundcube Unified TOTP** είναι ένα plugin που προσθέτει Two-Factor Authentication (2FA) στο Roundcube webmail με σκοπό να προσφέρει τη δυνατότητα να υπάρχει **ένα κοινό TOTP secret για πολλαπλά email domain aliases**. Οι χρήστες χρειάζονται μόνο ένα QR code στο authenticator app τους, ανεξάρτητα από το πόσα alias domains χρησιμοποιούν.

---

## ✨ Χαρακτηριστικά

- 🎯 **Unified TOTP**: Ένα secret για όλα τα alias domains.
- 📱 **Universal Compatibility**: Λειτουργεί με Google Authenticator, Microsoft Authenticator, Authy, FreeOTP, και όλα τα TOTP apps.
- 🔒 **Secure**: Encrypted secrets στη βάση, rate limiting, audit logging.
- 🌍 **Multilingual**: Υποστήριξη για πολλές γλώσσες (Αγγλικά, Ελληνικά).
- ⚡ **Easy Setup**: QR code generation και εύκολη διαχείριση από το Roundcube UI.
- 🕐 **Clock Skew Tolerance**: Αυτόματη αντιμετώπιση clock drift.
- 📊 **Admin Features**: Audit logs, backup codes (optional).

---

## 🎯 Πώς Λειτουργεί

### Περιγραφή του προβλήματος με τα 'παραδοσιακά' TOTP
Παραδοσιακά, κάθε email address (συμπεριλαμβανομένων των aliases) θα χρειαζόταν ξεχωριστό TOTP secret:

```
username@domain1.com    → Secret A → OTP 123456
username@alias1.com     → Secret B → OTP 789012
username@alias2.com     → Secret C → OTP 345678
```

Αυτό δημιουργεί **πολλαπλά QR codes** και σύγχυση στους χρήστες.

### Η Λύση του προβλήματος
Το Unified TOTP εξάγει το **username** (χωρίς το domain) και το χρησιμοποιεί ως βάση:

```
username@domain1.com  ─┐
username@alias1.com   ├──► "username" ──► Secret ABC ──► OTP 123456
username@alias2.com   ─┘
```

**Αποτέλεσμα**: Ένα QR code, ένα OTP για όλα τα aliases.

---

## 📋 Απαιτήσεις

- **PHP**: ≥ 7.4
- **Roundcube**: ≥ 1.5
- **Database**: MySQL/MariaDB ή PostgreSQL
- **PHP Extensions**: 
  - `hash` (HMAC support)
  - `openssl` (για encryption)
  - `gd` ή `imagick` (για QR code generation - optional)

---

## 🚀 Εγκατάσταση

### Βήμα 1: Κάνε Download το Plugin

```bash
cd /path/to/roundcube/plugins/
git clone https://github.com/yourusername/roundcube-unified-totp.git totp_unified
```

### Βήμα 2: Δημιουργία Database Tables

**Για MySQL/MariaDB:**
```bash
mysql -u root -p roundcube < plugins/totp_unified/SQL/mysql.initial.sql
```

**Για PostgreSQL:**
```bash
psql -U postgres -d roundcube -f plugins/totp_unified/SQL/postgres.initial.sql
```

### Βήμα 3: Configuration

Αντιγραφή του configuration file:
```bash
cd plugins/totp_unified/
cp config.inc.php.dist config.inc.php
```

Επεξεργασία του `config.inc.php`:
```php
<?php
// Όνομα που θα εμφανίζεται στο authenticator app
$config['totp_issuer'] = 'MyMailServer';

// Encryption key (δημιούργησε με: openssl rand -base64 32)
$config['totp_encryption_key'] = 'YOUR_RANDOM_32_BYTE_KEY_HERE';

// Time tolerance για clock skew (προτεινόμενο: 1)
$config['totp_tolerance'] = 1;

// Optional: Υποχρεωτικό 2FA για συγκεκριμένα domains
$config['totp_required_domains'] = array(
    // 'domain1.com',
);
?>
```

**⚠️ ΣΗΜΑΝΤΙΚΟ**: Δημιούργησε ένα ασφαλές encryption key:
```bash
openssl rand -base64 32
```

### Βήμα 4: Ενεργοποίηση του Plugin

Προσθήκη στο `config/config.inc.php`:
```php
$config['plugins'] = array(
    'totp_unified',
    // ... άλλα plugins
);
```

### Βήμα 5: Restart Roundcube

```bash
# Apache
sudo systemctl restart apache2

# Nginx + PHP-FPM
sudo systemctl restart php7.4-fpm nginx
```

---

## 📖 Χρήση

### Για Χρήστες

#### Ενεργοποίηση 2FA

1. Login στο Roundcube
2. Πήγαινε στο **Settings** → **Server Settings**
3. Βρες την ενότητα **Two-Factor Authentication**
4. Κλικ στο **"Enable 2FA"**
5. Σκανάρισε το QR code με το authenticator app σου:
   - Google Authenticator (Android/iOS)
   - Microsoft Authenticator (Android/iOS)
   - Authy (Android/iOS/Desktop)
   - FreeOTP (Android/iOS)
6. Εισήγαγε τον 6-ψήφιο κωδικό για επαλήθευση
7. Το 2FA είναι έτοιμο και ενεργό. ✓

#### Login με 2FA

1. Εισήγαγε το email και password σου όπως συνήθως
2. Άνοιξε το authenticator app σου
3. Εισήγαγε τον 6-ψήφιο κωδικό που βλέπεις
4. Κλικ **"Verify"**
5. Login επιτυχημένο. ✓

#### Χρήση με Alias Domains

Το ίδιο OTP λειτουργεί για **όλα** τα aliases σου:

```
Login: username@domain1.com     → OTP: 123456 ✓
Login: username@alias1.com      → OTP: 123456 ✓
Login: username@alias2.com      → OTP: 123456 ✓
```

Δεν χρειάζεται να σκανάρεις πολλαπλά QR codes.

### Για Administrators

#### Υποχρεωτικό 2FA για Domains

Στο `config.inc.php`:
```php
$config['totp_required_domains'] = array(
    'secure-domain.com',
    'executive.company.com'
);
```

#### IP Whitelist (Skip 2FA)

```php
$config['totp_whitelist_ips'] = array(
    '192.168.1.0/24',  // Local network
    '10.0.0.100',       // Specific IP
);
```

#### Audit Logging

```php
$config['totp_logging'] = true;
```

Logs αποθηκεύονται στο table `totp_audit_log`:
```sql
SELECT * FROM totp_audit_log 
WHERE username = 'john' 
ORDER BY timestamp DESC 
LIMIT 10;
```

---

## 🏗️ Αρχιτεκτονική

### Database Schema

```sql
CREATE TABLE totp_secrets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    secret VARCHAR(255) NOT NULL,
    enabled TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used TIMESTAMP NULL,
    failed_attempts INT DEFAULT 0,
    INDEX idx_username (username)
);
```

### TOTP Algorithm Flow

```
Secret Key (Base32) + Current Time (Unix timestamp / 30)
    ↓
HMAC-SHA1
    ↓
Dynamic Truncation
    ↓
6-digit OTP Code
```

### Username Extraction

```php
username@domain1.com  →  extract_username()  →  "username"
username@alias1.com   →  extract_username()  →  "username"
username@alias2.com   →  extract_username()  →  "username"
                                 ↓
                    Lookup in database by "username"
                                 ↓
                         Same TOTP secret
```

---

## 🧪 Testing

### Python Test Script

Χρησιμοποίησε το included test script για να δοκιμάσεις τη λογική:

```bash
# Install dependencies
pip install qrcode[pil]

# Run test
python tests/totp_test.py
```

Το script δείχνει:
- Username extraction
- TOTP generation
- Verification με time tolerance
- QR code generation
- Database structure simulation

### Manual Testing

1. Ενεργοποίησε 2FA για ένα test account
2. Πρόσθεσε το QR code στο authenticator app
3. Δοκίμασε login με διαφορετικά aliases
4. Επαλήθευσε ότι το ίδιο OTP δουλεύει παντού

---

## 🔐 Ασφάλεια

### Best Practices

- ✅ **Encrypt secrets**: Χρήση `totp_encryption_key` στη configuration
- ✅ **HTTPS Only**: Το 2FA πρέπει να χρησιμοποιείται μόνο με SSL/TLS
- ✅ **Rate Limiting**: Automatic lockout μετά από 5 αποτυχημένες προσπάθειες
- ✅ **Time Tolerance**: Μόνο ±30 seconds tolerance
- ✅ **Audit Logs**: Καταγραφή όλων των 2FA events
- ✅ **Backup Codes**: Optional για recovery

### Security Considerations

- Το secret **δεν** αποθηκεύεται σε plaintext
- Χρήση HMAC-SHA1 (RFC 6238 standard)
- Protection κατά brute force attacks
- Session management με timeouts

---

## 🛠️ Troubleshooting

### "Invalid verification code"

**Πιθανές αιτίες:**
1. **Clock skew**: Ο server clock διαφέρει από το device
   - **Λύση**: Sync τον server με NTP
   ```bash
   sudo ntpdate pool.ntp.org
   ```

2. **Wrong time zone**:
   - **Λύση**: Ρύθμισε το `date.timezone` στο `php.ini`
   ```ini
   date.timezone = "Europe/Athens"
   ```

3. **Secret mismatch**:
   - **Λύση**: Re-generate το QR code

### "2FA not working for alias"

**Έλεγχος:**
```sql
-- Verify username extraction
SELECT username, secret, enabled 
FROM totp_secrets 
WHERE username = 'your_username';

-- Should return ONE row με το username χωρίς @domain
```

### QR Code not displaying

**Λύση 1**: Χρήση Google Charts API
```php
$config['totp_qr_method'] = 'google';
```

**Λύση 2**: Local generation (requires GD/Imagick)
```php
$config['totp_qr_method'] = 'local';
```

---

## 📚 Τεχνική Τεκμηρίωση

### TOTP Standards

- **RFC 6238**: TOTP Time-Based One-Time Password Algorithm
- **RFC 4648**: Base32 Encoding
- **RFC 2104**: HMAC-SHA1

### API Reference

#### `extract_username($email)`
Εξάγει το username από email address.

```php
extract_username('user@domain.com')  // Returns: 'user'
extract_username('user@alias.com')   // Returns: 'user'
```

#### `generate_secret($length = 32)`
Δημιουργεί Base32 encoded secret.

```php
$secret = generate_secret(32);  // Returns: 'JBSWY3DPEHPK3PXP...'
```

#### `calculate_totp($secret, $time = null)`
Υπολογίζει 6-ψήφιο OTP.

```php
$otp = calculate_totp($secret);  // Returns: '123456'
```

#### `verify_otp($otp, $timestamp = null, $tolerance = 1)`
Επαληθεύει OTP με time tolerance.

```php
$valid = verify_otp('123456', time(), 1);  // Returns: true/false
```

---

## 🌍 Localization

### Υποστηριζόμενες Γλώσσες

- 🇬🇧 English (en_US)
- 🇬🇷 Ελληνικά (el_GR)

### Προσθήκη Νέας Γλώσσας

1. Δημιούργησε `localization/XX_YY.inc`:
```php
<?php
$labels = array();
$labels['2fa_title'] = 'Your Translation';
// ...
?>
```

2. Συνεισφορά στο project:
```bash
git add localization/XX_YY.inc
git commit -m "Add XX_YY translation"
git push
```

---

## 🤝 Contributing

Contributions are welcome! 

### Development Setup

```bash
# Clone repository
git clone https://github.com/yourusername/roundcube-unified-totp.git
cd roundcube-unified-totp

# Install dev dependencies
composer install --dev

# Run tests
./vendor/bin/phpunit tests/
```

### Contribution Guidelines

1. Fork το repository
2. Δημιούργησε feature branch (`git checkout -b feature/amazing-feature`)
3. Commit τις αλλαγές σου (`git commit -m 'Add amazing feature'`)
4. Push στο branch (`git push origin feature/amazing-feature`)
5. Άνοιξε Pull Request

---

## 📄 License

Αυτό το project είναι licensed under the **GNU General Public License v3.0**.

Δες το [LICENSE](LICENSE) file για λεπτομέρειες.

---

## 🙏 Acknowledgments

- Roundcube Development Team
- TOTP/HOTP Algorithm (RFC 6238, RFC 4226)
- Contributors και community

---

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/yourusername/roundcube-unified-totp/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/roundcube-unified-totp/discussions)
- **Email**: support@yourdomain.com

---

## 🗺️ Roadmap

- [ ] WebAuthn/FIDO2 support
- [ ] SMS backup authentication
- [ ] Admin panel για bulk management
- [ ] Mobile app για QR code scanning
- [ ] Integration με LDAP/Active Directory
- [ ] Multi-language support expansion

---

<div align="center">

**⭐ Αν σου αρέσει αυτό το project, δώσε του ένα star! ⭐**

Made with ❤️ for the Roundcube community

</div>