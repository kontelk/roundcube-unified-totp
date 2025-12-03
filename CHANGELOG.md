# Changelog

All notable changes to Roundcube Unified TOTP will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Planned
- WebAuthn/FIDO2 support
- SMS backup authentication
- Admin panel for bulk user management
- Mobile app for QR scanning
- LDAP/Active Directory integration
- Multi-language expansion (Spanish, French, German)

---

## [1.0.0] - 2024-12-03

### Added
- Initial release of Roundcube Unified TOTP plugin
- Core 2FA functionality with TOTP (RFC 6238)
- Unified secret management for alias domains
- Username-based secret storage (domain-independent)
- QR code generation for authenticator apps
- Support for all standard TOTP authenticator apps:
  - Google Authenticator
  - Microsoft Authenticator
  - Authy
  - FreeOTP
  - Any RFC 6238 compliant app
- Database support:
  - MySQL 5.7+
  - MariaDB 10.2+
  - PostgreSQL 10+
- Security features:
  - Encrypted secret storage
  - Rate limiting (max failed attempts)
  - Account lockout mechanism
  - Time-based tolerance for clock skew
  - Replay attack prevention
  - Session management
- Audit logging:
  - All 2FA events logged
  - Failed login attempts tracking
  - IP address and user agent logging
  - Configurable log retention
- Backup codes system:
  - One-time use recovery codes
  - Hashed storage for security
  - Regeneration capability
- Configuration options:
  - Customizable issuer name
  - Adjustable time tolerance
  - Domain-specific requirements
  - IP whitelist for bypassing 2FA
  - Session timeout settings
- User interface:
  - Settings page integration
  - QR code display (Google Charts API or local)
  - Verification form
  - Status indicators
- Localization:
  - English (en_US)
  - Greek (el_GR)
- Documentation:
  - Comprehensive README
  - Installation guide
  - Security guide
  - Configuration examples
  - Troubleshooting section
- Testing:
  - Python test script for TOTP validation
  - Example configurations
  - Database schema validation
- Development tools:
  - PSR-12 code style compliance
  - Composer support
  - PHPUnit test framework setup

### Security
- TOTP implementation follows RFC 6238 standard
- HMAC-SHA1 algorithm for maximum compatibility
- Base32 secret encoding
- 30-second time step (industry standard)
- 6-digit codes (configurable to 8)
- Protection against:
  - Brute force attacks
  - Replay attacks
  - Clock skew issues
  - SQL injection
  - XSS attacks
- Secure session handling
- Encrypted database storage

### Performance
- Efficient username extraction
- Minimal database queries
- Optimized time step calculations
- Indexed database tables
- Optional verification caching

---

## Version History Overview

### Version Numbering

We use Semantic Versioning (MAJOR.MINOR.PATCH):
- **MAJOR**: Incompatible API changes
- **MINOR**: New features (backwards compatible)
- **PATCH**: Bug fixes (backwards compatible)

### Release Schedule

- **Major releases**: When significant architecture changes occur
- **Minor releases**: Every 2-3 months with new features
- **Patch releases**: As needed for bug fixes

---

## Migration Guide

### From 0.x to 1.0.0

Initial release - no migration needed.

---

## Known Issues

### Current Limitations

1. **Authenticator App Support**: Only TOTP-based apps are supported (HOTP not implemented)
2. **QR Code Size**: Fixed size when using Google Charts API
3. **Backup Codes**: Manual regeneration required (no automatic rotation)
4. **LDAP Integration**: Not yet implemented
5. **Admin UI**: No dedicated admin panel (database management required)

### Workarounds

1. **Clock Skew**: Increase tolerance if users experience frequent failures
   ```php
   $config['totp_tolerance'] = 2; // ±60 seconds
   ```

2. **QR Code Not Displaying**: Switch to local generation
   ```php
   $config['totp_qr_method'] = 'local';
   ```

3. **Lost Device**: Use backup codes or admin can disable 2FA via database
   ```sql
   UPDATE totp_secrets SET enabled=0 WHERE username='user';
   ```

---

## Deprecation Notices

None for version 1.0.0 (initial release).

---

## Contributors

### Core Team
- Your Name (@yourusername) - Creator & Lead Developer

### Contributors
- Thank you to everyone who reported issues and provided feedback during development!

### Translators
- English: Your Name
- Greek: Your Name

---

## Links

- [GitHub Repository](https://github.com/yourusername/roundcube-unified-totp)
- [Issue Tracker](https://github.com/yourusername/roundcube-unified-totp/issues)
- [Discussions](https://github.com/yourusername/roundcube-unified-totp/discussions)
- [Documentation](https://github.com/yourusername/roundcube-unified-totp/tree/main/docs)

---

[Unreleased]: https://github.com/yourusername/roundcube-unified-totp/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/yourusername/roundcube-unified-totp/releases/tag/v1.0.0