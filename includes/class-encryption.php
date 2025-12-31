<?php
/**
 * Encryption Class
 *
 * Handles encryption and decryption of sensitive data like API keys.
 *
 * @package AI_Tools_For_WP
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AITWP_Encryption
 *
 * Provides encryption/decryption using WordPress salts.
 * Uses random IV for each encryption operation for proper security.
 */
class AITWP_Encryption {

    /**
     * Encryption method.
     */
    private const METHOD = 'aes-256-cbc';

    /**
     * Prefix to identify encrypted values.
     */
    private const ENCRYPTED_PREFIX = 'aitwp_enc_v2:';

    /**
     * IV length for AES-256-CBC.
     */
    private const IV_LENGTH = 16;

    /**
     * Get the encryption key derived from WordPress salts.
     *
     * @return string
     */
    private static function get_key() {
        $salt = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'default-key';
        $salt .= defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : '';
        return hash( 'sha256', $salt, true );
    }

    /**
     * Generate a random initialization vector.
     *
     * @return string
     */
    private static function generate_iv() {
        if ( function_exists( 'random_bytes' ) ) {
            return random_bytes( self::IV_LENGTH );
        }
        // Fallback for older PHP versions
        return openssl_random_pseudo_bytes( self::IV_LENGTH );
    }

    /**
     * Get legacy static IV for backwards compatibility.
     *
     * @return string
     */
    private static function get_legacy_iv() {
        $salt = defined( 'AUTH_SALT' ) ? AUTH_SALT : 'default-salt';
        $salt .= defined( 'SECURE_AUTH_SALT' ) ? SECURE_AUTH_SALT : '';
        return substr( hash( 'sha256', $salt, true ), 0, self::IV_LENGTH );
    }

    /**
     * Encrypt a value.
     *
     * Uses a random IV prepended to the ciphertext for proper security.
     *
     * @param string $value The value to encrypt.
     * @return string|false The encrypted value or false on failure.
     */
    public static function encrypt( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        // Don't re-encrypt already encrypted values
        if ( self::is_encrypted( $value ) ) {
            return $value;
        }

        if ( ! function_exists( 'openssl_encrypt' ) ) {
            // Fallback: base64 encode if OpenSSL not available (not secure, but functional)
            return self::ENCRYPTED_PREFIX . base64_encode( $value );
        }

        $iv = self::generate_iv();

        $encrypted = openssl_encrypt(
            $value,
            self::METHOD,
            self::get_key(),
            OPENSSL_RAW_DATA,
            $iv
        );

        if ( false === $encrypted ) {
            return false;
        }

        // Prepend IV to ciphertext and encode
        return self::ENCRYPTED_PREFIX . base64_encode( $iv . $encrypted );
    }

    /**
     * Decrypt a value.
     *
     * Handles both new format (random IV) and legacy format (static IV).
     *
     * @param string $value The encrypted value.
     * @return string|false The decrypted value or false on failure.
     */
    public static function decrypt( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        // Handle v2 encryption (random IV)
        if ( strpos( $value, self::ENCRYPTED_PREFIX ) === 0 ) {
            return self::decrypt_v2( $value );
        }

        // Try legacy format without prefix (for backwards compatibility)
        return self::decrypt_legacy( $value );
    }

    /**
     * Decrypt v2 encrypted value (random IV).
     *
     * @param string $value The encrypted value with prefix.
     * @return string|false The decrypted value or false on failure.
     */
    private static function decrypt_v2( $value ) {
        if ( ! function_exists( 'openssl_decrypt' ) ) {
            // Fallback: base64 decode if OpenSSL not available
            $data = substr( $value, strlen( self::ENCRYPTED_PREFIX ) );
            return base64_decode( $data );
        }

        $data = substr( $value, strlen( self::ENCRYPTED_PREFIX ) );
        $decoded = base64_decode( $data );

        if ( false === $decoded || strlen( $decoded ) < self::IV_LENGTH ) {
            return false;
        }

        // Extract IV from beginning of decoded data
        $iv = substr( $decoded, 0, self::IV_LENGTH );
        $ciphertext = substr( $decoded, self::IV_LENGTH );

        $decrypted = openssl_decrypt(
            $ciphertext,
            self::METHOD,
            self::get_key(),
            OPENSSL_RAW_DATA,
            $iv
        );

        return $decrypted;
    }

    /**
     * Decrypt legacy encrypted value (static IV without prefix).
     *
     * @param string $value The encrypted value without prefix.
     * @return string|false The decrypted value or false on failure.
     */
    private static function decrypt_legacy( $value ) {
        if ( ! function_exists( 'openssl_decrypt' ) ) {
            return base64_decode( $value );
        }

        $decoded = base64_decode( $value );
        if ( false === $decoded ) {
            return false;
        }

        $decrypted = openssl_decrypt(
            $decoded,
            self::METHOD,
            self::get_key(),
            0,
            self::get_legacy_iv()
        );

        return $decrypted;
    }

    /**
     * Mask a sensitive value for display.
     *
     * @param string $value The value to mask.
     * @param int    $visible_chars Number of characters to show at the end.
     * @return string The masked value.
     */
    public static function mask( $value, $visible_chars = 4 ) {
        if ( empty( $value ) ) {
            return '';
        }

        $length = strlen( $value );

        if ( $length <= $visible_chars ) {
            return str_repeat( '*', $length );
        }

        $masked_length = $length - $visible_chars;
        return str_repeat( '*', $masked_length ) . substr( $value, -$visible_chars );
    }

    /**
     * Check if a value is encrypted by this class.
     *
     * @param string $value The value to check.
     * @return bool
     */
    public static function is_encrypted( $value ) {
        if ( empty( $value ) ) {
            return false;
        }

        // Check for v2 prefix
        return strpos( $value, self::ENCRYPTED_PREFIX ) === 0;
    }

    /**
     * Migrate a legacy encrypted value to v2 format.
     *
     * @param string $encrypted_value The legacy encrypted value.
     * @return string|false The v2 encrypted value or false on failure.
     */
    public static function migrate_to_v2( $encrypted_value ) {
        // Already v2
        if ( strpos( $encrypted_value, self::ENCRYPTED_PREFIX ) === 0 ) {
            return $encrypted_value;
        }

        // Decrypt with legacy method
        $decrypted = self::decrypt( $encrypted_value );

        if ( false === $decrypted || empty( $decrypted ) ) {
            return false;
        }

        // Re-encrypt with v2
        return self::encrypt( $decrypted );
    }
}
