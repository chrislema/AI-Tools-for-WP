<?php
/**
 * Main Plugin Class
 *
 * @package AI_Tools_For_WP
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AITWP_Plugin
 *
 * Main plugin controller that initializes all components.
 */
class AITWP_Plugin {

    /**
     * Single instance of this class.
     *
     * @var AITWP_Plugin|null
     */
    private static $instance = null;

    /**
     * Settings instance.
     *
     * @var AITWP_Settings|null
     */
    private $settings = null;

    /**
     * Get the singleton instance.
     *
     * @return AITWP_Plugin
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->init_hooks();
        $this->init_components();
    }

    /**
     * Initialize WordPress hooks.
     */
    private function init_hooks() {
        // Register REST API routes
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

        // Enqueue editor assets
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
    }

    /**
     * Initialize plugin components.
     */
    private function init_components() {
        // Initialize settings page (admin only)
        if ( is_admin() ) {
            $this->settings = new AITWP_Settings();
        }
    }

    /**
     * Register REST API routes.
     */
    public function register_rest_routes() {
        $namespace = 'ai-tools/v1';

        // Categorize endpoint
        register_rest_route( $namespace, '/categorize', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_categorize' ),
            'permission_callback' => array( $this, 'check_edit_permission' ),
        ) );

        // Rewrite endpoint
        register_rest_route( $namespace, '/rewrite', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_rewrite' ),
            'permission_callback' => array( $this, 'check_edit_permission' ),
        ) );

        // Suggest audience endpoint
        register_rest_route( $namespace, '/suggest-audience', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_suggest_audience' ),
            'permission_callback' => array( $this, 'check_edit_permission' ),
        ) );

        // Get voice profiles
        register_rest_route( $namespace, '/voice-profiles', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_voice_profiles' ),
            'permission_callback' => array( $this, 'check_edit_permission' ),
        ) );

        // Get audiences
        register_rest_route( $namespace, '/audiences', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_audiences' ),
            'permission_callback' => array( $this, 'check_edit_permission' ),
        ) );
    }

    /**
     * Check if user has edit permission.
     *
     * @return bool
     */
    public function check_edit_permission() {
        return current_user_can( 'edit_posts' );
    }

    /**
     * Handle categorize request.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_categorize( $request ) {
        $content     = $request->get_param( 'content' );
        $audience_id = $request->get_param( 'audience_id' );

        if ( empty( $content ) ) {
            return new WP_Error( 'missing_content', __( 'Content is required.', 'ai-tools-for-wp' ), array( 'status' => 400 ) );
        }

        $categorizer = new AITWP_Categorizer();
        $result      = $categorizer->analyze( $content, $audience_id );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response( $result );
    }

    /**
     * Handle rewrite request.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_rewrite( $request ) {
        $content          = $request->get_param( 'content' );
        $voice_profile_id = $request->get_param( 'voice_profile_id' );
        $audience_id      = $request->get_param( 'audience_id' );

        if ( empty( $content ) ) {
            return new WP_Error( 'missing_content', __( 'Content is required.', 'ai-tools-for-wp' ), array( 'status' => 400 ) );
        }

        $rewriter = new AITWP_Rewriter();
        $result   = $rewriter->rewrite( $content, $voice_profile_id, $audience_id );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response( array( 'rewritten_content' => $result ) );
    }

    /**
     * Handle suggest audience request.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_suggest_audience( $request ) {
        $content = $request->get_param( 'content' );

        if ( empty( $content ) ) {
            return new WP_Error( 'missing_content', __( 'Content is required.', 'ai-tools-for-wp' ), array( 'status' => 400 ) );
        }

        $categorizer = new AITWP_Categorizer();
        $result      = $categorizer->suggest_audience( $content );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response( $result );
    }

    /**
     * Get voice profiles.
     *
     * @return WP_REST_Response
     */
    public function get_voice_profiles() {
        $profiles = get_option( 'aitwp_voice_profiles', array() );
        return rest_ensure_response( $profiles );
    }

    /**
     * Get audiences.
     *
     * @return WP_REST_Response
     */
    public function get_audiences() {
        $audiences = get_option( 'aitwp_audiences', array() );
        return rest_ensure_response( $audiences );
    }

    /**
     * Enqueue block editor assets.
     */
    public function enqueue_editor_assets() {
        $asset_file = AITWP_PLUGIN_DIR . 'editor/build/index.asset.php';

        if ( file_exists( $asset_file ) ) {
            $asset = include $asset_file;
        } else {
            $asset = array(
                'dependencies' => array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-api-fetch' ),
                'version'      => AITWP_VERSION,
            );
        }

        // Only enqueue if build exists
        if ( file_exists( AITWP_PLUGIN_DIR . 'editor/build/index.js' ) ) {
            wp_enqueue_script(
                'aitwp-editor',
                AITWP_PLUGIN_URL . 'editor/build/index.js',
                $asset['dependencies'],
                $asset['version'],
                true
            );

            // Pass data to JavaScript
            wp_localize_script( 'aitwp-editor', 'aitwpData', array(
                'restUrl'   => rest_url( 'ai-tools/v1/' ),
                'nonce'     => wp_create_nonce( 'wp_rest' ),
                'autoApply' => get_option( 'aitwp_auto_apply_tags', false ),
            ) );
        }

        // Enqueue editor styles if they exist
        if ( file_exists( AITWP_PLUGIN_DIR . 'assets/css/editor.css' ) ) {
            wp_enqueue_style(
                'aitwp-editor-styles',
                AITWP_PLUGIN_URL . 'assets/css/editor.css',
                array(),
                AITWP_VERSION
            );
        }
    }
}
