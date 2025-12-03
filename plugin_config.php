<?php

/**
 * Configuration file για το totp_unified plugin
 * Αποθήκευσε αυτό το αρχείο ως: plugins/totp_unified/config.inc.php
 */

// Όνομα εφαρμογής που θα εμφανίζεται στο authenticator app
$config['totp_issuer'] = 'MyMailServer';

// Μήκος TOTP code (6 ή 8 ψηφία)
$config['totp_digits'] = 6;

// Time step σε seconds (συνήθως 30)
$config['totp_period'] = 30;

// Time tolerance για clock skew (±N time steps)
// Προτείνεται 1 για ασφάλεια και 2 για compatibility
$config['totp_tolerance'] = 1;

// Μήκος secret σε characters (Base32)
// Προτείνεται τουλάχιστον 32 για καλή ασφάλεια
$config['totp_secret_length'] = 32;

// Encryption key για το secret στη βάση (ΠΡΟΣΟΧΗ: Κράτησέ το ασφαλές!)
// Δημιούργησε ένα μοναδικό key με: openssl rand -base64 32
$config['totp_encryption_key'] = 'YOUR_RANDOM_ENCRYPTION_KEY_HERE';

// Database table name
$config['totp_table_name'] = 'totp_secrets';

// Εμφάνιση QR code με Google Charts API ή local generation
$config['totp_qr_method'] = 'google'; // 'google' ή 'local'

// Απαιτείται 2FA για όλους τους χρήστες;
$config['totp_required_for_all'] = false;

// Domains που απαιτούν υποχρεωτικά 2FA
$config['totp_required_domains'] = array(
    // 'domain1.com',
    // 'domain2.com'
);

// Whitelist IPs που δεν χρειάζονται 2FA
$config['totp_whitelist_ips'] = array(
    // '192.168.1.100',
    // '10.0.0.0/8'
);

// Logging enabled
$config['totp_logging'] = true;

// Session timeout μετά από επιτυχή 2FA (σε seconds)
// 0 = μέχρι να κλείσει ο browser
$config['totp_session_timeout'] = 0;

// Backup codes enabled (για περίπτωση που χαθεί το device)
$config['totp_backup_codes_enabled'] = true;
$config['totp_backup_codes_count'] = 10;

// Algorithm (μην αλλάξεις εκτός αν ξέρεις τι κάνεις)
$config['totp_algorithm'] = 'sha1'; // 'sha1', 'sha256', 'sha512'

?>


<?php
/**
 * composer.json για dependencies
 * Αποθήκευσε ως: plugins/totp_unified/composer.json
 */
?>
{
  "name": "roundcube/totp_unified",
  "description": "Two-Factor Authentication with unified TOTP for alias domains",
  "type": "roundcube-plugin",
  "license": "GPL-3.0+",
  "version": "1.0.0",
  "authors": [
    {
      "name": "Your Name",
      "email": "your.email@example.com"
    }
  ],
  "require": {
    "php": ">=7.4",
    "roundcube/plugin-installer": ">=0.1.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^9.0"
  }
}


<?php
/**
 * Δομή καταλόγων του plugin:
 * 
 * plugins/
 * └── totp_unified/
 *     ├── totp_unified.php          (Κύριο PHP αρχείο)
 *     ├── config.inc.php            (Configuration)
 *     ├── composer.json             (Dependencies)
 *     ├── totp_unified.js           (JavaScript)
 *     ├── skins/
 *     │   └── elastic/
 *     │       ├── totp_unified.css  (Styles)
 *     │       └── templates/
 *     │           ├── setup.html    (Setup template)
 *     │           └── verify.html   (Verification template)
 *     ├── localization/
 *     │   ├── en_US.inc            (English translations)
 *     │   └── el_GR.inc            (Greek translations)
 *     └── SQL/
 *         ├── mysql.initial.sql    (MySQL schema)
 *         └── postgres.initial.sql (PostgreSQL schema)
 */
?>


<?php
/**
 * SQL Schema για MySQL/MariaDB
 * Αποθήκευσε ως: plugins/totp_unified/SQL/mysql.initial.sql
 */
?>

-- TOTP Secrets Table
CREATE TABLE IF NOT EXISTS `totp_secrets` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `secret` VARCHAR(255) NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used` TIMESTAMP NULL DEFAULT NULL,
  `failed_attempts` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_unique` (`username`),
  KEY `idx_username` (`username`),
  KEY `idx_enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backup Codes Table (optional)
CREATE TABLE IF NOT EXISTS `totp_backup_codes` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `code` VARCHAR(64) NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  `used_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Log Table (optional)
CREATE TABLE IF NOT EXISTS `totp_audit_log` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT,
  `success` TINYINT(1) NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


<?php
/**
 * Ελληνικές μεταφράσεις
 * Αποθήκευσε ως: plugins/totp_unified/localization/el_GR.inc
 */

$labels = array();
$labels['2fa_title'] = 'Έλεγχος Ταυτότητας Δύο Παραγόντων (2FA)';
$labels['2fa_enable'] = 'Ενεργοποίηση 2FA';
$labels['2fa_disable'] = 'Απενεργοποίηση 2FA';
$labels['2fa_status'] = 'Κατάσταση';
$labels['2fa_enabled'] = 'Ενεργοποιημένο';
$labels['2fa_disabled'] = 'Απενεργοποιημένο';
$labels['2fa_scan_qr'] = 'Σκανάρετε τον QR κωδικό';
$labels['2fa_or_enter_secret'] = 'Ή εισάγετε το secret χειροκίνητα';
$labels['2fa_enter_code'] = 'Εισάγετε τον 6-ψήφιο κωδικό';
$labels['2fa_verify'] = 'Επαλήθευση';
$labels['2fa_cancel'] = 'Ακύρωση';
$labels['2fa_setup_success'] = 'Το 2FA ενεργοποιήθηκε επιτυχώς!';
$labels['2fa_setup_failed'] = 'Αποτυχία ενεργοποίησης 2FA';
$labels['2fa_invalid_code'] = 'Μη έγκυρος κωδικός επαλήθευσης';
$labels['2fa_verification_required'] = 'Απαιτείται έλεγχος ταυτότητας δύο παραγόντων';
$labels['2fa_unified_notice'] = 'Σημείωση: Αυτός ο κωδικός λειτουργεί για όλα τα email aliases σας!';

$messages = array();
$messages['2fa_enabled_success'] = 'Το 2FA ενεργοποιήθηκε επιτυχώς για το λογαριασμό σας';
$messages['2fa_disabled_success'] = 'Το 2FA απενεργοποιήθηκε για το λογαριασμό σας';
$messages['2fa_verification_failed'] = 'Αποτυχία επαλήθευσης. Παρακαλώ δοκιμάστε ξανά.';
$messages['2fa_too_many_attempts'] = 'Πάρα πολλές αποτυχημένες προσπάθειες. Δοκιμάστε ξανά αργότερα.';
?>
