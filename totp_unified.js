/**
 * JavaScript για το Roundcube 2FA Plugin
 */

function setup2FA() {
    if (!confirm('Do you want to enable Two-Factor Authentication?')) {
        return;
    }
    
    // Call plugin action
    rcmail.http_post('plugin.totp_unified.setup', {}, rcmail.set_busy(true, 'loading'));
    
    rcmail.addEventListener('plugin.totp_setup_response', function(response) {
        rcmail.set_busy(false);
        
        if (response.success) {
            showQRCodeDialog(response);
        } else {
            alert('Failed to setup 2FA');
        }
    });
}

function showQRCodeDialog(data) {
    var dialog = $('<div id="totp-setup-dialog">')
        .html(
            '<h3>Setup Two-Factor Authentication</h3>' +
            '<p><strong>Important:</strong> This code works for all your email aliases!</p>' +
            '<p>Scan this QR code with your authenticator app:</p>' +
            '<div style="text-align: center; margin: 20px 0;">' +
                '<img src="' + data.qr_url + '" alt="QR Code">' +
            '</div>' +
            '<p>Or enter this secret manually:</p>' +
            '<p><code style="background: #f0f0f0; padding: 10px; display: block; word-break: break-all;">' + 
                data.secret + 
            '</code></p>' +
            '<p><strong>Username:</strong> ' + data.username + '</p>' +
            '<hr>' +
            '<p>Enter a code from your app to verify setup:</p>' +
            '<input type="text" id="verify-code" maxlength="6" pattern="[0-9]{6}" placeholder="000000" ' +
                'style="width: 150px; font-size: 24px; text-align: center; letter-spacing: 5px;">' +
            '<br><br>' +
            '<button onclick="verifySetup()">Verify & Enable</button>' +
            '<button onclick="closeDialog()">Cancel</button>'
        )
        .appendTo('body');
    
    dialog.css({
        position: 'fixed',
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)',
        background: 'white',
        padding: '30px',
        border: '2px solid #333',
        'border-radius': '8px',
        'box-shadow': '0 4px 20px rgba(0,0,0,0.3)',
        'z-index': 10000,
        'max-width': '500px'
    });
    
    // Backdrop
    $('<div id="totp-backdrop">')
        .css({
            position: 'fixed',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            background: 'rgba(0,0,0,0.5)',
            'z-index': 9999
        })
        .appendTo('body');
}

function verifySetup() {
    var code = $('#verify-code').val().replace(/\s/g, '');
    
    if (code.length !== 6) {
        alert('Please enter a 6-digit code');
        return;
    }
    
    // Verify the code (server-side verification would be better)
    $.post(rcmail.url('plugin.totp_unified.verify_setup'), { code: code }, function(response) {
        if (response.success) {
            alert('2FA enabled successfully! You will need to enter a code next time you log in.');
            closeDialog();
            location.reload();
        } else {
            alert('Invalid code. Please try again.');
        }
    });
}

function closeDialog() {
    $('#totp-setup-dialog').remove();
    $('#totp-backdrop').remove();
}

function disable2FA() {
    if (!confirm('Are you sure you want to disable Two-Factor Authentication?')) {
        return;
    }
    
    rcmail.http_post('plugin.totp_unified.disable', {}, rcmail.set_busy(true, 'loading'));
    
    rcmail.addEventListener('plugin.totp_disable_response', function(response) {
        rcmail.set_busy(false);
        
        if (response.success) {
            alert('2FA disabled successfully');
            location.reload();
        } else {
            alert('Failed to disable 2FA');
        }
    });
}

// Auto-format TOTP code input (add spaces every 3 digits)
$(document).on('input', '#verify-code, input[name="totp_code"]', function() {
    var value = $(this).val().replace(/\s/g, '');
    if (value.length > 3) {
        $(this).val(value.substr(0, 3) + ' ' + value.substr(3, 3));
    }
});