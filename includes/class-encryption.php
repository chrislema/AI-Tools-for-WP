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
 */
class AITWP_Encryption {

    /**
     * Encryption method.
     */
    private const METHOD = 'aes-256-cbc';

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
     * Get the initialization vector from WordPress salts.
     *
     * @return string
     */
    private static function get_iv() {
        $salt = defined( 'AUTH_SALT' ) ? AUTH_SALT : 'default-salt';
        $salt .= defined( 'SECURE_AUTH_SALT' ) ? SECURE_AUTH_SALT : '';
        return substr( hash( 'sha256', $salt, true ), 0, 16 );
    }

    /**
     * Encrypt a value.
     *
     * @param string $value The value to encrypt.
     * @return string|false The encrypted value (base64 encoded) or false on failure.
     */
    public static function encrypt( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        if ( ! function_exists( 'openssl_encrypt' ) ) {
            // Fallback: base64 encode if OpenSSL not available
            return base64_encode( $value );
        }

        $encrypted = openssl_encrypt(
            $value,
            self::METHOD,
            self::get_key(),
            0,
            self::get_iv()
        );

        if ( false === $encrypted ) {
            return false;
        }

        return base64_encode( $encrypted );
    }

    /**
     * Decrypt a value.
     *
     * @param string $value The encrypted value (base64 encoded).
     * @return string|false The decrypted value or false on failure.
     */
    public static function decrypt( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        if ( ! function_exists( 'openssl_decrypt' ) ) {
            // Fallback: base64 decode if OpenSSL not available
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
            self::get_iv()
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
     * Check if a value looks like it's already encrypted.
     *
     * @param string $value The value to check.
     * @return bool
     */
    public static function is_encrypted( $value ) {
        // Check if it's base64 encoded and decodes to something that looks encrypted
        $decoded = base64_decode( $value, true );
        return false !== $decoded && $decoded !== $value;
    }
}
