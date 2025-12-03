---
name: Bug Report
about: Create a report to help us improve
title: '[BUG] '
labels: bug
assignees: ''
---

## 🐛 Bug Description

<!-- A clear and concise description of what the bug is -->


## 📋 Steps to Reproduce

<!-- Steps to reproduce the behavior -->

1. Go to '...'
2. Click on '...'
3. Enter '...'
4. See error

## ✅ Expected Behavior

<!-- A clear and concise description of what you expected to happen -->


## ❌ Actual Behavior

<!-- A clear and concise description of what actually happened -->


## 🖼️ Screenshots

<!-- If applicable, add screenshots to help explain your problem -->


## 🖥️ Environment

**Server Environment:**
- OS: [e.g., Ubuntu 22.04]
- PHP Version: [e.g., 8.1.2]
- Roundcube Version: [e.g., 1.6.0]
- Database: [e.g., MySQL 8.0.32]
- Web Server: [e.g., Apache 2.4.52 / Nginx 1.18.0]

**Client Environment:**
- Browser: [e.g., Chrome 120, Firefox 121]
- Browser Version: 
- OS: [e.g., Windows 11, macOS 14, iOS 17]
- Authenticator App: [e.g., Google Authenticator, Microsoft Authenticator]

**Plugin Configuration:**
- Plugin Version: [e.g., 1.0.0]
- QR Method: [google / local]
- Time Tolerance: [e.g., 1]
- Backup Codes Enabled: [yes / no]

## 📝 Relevant Configuration

<!-- Share relevant parts of your config.inc.php (remove sensitive data!) -->

```php
$config['totp_issuer'] = 'MyMailServer';
$config['totp_tolerance'] = 1;
// ... other relevant settings
```

## 📊 Error Logs

<!-- Please include relevant error logs -->

**PHP Error Log:**
```
[paste PHP error log here]
```

**Roundcube Error Log:**
```
[paste Roundcube error log here]
```

**Browser Console:**
```
[paste browser console errors here]
```

**Database Errors:**
```
[paste any database errors here]
```

## 🔍 Additional Context

<!-- Add any other context about the problem here -->


## 🧪 Attempted Solutions

<!-- What have you tried to fix this issue? -->

- [ ] Cleared browser cache
- [ ] Restarted web server
- [ ] Checked file permissions
- [ ] Verified database connection
- [ ] Checked server time synchronization
- [ ] Reviewed configuration settings
- [ ] Other (describe):

## 📌 Related Issues

<!-- Link to related issues if any -->

Related to #

## ⚠️ Impact

<!-- How is this bug affecting you? -->

- [ ] Blocking - Cannot use the plugin at all
- [ ] High - Major feature not working
- [ ] Medium - Some functionality impaired
- [ ] Low - Minor inconvenience

## 🔐 Security

<!-- Is this a security issue? If yes, please email security@example.com instead -->

- [ ] This is NOT a security issue
- [ ] This IS a security issue (please use private disclosure)

---

**Checklist before submitting:**

- [ ] I have searched for similar issues
- [ ] I have included all required information above
- [ ] I have removed any sensitive information from logs/configs
- [ ] I am using the latest version of the plugin
- [ ] I have verified this is not a Roundcube core issue