<?php

/**
 * Roundcube Unified TOTP Plugin
 * English (United States) Localization
 * 
 * @language en_US
 * @version 1.0.0
 */

// ========================================================================
// LABELS (UI Elements)
// ========================================================================

$labels = array();

// Main section titles
$labels['2fa_title'] = 'Two-Factor Authentication';
$labels['2fa_section'] = 'Security';

// Status
$labels['2fa_status'] = 'Status';
$labels['2fa_enabled'] = 'Enabled';
$labels['2fa_disabled'] = 'Disabled';
$labels['2fa_active'] = 'Active';
$labels['2fa_inactive'] = 'Inactive';

// Actions
$labels['2fa_enable'] = 'Enable 2FA';
$labels['2fa_disable'] = 'Disable 2FA';
$labels['2fa_setup'] = 'Setup Two-Factor Authentication';
$labels['2fa_configure'] = 'Configure';
$labels['2fa_verify'] = 'Verify';
$labels['2fa_cancel'] = 'Cancel';
$labels['2fa_save'] = 'Save';
$labels['2fa_close'] = 'Close';

// Setup process
$labels['2fa_scan_qr'] = 'Scan QR Code';
$labels['2fa_or_enter_secret'] = 'Or enter secret manually';
$labels['2fa_secret_key'] = 'Secret Key';
$labels['2fa_enter_code'] = 'Enter 6-digit code';
$labels['2fa_code_placeholder'] = '000000';
$labels['2fa_verify_code'] = 'Verify Code';

// Information
$labels['2fa_unified_notice'] = 'Important: This code works for all your email aliases!';
$labels['2fa_scan_instructions'] = 'Scan this QR code with your authenticator app';
$labels['2fa_manual_instructions'] = 'Enter this secret in your authenticator app';
$labels['2fa_app_recommendations'] = 'Recommended apps: Google Authenticator, Microsoft Authenticator, Authy';
$labels['2fa_verification_required'] = 'Two-Factor Authentication Required';
$labels['2fa_enter_verification_code'] = 'Enter the verification code from your authenticator app';

// Account info
$labels['2fa_username'] = 'Username';
$labels['2fa_email'] = 'Email Address';
$labels['2fa_enabled_on'] = 'Enabled on';
$labels['2fa_last_used'] = 'Last used';
$labels['2fa_never_used'] = 'Never used';

// Backup codes
$labels['2fa_backup_codes'] = 'Backup Codes';
$labels['2fa_backup_codes_info'] = 'Save these codes in a secure location. Each code can only be used once.';
$labels['2fa_backup_codes_generate'] = 'Generate Backup Codes';
$labels['2fa_backup_codes_regenerate'] = 'Regenerate Backup Codes';
$labels['2fa_backup_codes_print'] = 'Print Codes';
$labels['2fa_backup_codes_download'] = 'Download Codes';
$labels['2fa_backup_codes_remaining'] = 'Remaining codes';
$labels['2fa_use_backup_code'] = 'Use a backup code instead';

// Warnings
$labels['2fa_disable_warning'] = 'Warning: Disabling 2FA will make your account less secure.';
$labels['2fa_backup_codes_warning'] = 'Keep these codes secure. Anyone with these codes can access your account.';

// Form labels
$labels['2fa_issuer'] = 'Issuer';
$labels['2fa_algorithm'] = 'Algorithm';
$labels['2fa_digits'] = 'Digits';
$labels['2fa_period'] = 'Period';
$labels['2fa_period_seconds'] = 'seconds';


// ========================================================================
// MESSAGES (Feedback & Notifications)
// ========================================================================

$messages = array();

// Success messages
$messages['2fa_enabled_success'] = 'Two-Factor Authentication has been enabled successfully for your account';
$messages['2fa_disabled_success'] = 'Two-Factor Authentication has been disabled for your account';
$messages['2fa_verification_success'] = 'Verification successful. Welcome back!';
$messages['2fa_code_correct'] = 'Code verified successfully';
$messages['2fa_backup_codes_generated'] = 'Backup codes have been generated successfully';

// Error messages
$messages['2fa_setup_failed'] = 'Failed to enable Two-Factor Authentication. Please try again.';
$messages['2fa_disable_failed'] = 'Failed to disable Two-Factor Authentication. Please contact support.';
$messages['2fa_verification_failed'] = 'Verification failed. Please check your code and try again.';
$messages['2fa_invalid_code'] = 'Invalid verification code. Please try again.';
$messages['2fa_code_expired'] = 'This code has expired. Please use the current code from your app.';
$messages['2fa_too_many_attempts'] = 'Too many failed attempts. Your account has been temporarily locked. Please try again in 15 minutes.';
$messages['2fa_account_locked'] = 'Your account is temporarily locked due to multiple failed login attempts.';
$messages['2fa_code_required'] = 'Verification code is required';
$messages['2fa_backup_code_used'] = 'This backup code has already been used';
$messages['2fa_no_backup_codes'] = 'No backup codes available. Please contact support.';

// Warning messages
$messages['2fa_required'] = 'Two-Factor Authentication is required for your account';
$messages['2fa_grace_period'] = 'You have {days} days to enable Two-Factor Authentication';
$messages['2fa_grace_period_expired'] = 'Your grace period for enabling 2FA has expired. Please enable it now to continue.';

// Info messages
$messages['2fa_not_enabled'] = 'Two-Factor Authentication is not enabled';
$messages['2fa_already_enabled'] = 'Two-Factor Authentication is already enabled';
$messages['2fa_setup_complete'] = 'Setup complete. You will need to enter a code next time you log in.';

// Notification emails (subject lines)
$messages['2fa_email_enabled_subject'] = '2FA Enabled on Your Account';
$messages['2fa_email_disabled_subject'] = '2FA Disabled on Your Account';
$messages['2fa_email_failed_attempts_subject'] = 'Multiple Failed 2FA Attempts Detected';

// Notification emails (body text)
$messages['2fa_email_enabled_body'] = 'Two-Factor Authentication has been enabled on your account. If this wasn\'t you, please contact support immediately.';
$messages['2fa_email_disabled_body'] = 'Two-Factor Authentication has been disabled on your account. If this wasn\'t you, please contact support immediately.';
$messages['2fa_email_failed_attempts_body'] = 'We detected multiple failed 2FA attempts on your account. If this wasn\'t you, please secure your account immediately.';


// ========================================================================
// HELP TEXT (Tooltips & Instructions)
// ========================================================================

$help = array();

$help['2fa_what_is'] = 'Two-Factor Authentication adds an extra layer of security to your account by requiring both your password and a verification code from your phone.';
$help['2fa_how_it_works'] = 'After enabling 2FA, you\'ll need to enter a 6-digit code from your authenticator app each time you log in.';
$help['2fa_unified_explanation'] = 'With Unified TOTP, you only need one QR code for all your email aliases. The same verification code works for username@domain1.com, username@alias1.com, etc.';
$help['2fa_apps'] = 'You can use any TOTP-compatible authenticator app, such as Google Authenticator, Microsoft Authenticator, or Authy.';
$help['2fa_backup_codes'] = 'Backup codes allow you to access your account if you lose your phone. Each code can only be used once.';
$help['2fa_time_based'] = 'Codes are time-based and change every 30 seconds. Make sure your device\'s clock is accurate.';
$help['2fa_security_notice'] = 'Never share your secret key or verification codes with anyone. Our support team will never ask for these.';


// ========================================================================
// BUTTONS
// ========================================================================

$buttons = array();

$buttons['2fa_enable_now'] = 'Enable Now';
$buttons['2fa_disable_now'] = 'Disable Now';
$buttons['2fa_setup_now'] = 'Setup Now';
$buttons['2fa_verify_now'] = 'Verify Now';
$buttons['2fa_try_again'] = 'Try Again';
$buttons['2fa_use_backup'] = 'Use Backup Code';
$buttons['2fa_contact_support'] = 'Contact Support';


// ========================================================================
// ERROR CODES
// ========================================================================

$errors = array();

$errors['2fa_error_1001'] = 'Database error occurred';
$errors['2fa_error_1002'] = 'Invalid username format';
$errors['2fa_error_1003'] = 'Secret generation failed';
$errors['2fa_error_1004'] = 'QR code generation failed';
$errors['2fa_error_1005'] = 'Invalid configuration';
$errors['2fa_error_2001'] = 'Invalid verification code format';
$errors['2fa_error_2002'] = 'Code verification timeout';
$errors['2fa_error_2003'] = 'Account is locked';
$errors['2fa_error_3001'] = 'Backup code not found';
$errors['2fa_error_3002'] = 'Backup code already used';


// ========================================================================
// ADMIN INTERFACE (Future use)
// ========================================================================

$admin = array();

$admin['2fa_admin_title'] = '2FA Administration';
$admin['2fa_stats'] = 'Statistics';
$admin['2fa_users_enabled'] = 'Users with 2FA enabled';
$admin['2fa_users_disabled'] = 'Users without 2FA';
$admin['2fa_recent_activity'] = 'Recent Activity';
$admin['2fa_failed_attempts'] = 'Failed Attempts';
$admin['2fa_locked_accounts'] = 'Locked Accounts';

?>
