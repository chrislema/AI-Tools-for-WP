# [Security] Static IV in AES-256-CBC encryption weakens security

## Priority: Critical

## Description
The encryption class uses a deterministic IV (Initialization Vector) derived from WordPress salts. This means the same IV is used for every encryption operation, which significantly weakens AES-CBC security.

## Location
`includes/class-encryption.php:43-47`

```php
private static function get_iv() {
    $salt = defined( 'AUTH_SALT' ) ? AUTH_SALT : 'default-salt';
    $salt .= defined( 'SECURE_AUTH_SALT' ) ? SECURE_AUTH_SALT : '';
    return substr( hash( 'sha256', $salt, true ), 0, 16 );
}
```

## Problem
- Identical plaintexts will produce identical ciphertexts
- Attackers can detect when the same API key is stored multiple times
- Violates cryptographic best practices for CBC mode

## Recommendation
Generate a random IV for each encryption operation and prepend it to the ciphertext. On decryption, extract the IV from the beginning of the ciphertext.

## Labels
`security`, `bug`, `critical`
