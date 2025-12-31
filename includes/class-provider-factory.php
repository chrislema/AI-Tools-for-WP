<?php
/**
 * AI Provider Factory Class
 *
 * Factory for creating AI provider instances.
 *
 * @package AI_Tools_For_WP
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AITWP_Provider_Factory
 *
 * Creates and manages AI provider instances.
 */
class AITWP_Provider_Factory {

    /**
     * Registered providers.
     *
     * @var array
     */
    private static $providers = array(
        'openai'    => 'AITWP_OpenAI',
        'anthropic' => 'AITWP_Anthropic',
    );

    /**
     * Cached provider instances.
     *
     * @var array
     */
    private static $instances = array();

    /**
     * Get a provider instance.
     *
     * @param string $provider_id Optional. Provider ID. Defaults to configured default.
     * @return AITWP_AI_Provider|WP_Error
     */
    public static function get_provider( $provider_id = '' ) {
        // Use default provider if not specified
        if ( empty( $provider_id ) ) {
            $provider_id = get_option( 'aitwp_default_provider', 'openai' );
        }

        // Return cached instance if available
        if ( isset( self::$instances[ $provider_id ] ) ) {
            return self::$instances[ $provider_id ];
        }

        // Check if provider is registered
        if ( ! isset( self::$providers[ $provider_id ] ) ) {
            return new WP_Error(
                'invalid_provider',
                sprintf(
                    /* translators: %s: provider ID */
                    __( 'Invalid AI provider: %s', 'ai-tools-for-wp' ),
                    $provider_id
                )
            );
        }

        // Get API key for this provider
        $api_key = self::get_api_key( $provider_id );

        // Create instance
        $class_name = self::$providers[ $provider_id ];
        $instance   = new $class_name( $api_key );

        // Cache instance
        self::$instances[ $provider_id ] = $instance;

        return $instance;
    }

    /**
     * Get the API key for a provider.
     *
     * @param string $provider_id The provider ID.
     * @return string The encrypted API key.
     */
    private static function get_api_key( $provider_id ) {
        switch ( $provider_id ) {
            case 'openai':
                return get_option( 'aitwp_openai_key', '' );
            case 'anthropic':
                return get_option( 'aitwp_anthropic_key', '' );
            default:
                return '';
        }
    }

    /**
     * Get all registered providers.
     *
     * @return array Array of provider ID => display name.
     */
    public static function get_registered_providers() {
        $providers = array();

        foreach ( self::$providers as $id => $class ) {
            $instance         = self::get_provider( $id );
            if ( ! is_wp_error( $instance ) ) {
                $providers[ $id ] = array(
                    'name'       => $instance->get_name(),
                    'configured' => $instance->is_configured(),
                );
            }
        }

        return $providers;
    }

    /**
     * Check if a provider is configured.
     *
     * @param string $provider_id The provider ID.
     * @return bool
     */
    public static function is_provider_configured( $provider_id ) {
        $provider = self::get_provider( $provider_id );

        if ( is_wp_error( $provider ) ) {
            return false;
        }

        return $provider->is_configured();
    }

    /**
     * Check if any provider is configured.
     *
     * @return bool
     */
    public static function has_configured_provider() {
        foreach ( array_keys( self::$providers ) as $provider_id ) {
            if ( self::is_provider_configured( $provider_id ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the first configured provider.
     *
     * @return AITWP_AI_Provider|WP_Error
     */
    public static function get_configured_provider() {
        // First try the default provider
        $default_id = get_option( 'aitwp_default_provider', 'openai' );
        if ( self::is_provider_configured( $default_id ) ) {
            return self::get_provider( $default_id );
        }

        // Otherwise find any configured provider
        foreach ( array_keys( self::$providers ) as $provider_id ) {
            if ( self::is_provider_configured( $provider_id ) ) {
                return self::get_provider( $provider_id );
            }
        }

        return new WP_Error(
            'no_provider',
            __( 'No AI provider is configured. Please add an API key in the settings.', 'ai-tools-for-wp' )
        );
    }

    /**
     * Register a custom provider.
     *
     * @param string $provider_id The unique provider ID.
     * @param string $class_name  The provider class name (must extend AITWP_AI_Provider).
     * @return bool
     */
    public static function register_provider( $provider_id, $class_name ) {
        if ( ! class_exists( $class_name ) ) {
            return false;
        }

        if ( ! is_subclass_of( $class_name, 'AITWP_AI_Provider' ) ) {
            return false;
        }

        self::$providers[ $provider_id ] = $class_name;

        return true;
    }

    /**
     * Clear cached instances.
     */
    public static function clear_cache() {
        self::$instances = array();
    }
}
