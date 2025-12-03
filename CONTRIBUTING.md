# 🤝 Contributing to Roundcube Unified TOTP

Thank you for considering contributing to Roundcube Unified TOTP! This document provides guidelines and instructions for contributing.

---

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)
- [Translation](#translation)

---

## 📜 Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inclusive environment for everyone. We pledge to:

- Use welcoming and inclusive language
- Be respectful of differing viewpoints
- Accept constructive criticism gracefully
- Focus on what's best for the community
- Show empathy towards other community members

### Expected Behavior

- Be professional and respectful
- Provide constructive feedback
- Help others when possible
- Report inappropriate behavior

### Unacceptable Behavior

- Harassment or discrimination
- Trolling or insulting comments
- Personal or political attacks
- Publishing others' private information
- Any conduct inappropriate in a professional setting

---

## 🎯 How Can I Contribute?

### 1. Reporting Bugs

Found a bug? Help us fix it!

**Before reporting:**
- Check if the issue already exists in [GitHub Issues](https://github.com/yourusername/roundcube-unified-totp/issues)
- Test with the latest version
- Collect relevant information

**Bug report should include:**
- Clear, descriptive title
- Steps to reproduce
- Expected vs actual behavior
- Environment details (OS, PHP version, Roundcube version)
- Relevant logs or error messages
- Screenshots (if applicable)

**Bug Report Template:**

```markdown
## Bug Description
[Clear description of the bug]

## Steps to Reproduce
1. Go to...
2. Click on...
3. See error...

## Expected Behavior
[What should happen]

## Actual Behavior
[What actually happens]

## Environment
- OS: Ubuntu 22.04
- PHP Version: 8.1
- Roundcube Version: 1.6.0
- Database: MySQL 8.0
- Browser: Chrome 120

## Logs
```
[Paste relevant logs]
```

## Additional Context
[Any other relevant information]
```

### 2. Suggesting Features

Have an idea for a new feature?

**Feature request should include:**
- Clear, descriptive title
- Problem statement (what problem does it solve?)
- Proposed solution
- Alternative solutions considered
- Use cases and examples

**Feature Request Template:**

```markdown
## Feature Description
[Clear description of the feature]

## Problem Statement
[What problem does this solve?]

## Proposed Solution
[How should it work?]

## Use Cases
1. [Use case 1]
2. [Use case 2]

## Alternatives Considered
[What other approaches did you consider?]

## Additional Context
[Mockups, diagrams, or other helpful info]
```

### 3. Writing Code

Ready to contribute code? Great!

**Types of contributions:**
- Bug fixes
- New features
- Performance improvements
- Code refactoring
- Documentation improvements
- Test coverage

**Before starting:**
1. Check existing issues and PRs
2. Discuss major changes first
3. Fork the repository
4. Create a feature branch
5. Write clean, documented code
6. Test thoroughly
7. Submit a pull request

---

## 💻 Development Setup

### Prerequisites

```bash
# Required
- PHP 7.4+
- MySQL/MariaDB or PostgreSQL
- Git
- Composer

# Optional
- Docker (for testing)
- PHPUnit
- PHP_CodeSniffer
```

### Setup Steps

1. **Fork and Clone**

```bash
# Fork on GitHub, then clone your fork
git clone https://github.com/YOUR_USERNAME/roundcube-unified-totp.git
cd roundcube-unified-totp

# Add upstream remote
git remote add upstream https://github.com/original/roundcube-unified-totp.git
```

2. **Install Dependencies**

```bash
composer install
```

3. **Setup Test Environment**

```bash
# Copy test config
cp tests/config.php.dist tests/config.php

# Edit test configuration
nano tests/config.php

# Setup test database
mysql -u root -p < tests/schema.sql
```

4. **Create Feature Branch**

```bash
git checkout -b feature/my-new-feature
```

### Development Workflow

```bash
# 1. Keep your fork updated
git fetch upstream
git checkout main
git merge upstream/main

# 2. Create feature branch
git checkout -b feature/amazing-feature

# 3. Make changes
# ... edit files ...

# 4. Test your changes
composer test

# 5. Commit
git add .
git commit -m "feat: add amazing feature"

# 6. Push
git push origin feature/amazing-feature

# 7. Create Pull Request on GitHub
```

---

## 📏 Coding Standards

### PHP Standards

We follow **PSR-12** coding style:

```bash
# Check code style
composer cs-check

# Auto-fix code style
composer cs-fix
```

**Key rules:**
- Use 4 spaces for indentation (no tabs)
- Opening braces on same line for classes/functions
- Use meaningful variable names
- Add PHPDoc comments for functions
- Follow Roundcube plugin conventions

**Example:**

```php
<?php

/**
 * Calculate TOTP code for given secret
 *
 * @param string $secret Base32 encoded secret
 * @param int|null $timestamp Unix timestamp (null = current time)
 * @return string 6-digit TOTP code
 */
private function calculate_totp($secret, $timestamp = null)
{
    if ($timestamp === null) {
        $timestamp = time();
    }
    
    $time_step = floor($timestamp / 30);
    $secret_key = $this->base32_decode($secret);
    
    // ... rest of implementation
    
    return str_pad($code, 6, '0', STR_PAD_LEFT);
}
```

### JavaScript Standards

- Use ES6+ features
- 2 spaces for indentation
- Use semicolons
- Use `const` and `let` (not `var`)
- Add JSDoc comments

```javascript
/**
 * Setup Two-Factor Authentication
 * 
 * @returns {Promise<void>}
 */
async function setup2FA() {
    const response = await fetch('/plugin/totp/setup');
    const data = await response.json();
    
    if (data.success) {
        showQRCode(data.qr_url);
    }
}
```

### SQL Standards

- Use uppercase for keywords
- Proper indentation
- Meaningful table/column names
- Add comments

```sql
-- Get failed login attempts for user
SELECT 
    username,
    COUNT(*) AS failed_attempts,
    MAX(timestamp) AS last_attempt
FROM totp_audit_log
WHERE 
    action = 'login_fail'
    AND username = ?
    AND timestamp > NOW() - INTERVAL 24 HOUR
GROUP BY username;
```

### Documentation Standards

- Use Markdown format
- Clear, concise language
- Include code examples
- Add screenshots when helpful
- Keep up to date with code changes

---

## 🔍 Testing

### Running Tests

```bash
# Run all tests
composer test

# Run specific test
./vendor/bin/phpunit tests/TOTPTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

### Writing Tests

```php
<?php

use PHPUnit\Framework\TestCase;

class TOTPTest extends TestCase
{
    public function testGenerateSecret()
    {
        $secret = generate_secret(32);
        
        $this->assertEquals(32, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }
    
    public function testCalculateTOTP()
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $time = 1234567890;
        
        $otp = calculate_totp($secret, $time);
        
        $this->assertEquals(6, strlen($otp));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);
    }
}
```

### Test Coverage

Aim for:
- **80%+ overall coverage**
- **100% coverage** for critical security functions
- Test both success and failure cases
- Test edge cases and error conditions

---

## 📤 Pull Request Process

### Before Submitting

1. **Update your branch**

```bash
git fetch upstream
git rebase upstream/main
```

2. **Run tests**

```bash
composer test
composer cs-check
```

3. **Update documentation**
   - Update README if needed
   - Add/update inline comments
   - Update CHANGELOG

4. **Commit message format**

Use [Conventional Commits](https://www.conventionalcommits.org/):

```
type(scope): subject

body

footer
```

**Types:**
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation only
- `style:` Code style (formatting, etc)
- `refactor:` Code refactoring
- `test:` Adding tests
- `chore:` Maintenance tasks

**Examples:**

```bash
git commit -m "feat: add backup codes functionality"
git commit -m "fix: correct time tolerance calculation"
git commit -m "docs: update installation guide"
git commit -m "test: add TOTP generation tests"
```

### PR Template

```markdown
## Description
[Clear description of changes]

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Related Issues
Closes #123

## Testing
- [ ] Added/updated tests
- [ ] All tests passing
- [ ] Tested manually

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-reviewed code
- [ ] Commented complex code
- [ ] Updated documentation
- [ ] No new warnings
- [ ] Added tests that prove fix/feature works
- [ ] New/existing tests pass locally
- [ ] Dependent changes merged

## Screenshots (if applicable)
[Add screenshots]

## Additional Notes
[Any other information]
```

### Review Process

1. **Automated checks**
   - CI/CD pipeline runs
   - Code style check
   - Tests execution
   - Security scan

2. **Code review**
   - At least one maintainer approval
   - Address review comments
   - Update PR as needed

3. **Merge**
   - Squash commits if requested
   - Maintainer merges PR
   - Delete feature branch

---

## 🌍 Translation

Help translate the plugin!

### Adding a New Language

1. **Create translation file**

```bash
cp localization/en_US.inc localization/XX_YY.inc
```

2. **Translate strings**

```php
<?php
// localization/el_GR.inc

$labels = array();
$labels['2fa_title'] = 'Έλεγχος Ταυτότητας Δύο Παραγόντων';
$labels['2fa_enable'] = 'Ενεργοποίηση 2FA';
// ... more translations

$messages = array();
$messages['2fa_enabled_success'] = 'Το 2FA ενεργοποιήθηκε επιτυχώς';
// ... more messages
?>
```

3. **Test translation**
   - Install in Roundcube
   - Set language in preferences
   - Verify all strings display correctly

4. **Submit PR**
   - Include translation file
   - Note: Translation for XX_YY language
   - Credit yourself in translators list

### Translation Guidelines

- Maintain tone and formality
- Keep technical terms consistent
- Consider cultural context
- Test with actual interface
- Keep string length reasonable

---

## 🏆 Recognition

Contributors will be:
- Listed in CONTRIBUTORS.md
- Credited in release notes
- Mentioned in README (for significant contributions)

---

## 📞 Getting Help

Need help contributing?

- 💬 [GitHub Discussions](https://github.com/yourusername/roundcube-unified-totp/discussions)
- 🐛 [GitHub Issues](https://github.com/yourusername/roundcube-unified-totp/issues)
- 📧 Email: contribute@example.com

---

## 📄 License

By contributing, you agree that your contributions will be licensed under the GPL-3.0 License.

---

**Thank you for contributing! 🎉**

Every contribution, no matter how small, makes a difference!