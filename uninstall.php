<?php
/**
 * AI Tools for WP Uninstall
 *
 * Cleans up plugin data when uninstalled.
 *
 * @package AI_Tools_For_WP
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Delete all plugin options from the database.
 */
function aitwp_uninstall_cleanup() {
    // List of all options used by the plugin
    $options = array(
        'aitwp_openai_key',
        'aitwp_anthropic_key',
        'aitwp_default_provider',
        'aitwp_auto_apply_tags',
        'aitwp_voice_profiles',
        'aitwp_audiences',
    );

    // Delete each option
    foreach ( $options as $option ) {
        delete_option( $option );
    }

    // Clean up any transients
    global $wpdb;
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like( '_transient_aitwp_' ) . '%'
        )
    );
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like( '_transient_timeout_aitwp_' ) . '%'
        )
    );
}

// Run cleanup
aitwp_uninstall_cleanup();
