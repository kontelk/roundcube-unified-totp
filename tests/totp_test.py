#!/usr/bin/env python3
"""
TOTP Testing Script για Roundcube 2FA Plugin
Αυτό το script σου επιτρέπει να δοκιμάσεις τη λογική του TOTP πριν την υλοποιήσεις
"""

import hmac
import hashlib
import time
import base64
import struct
import secrets
import string
import qrcode
from io import BytesIO

class TOTPGenerator:
    """
    Κλάση για δημιουργία και επαλήθευση TOTP codes
    """
    
    def __init__(self, secret=None, digits=6, period=30):
        """
        Args:
            secret: Base32 encoded secret (αν None, δημιουργείται νέο)
            digits: Μήκος OTP code (συνήθως 6)
            period: Time step σε seconds (συνήθως 30)
        """
        self.digits = digits
        self.period = period
        
        if secret is None:
            self.secret = self.generate_secret()
        else:
            self.secret = secret.upper().replace(' ', '')
    
    @staticmethod
    def generate_secret(length=32):
        """
        Δημιουργεί ένα τυχαίο Base32 secret
        """
        alphabet = string.ascii_uppercase + '234567'  # Base32 alphabet
        return ''.join(secrets.choice(alphabet) for _ in range(length))
    
    @staticmethod
    def base32_decode(secret):
        """
        Decode Base32 string σε bytes
        """
        # Προσθήκη padding αν χρειάζεται
        missing_padding = len(secret) % 8
        if missing_padding:
            secret += '=' * (8 - missing_padding)
        return base64.b32decode(secret, casefold=True)
    
    def get_time_step(self, timestamp=None):
        """
        Υπολογίζει το time step
        """
        if timestamp is None:
            timestamp = time.time()
        return int(timestamp) // self.period
    
    def generate_otp(self, timestamp=None):
        """
        Δημιουργεί TOTP code για το συγκεκριμένο timestamp
        """
        # Decode secret
        key = self.base32_decode(self.secret)
        
        # Get time step
        time_step = self.get_time_step(timestamp)
        
        # Convert to 8-byte big-endian
        time_bytes = struct.pack('>Q', time_step)
        
        # HMAC-SHA1
        hmac_hash = hmac.new(key, time_bytes, hashlib.sha1).digest()
        
        # Dynamic truncation
        offset = hmac_hash[-1] & 0x0F
        truncated = struct.unpack('>I', hmac_hash[offset:offset+4])[0]
        truncated &= 0x7FFFFFFF
        
        # Generate OTP
        otp = truncated % (10 ** self.digits)
        
        return str(otp).zfill(self.digits)
    
    def verify_otp(self, otp, timestamp=None, tolerance=1):
        """
        Επαληθεύει ένα OTP code με tolerance για clock skew
        
        Args:
            otp: Το OTP code προς επαλήθευση
            timestamp: Timestamp για έλεγχο (None = τώρα)
            tolerance: Πόσα time steps να ελέγξει (±tolerance)
        
        Returns:
            True αν το OTP είναι έγκυρο, False διαφορετικά
        """
        if timestamp is None:
            timestamp = time.time()
        
        current_step = self.get_time_step(timestamp)
        
        # Έλεγχος για κάθε time step μέσα στο tolerance window
        for offset in range(-tolerance, tolerance + 1):
            test_time = (current_step + offset) * self.period
            if self.generate_otp(test_time) == otp:
                return True
        
        return False
    
    def get_qr_code_uri(self, username, issuer="Roundcube"):
        """
        Δημιουργεί το otpauth:// URI για QR code
        """
        uri = (f"otpauth://totp/{issuer}:{username}"
               f"?secret={self.secret}"
               f"&issuer={issuer}"
               f"&algorithm=SHA1"
               f"&digits={self.digits}"
               f"&period={self.period}")
        return uri
    
    def generate_qr_code(self, username, issuer="Roundcube", save_path=None):
        """
        Δημιουργεί QR code για το authenticator app
        """
        uri = self.get_qr_code_uri(username, issuer)
        
        qr = qrcode.QRCode(
            version=1,
            error_correction=qrcode.constants.ERROR_CORRECT_L,
            box_size=10,
            border=4,
        )
        qr.add_data(uri)
        qr.make(fit=True)
        
        img = qr.make_image(fill_color="black", back_color="white")
        
        if save_path:
            img.save(save_path)
            print(f"QR code saved to: {save_path}")
        
        return img


def extract_username(email):
    """
    Εξάγει το username από email address
    """
    return email.split('@')[0].lower()


def simulate_login_flow():
    """
    Προσομοιώνει το flow του login με 2FA
    """
    print("=" * 70)
    print("ROUNDCUBE 2FA SIMULATION - LOGIN FLOW")
    print("=" * 70)
    
    # Email addresses με κοινό username
    emails = [
        "username1@domain1.com",
        "username1@alias1.com",
        "username1@alias2.com"
    ]
    
    # Extract common username
    username = extract_username(emails[0])
    print(f"\nCommon username extracted: {username}")
    print(f"This username works for all these emails:")
    for email in emails:
        print(f"  - {email}")
    
    # Create TOTP generator
    totp = TOTPGenerator()
    print(f"\nGenerated Secret: {totp.secret}")
    print(f"Secret Length: {len(totp.secret)} characters")
    
    # Generate QR code
    print("\n" + "-" * 70)
    print("QR CODE URI:")
    print("-" * 70)
    uri = totp.get_qr_code_uri(username, "MyMailServer")
    print(uri)
    
    # Generate current OTP
    print("\n" + "-" * 70)
    print("CURRENT OTP CODES:")
    print("-" * 70)
    
    current_time = time.time()
    current_otp = totp.generate_otp(current_time)
    
    print(f"Current timestamp: {int(current_time)}")
    print(f"Current time step: {totp.get_time_step(current_time)}")
    print(f"Current OTP: {current_otp}")
    
    # Show OTP codes for next few time steps
    print("\nNext OTP codes:")
    for i in range(1, 4):
        future_time = current_time + (i * totp.period)
        future_otp = totp.generate_otp(future_time)
        seconds_until = int(future_time - current_time)
        print(f"  In {seconds_until}s: {future_otp}")
    
    # Simulate login with different emails
    print("\n" + "-" * 70)
    print("LOGIN SIMULATION:")
    print("-" * 70)
    
    for email in emails:
        extracted = extract_username(email)
        print(f"\nAttempting login with: {email}")
        print(f"  Extracted username: {extracted}")
        print(f"  Username matches? {extracted == username}")
        
        # Simulate user entering OTP
        user_otp = current_otp  # Προσομοίωση σωστού κωδικού
        is_valid = totp.verify_otp(user_otp, current_time)
        
        print(f"  User entered OTP: {user_otp}")
        print(f"  Verification result: {'✓ SUCCESS' if is_valid else '✗ FAILED'}")
    
    # Test with wrong OTP
    print("\n" + "-" * 70)
    print("TESTING WITH WRONG OTP:")
    print("-" * 70)
    wrong_otp = "999999"
    is_valid = totp.verify_otp(wrong_otp, current_time)
    print(f"User entered OTP: {wrong_otp}")
    print(f"Verification result: {'✓ SUCCESS' if is_valid else '✗ FAILED'}")
    
    # Test tolerance window
    print("\n" + "-" * 70)
    print("TESTING TIME TOLERANCE (for clock skew):")
    print("-" * 70)
    
    # Test με παλιό OTP (30 seconds πριν)
    old_time = current_time - 30
    old_otp = totp.generate_otp(old_time)
    
    print(f"OTP from 30 seconds ago: {old_otp}")
    print(f"  Tolerance 0: {'✓ VALID' if totp.verify_otp(old_otp, current_time, tolerance=0) else '✗ INVALID'}")
    print(f"  Tolerance 1: {'✓ VALID' if totp.verify_otp(old_otp, current_time, tolerance=1) else '✗ INVALID'}")
    
    return totp, username


def demonstrate_database_structure():
    """
    Δείχνει πώς θα έπρεπε να είναι η δομή της βάσης
    """
    print("\n" + "=" * 70)
    print("DATABASE STRUCTURE")
    print("=" * 70)
    
    print("""
    CREATE TABLE totp_secrets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        secret VARCHAR(64) NOT NULL,
        enabled TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username)
    );
    """)
    
    print("\nΠαράδειγμα δεδομένων:")
    print("-" * 70)
    print(f"{'username':<20} {'secret':<35} {'enabled':<10}")
    print("-" * 70)
    
    # Simulate some data
    users = ["username1", "alice", "bob"]
    for user in users:
        totp = TOTPGenerator()
        secret_display = totp.secret[:20] + "..." + totp.secret[-5:]
        print(f"{user:<20} {secret_display:<35} {1:<10}")


if __name__ == "__main__":
    # Run simulation
    totp, username = simulate_login_flow()
    
    # Show database structure
    demonstrate_database_structure()
    
    # Generate QR code image
    print("\n" + "=" * 70)
    print("GENERATING QR CODE IMAGE")
    print("=" * 70)
    
    try:
        totp.generate_qr_code(username, issuer="MyMailServer", save_path="totp_qr_code.png")
    except Exception as e:
        print(f"Could not generate QR code image: {e}")
        print("Install qrcode package: pip install qrcode[pil]")
    
    # Interactive testing
    print("\n" + "=" * 70)
    print("INTERACTIVE TESTING")
    print("=" * 70)
    print("Το OTP code αλλάζει κάθε 30 δευτερόλεπτα.")
    print(f"Τρέχον OTP: {totp.generate_otp()}")
    print("\nΜπορείς να δοκιμάσεις αυτό το secret σε ένα authenticator app!")
    print(f"Secret: {totp.secret}")
    
    # Show time remaining until next code
    current_time = int(time.time())
    seconds_remaining = 30 - (current_time % 30)
    print(f"\nΔευτερόλεπτα μέχρι το επόμενο code: {seconds_remaining}")
    
    print("\n" + "=" * 70)
    print("SIMULATION COMPLETE")
    print("=" * 70)
