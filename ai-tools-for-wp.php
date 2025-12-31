<?php
/**
 * Plugin Name: AI Tools for WP
 * Plugin URI: https://github.com/chrislema/AI-Tools-for-WP
 * Description: AI-powered content tools for WordPress - auto-categorization, tagging, and voice-based content rewriting.
 * Version: 1.0.0
 * Author: Chris Lema
 * Author URI: https://chrislema.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-tools-for-wp
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'AITWP_VERSION', '1.0.0' );
define( 'AITWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AITWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AITWP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoloader for plugin classes.
 *
 * @param string $class_name The class name to load.
 */
function aitwp_autoloader( $class_name ) {
    // Only autoload our classes
    if ( strpos( $class_name, 'AITWP_' ) !== 0 ) {
        return;
    }

    // Convert class name to file name
    $class_file = str_replace( 'AITWP_', '', $class_name );
    $class_file = strtolower( $class_file );
    $class_file = str_replace( '_', '-', $class_file );
    $class_file = 'class-' . $class_file . '.php';

    // Check includes directory
    $file_path = AITWP_PLUGIN_DIR . 'includes/' . $class_file;
    if ( file_exists( $file_path ) ) {
        require_once $file_path;
        return;
    }

    // Check admin directory
    $file_path = AITWP_PLUGIN_DIR . 'admin/' . $class_file;
    if ( file_exists( $file_path ) ) {
        require_once $file_path;
        return;
    }
}
spl_autoload_register( 'aitwp_autoloader' );

/**
 * Plugin activation hook.
 */
function aitwp_activate() {
    // Set default options
    $defaults = array(
        'default_provider'  => 'openai',
        'auto_apply_tags'   => false,
        'voice_profiles'    => array(),
        'audiences'         => array(),
    );

    foreach ( $defaults as $key => $value ) {
        if ( get_option( 'aitwp_' . $key ) === false ) {
            add_option( 'aitwp_' . $key, $value );
        }
    }

    // Flush rewrite rules for REST API
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'aitwp_activate' );

/**
 * Plugin deactivation hook.
 */
function aitwp_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'aitwp_deactivate' );

/**
 * Initialize the plugin.
 */
function aitwp_init() {
    // Load text domain for translations
    load_plugin_textdomain( 'ai-tools-for-wp', false, dirname( AITWP_PLUGIN_BASENAME ) . '/languages' );

    // Initialize main plugin class
    if ( class_exists( 'AITWP_Plugin' ) ) {
        AITWP_Plugin::get_instance();
    }
}
add_action( 'plugins_loaded', 'aitwp_init' );

/**
 * Add settings link to plugins page.
 *
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links.
 */
function aitwp_plugin_action_links( $links ) {
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        admin_url( 'options-general.php?page=ai-tools-for-wp' ),
        __( 'Settings', 'ai-tools-for-wp' )
    );
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . AITWP_PLUGIN_BASENAME, 'aitwp_plugin_action_links' );
