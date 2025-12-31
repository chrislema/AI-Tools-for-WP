# [Security] Weak encryption detection method

## Priority: High

## Description
The `is_encrypted()` method only checks if a value is base64 encoded, not if it's actually encrypted. Many regular strings are valid base64.

## Location
`includes/class-encryption.php:140-144`

```php
public static function is_encrypted( $value ) {
    $decoded = base64_decode( $value, true );
    return false !== $decoded && $decoded !== $value;
}
```

## Problem
- False positives: Many strings that aren't encrypted will pass this check
- Could lead to double-encryption or decryption failures
- Example: "Hello" is valid base64 and would be incorrectly identified as encrypted

## Recommendation
Use a prefix marker to identify encrypted values:
```php
private const ENCRYPTED_PREFIX = 'enc:';

public static function encrypt( $value ) {
    // ... encryption logic ...
    return self::ENCRYPTED_PREFIX . base64_encode( $encrypted );
}

public static function is_encrypted( $value ) {
    return strpos( $value, self::ENCRYPTED_PREFIX ) === 0;
}
```

## Labels
`security`, `bug`
