<?php

/**
 * Roundcube 2FA Plugin with Unified TOTP for Alias Domains
 * 
 * Το plugin αυτό υλοποιεί 2FA με TOTP που είναι κοινό για όλα τα alias domains
 * βασιζόμενο μόνο στο username (χωρίς το domain)
 */

class totp_unified extends rcube_plugin
{
    public $task = 'login|settings';
    private $rc;
    private $secret_length = 32; // Base32 encoded secret length
    
    public function init()
    {
        $this->rc = rcube::get_instance();
        $this->load_config();
        
        // Hooks για authentication
        $this->add_hook('authenticate', array($this, 'authenticate'));
        $this->add_hook('login_after', array($this, 'login_after'));
        
        // Hooks για settings interface
        $this->add_hook('preferences_list', array($this, 'preferences_list'));
        $this->add_hook('preferences_save', array($this, 'preferences_save'));
        
        // Register actions
        $this->register_action('plugin.totp_unified.setup', array($this, 'setup_2fa'));
        $this->register_action('plugin.totp_unified.verify', array($this, 'verify_code'));
        $this->register_action('plugin.totp_unified.disable', array($this, 'disable_2fa'));
        
        $this->include_script('totp_unified.js');
        $this->include_stylesheet($this->local_skin_path() . '/totp_unified.css');
    }
    
    /**
     * Εξάγει το username από το email address
     * π.χ. "username1@domain1.com" -> "username1"
     */
    private function extract_username($email)
    {
        $parts = explode('@', $email);
        return strtolower(trim($parts[0])); // Normalize σε lowercase
    }
    
    /**
     * Δημιουργεί ένα μοναδικό TOTP secret για το username
     */
    private function generate_secret()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 alphabet
        $secret = '';
        
        for ($i = 0; $i < $this->secret_length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $secret;
    }
    
    /**
     * Υπολογίζει το TOTP code για το συγκεκριμένο secret
     */
    private function calculate_totp($secret, $time = null)
    {
        if ($time === null) {
            $time = time();
        }
        
        // TOTP χρησιμοποιεί 30-second time steps
        $time_step = floor($time / 30);
        
        // Decode Base32 secret
        $secret_key = $this->base32_decode($secret);
        
        // Pack time as 8-byte big-endian
        $time_bytes = pack('N*', 0, $time_step);
        
        // HMAC-SHA1
        $hash = hash_hmac('sha1', $time_bytes, $secret_key, true);
        
        // Dynamic truncation
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Base32 decoding (RFC 4648)
     */
    private function base32_decode($input)
    {
        $input = strtoupper($input);
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $v = 0;
        $vbits = 0;
        
        for ($i = 0, $j = strlen($input); $i < $j; $i++) {
            $v <<= 5;
            $v += stripos($alphabet, $input[$i]);
            $vbits += 5;
            
            while ($vbits >= 8) {
                $vbits -= 8;
                $output .= chr($v >> $vbits);
                $v &= ((1 << $vbits) - 1);
            }
        }
        
        return $output;
    }
    
    /**
     * Αποθηκεύει το TOTP secret στη βάση δεδομένων
     * Κλειδί: username (χωρίς domain)
     */
    private function save_secret($username, $secret)
    {
        $db = $this->rc->get_dbh();
        
        // Δημιουργία πίνακα αν δεν υπάρχει
        $this->create_table_if_not_exists();
        
        // Check if secret already exists
        $query = "SELECT id FROM totp_secrets WHERE username = ?";
        $result = $db->query($query, $username);
        
        if ($db->num_rows($result) > 0) {
            // Update existing
            $query = "UPDATE totp_secrets SET secret = ?, enabled = 1, created_at = NOW() WHERE username = ?";
            $db->query($query, $secret, $username);
        } else {
            // Insert new
            $query = "INSERT INTO totp_secrets (username, secret, enabled, created_at) VALUES (?, ?, 1, NOW())";
            $db->query($query, $username, $secret);
        }
        
        return true;
    }
    
    /**
     * Ανακτά το TOTP secret από τη βάση
     */
    private function get_secret($username)
    {
        $db = $this->rc->get_dbh();
        
        $query = "SELECT secret, enabled FROM totp_secrets WHERE username = ?";
        $result = $db->query($query, $username);
        
        if ($row = $db->fetch_assoc($result)) {
            if ($row['enabled'] == 1) {
                return $row['secret'];
            }
        }
        
        return null;
    }
    
    /**
     * Δημιουργεί τον πίνακα στη βάση αν δεν υπάρχει
     */
    private function create_table_if_not_exists()
    {
        $db = $this->rc->get_dbh();
        
        $query = "CREATE TABLE IF NOT EXISTS totp_secrets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            secret VARCHAR(64) NOT NULL,
            enabled TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_username (username)
        )";
        
        $db->query($query);
    }
    
    /**
     * Δημιουργεί QR code URL για authenticator apps
     */
    private function get_qr_code_url($username, $secret)
    {
        $issuer = $this->rc->config->get('product_name', 'Roundcube');
        $label = urlencode($username . '@' . $issuer);
        
        $otpauth_url = sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            $label,
            $secret,
            urlencode($issuer)
        );
        
        // Χρήση Google Charts API για QR code
        return 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . urlencode($otpauth_url);
    }
    
    /**
     * Hook για authentication - έλεγχος TOTP
     */
    public function authenticate($args)
    {
        if (!isset($_SESSION['totp_verified'])) {
            $username = $this->extract_username($args['user']);
            $secret = $this->get_secret($username);
            
            if ($secret) {
                // 2FA είναι ενεργοποιημένο, απαιτείται verification
                $_SESSION['pending_2fa'] = true;
                $_SESSION['pending_2fa_user'] = $args['user'];
                $_SESSION['pending_2fa_pass'] = $args['pass'];
                
                // Redirect to 2FA verification page
                $this->rc->output->redirect(array('action' => 'plugin.totp_unified.verify'));
                $args['abort'] = true;
            }
        }
        
        return $args;
    }
    
    /**
     * Verification page για TOTP code
     */
    public function verify_code()
    {
        $this->register_handler('plugin.body', array($this, 'verify_form'));
        $this->rc->output->set_pagetitle($this->gettext('2fa_verification'));
        $this->rc->output->send('plugin');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['totp_code'])) {
            $username = $this->extract_username($_SESSION['pending_2fa_user']);
            $secret = $this->get_secret($username);
            $code = preg_replace('/\s+/', '', $_POST['totp_code']);
            
            // Verify code (με tolerance ±1 time step για clock skew)
            $valid = false;
            for ($i = -1; $i <= 1; $i++) {
                $time = time() + ($i * 30);
                if ($code === $this->calculate_totp($secret, $time)) {
                    $valid = true;
                    break;
                }
            }
            
            if ($valid) {
                $_SESSION['totp_verified'] = true;
                
                // Complete login
                $user = rcube_user::query($_SESSION['pending_2fa_user'], $_SESSION['pending_2fa_pass']);
                
                if ($user) {
                    $this->rc->login($user->ID, $_SESSION['pending_2fa_user']);
                    $this->rc->output->redirect(array('task' => 'mail'));
                }
            } else {
                $this->rc->output->show_message('Invalid verification code', 'error');
            }
        }
    }
    
    /**
     * HTML form για TOTP verification
     */
    public function verify_form()
    {
        $html = '
        <div class="totp-verify-container">
            <h2>Two-Factor Authentication</h2>
            <p>Enter the 6-digit code from your authenticator app:</p>
            <form method="post" action="">
                <input type="text" name="totp_code" maxlength="6" pattern="[0-9]{6}" 
                       placeholder="000000" autocomplete="off" autofocus required>
                <button type="submit">Verify</button>
            </form>
        </div>';
        
        return $html;
    }
    
    /**
     * Settings interface για setup 2FA
     */
    public function preferences_list($args)
    {
        if ($args['section'] == 'server') {
            $username = $this->extract_username($this->rc->user->get_username());
            $secret = $this->get_secret($username);
            
            if (!$secret) {
                // 2FA not enabled - show setup option
                $args['blocks']['totp']['name'] = 'Two-Factor Authentication';
                $args['blocks']['totp']['options']['setup'] = array(
                    'title' => 'Enable 2FA',
                    'content' => '<button onclick="setup2FA()">Setup 2FA</button>'
                );
            } else {
                // 2FA enabled - show disable option
                $args['blocks']['totp']['name'] = 'Two-Factor Authentication';
                $args['blocks']['totp']['options']['status'] = array(
                    'title' => 'Status',
                    'content' => '<span style="color: green;">✓ Enabled</span>'
                );
                $args['blocks']['totp']['options']['disable'] = array(
                    'title' => 'Disable 2FA',
                    'content' => '<button onclick="disable2FA()">Disable 2FA</button>'
                );
            }
        }
        
        return $args;
    }
    
    /**
     * Setup 2FA - δημιουργία secret και QR code
     */
    public function setup_2fa()
    {
        $username = $this->extract_username($this->rc->user->get_username());
        $secret = $this->generate_secret();
        
        $this->save_secret($username, $secret);
        
        $qr_url = $this->get_qr_code_url($username, $secret);
        
        $response = array(
            'success' => true,
            'secret' => $secret,
            'qr_url' => $qr_url,
            'username' => $username
        );
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Disable 2FA
     */
    public function disable_2fa()
    {
        $username = $this->extract_username($this->rc->user->get_username());
        $db = $this->rc->get_dbh();
        
        $query = "UPDATE totp_secrets SET enabled = 0 WHERE username = ?";
        $db->query($query, $username);
        
        echo json_encode(array('success' => true));
        exit;
    }
}
