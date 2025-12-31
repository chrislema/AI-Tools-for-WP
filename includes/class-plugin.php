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
     * Rate limit: maximum requests per minute.
     *
     * @var int
     */
    private const RATE_LIMIT_MAX_REQUESTS = 10;

    /**
     * Rate limit: time window in seconds.
     *
     * @var int
     */
    private const RATE_LIMIT_WINDOW = 60;

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
            'args'                => array(
                'content'     => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'wp_kses_post',
                    'validate_callback' => array( $this, 'validate_content' ),
                    'description'       => __( 'The post content to analyze.', 'ai-tools-for-wp' ),
                ),
                'audience_id' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => '',
                    'description'       => __( 'Optional audience ID for context.', 'ai-tools-for-wp' ),
                ),
            ),
        ) );

        // Rewrite endpoint
        register_rest_route( $namespace, '/rewrite', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_rewrite' ),
            'permission_callback' => array( $this, 'check_edit_permission' ),
            'args'                => array(
                'content'          => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'wp_kses_post',
                    'validate_callback' => array( $this, 'validate_content' ),
                    'description'       => __( 'The content to rewrite.', 'ai-tools-for-wp' ),
                ),
                'voice_profile_id' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => array( $this, 'validate_voice_profile_id' ),
                    'description'       => __( 'The voice profile ID to use.', 'ai-tools-for-wp' ),
                ),
                'audience_id'      => array(
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => '',
                    'description'       => __( 'Optional audience ID for context.', 'ai-tools-for-wp' ),
                ),
            ),
        ) );

        // Suggest audience endpoint
        register_rest_route( $namespace, '/suggest-audience', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_suggest_audience' ),
            'permission_callback' => array( $this, 'check_edit_permission' ),
            'args'                => array(
                'content' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'wp_kses_post',
                    'validate_callback' => array( $this, 'validate_content' ),
                    'description'       => __( 'The post content to analyze.', 'ai-tools-for-wp' ),
                ),
            ),
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
     * Validate content parameter.
     *
     * @param mixed           $value   The parameter value.
     * @param WP_REST_Request $request The request object.
     * @param string          $param   The parameter name.
     * @return bool|WP_Error
     */
    public function validate_content( $value, $request, $param ) {
        if ( ! is_string( $value ) ) {
            return new WP_Error(
                'rest_invalid_param',
                sprintf(
                    /* translators: %s: parameter name */
                    __( '%s must be a string.', 'ai-tools-for-wp' ),
                    $param
                ),
                array( 'status' => 400 )
            );
        }

        $trimmed = trim( wp_strip_all_tags( $value ) );
        if ( strlen( $trimmed ) < 50 ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Content must be at least 50 characters.', 'ai-tools-for-wp' ),
                array( 'status' => 400 )
            );
        }

        return true;
    }

    /**
     * Validate voice profile ID parameter.
     *
     * @param mixed           $value   The parameter value.
     * @param WP_REST_Request $request The request object.
     * @param string          $param   The parameter name.
     * @return bool|WP_Error
     */
    public function validate_voice_profile_id( $value, $request, $param ) {
        if ( empty( $value ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Voice profile ID is required.', 'ai-tools-for-wp' ),
                array( 'status' => 400 )
            );
        }

        $profiles = get_option( 'aitwp_voice_profiles', array() );
        if ( ! isset( $profiles[ $value ] ) ) {
            return new WP_Error(
                'rest_invalid_param',
                __( 'Invalid voice profile ID.', 'ai-tools-for-wp' ),
                array( 'status' => 400 )
            );
        }

        return true;
    }

    /**
     * Check rate limit for current user.
     *
     * @return bool|WP_Error True if within limit, WP_Error if exceeded.
     */
    private function check_rate_limit() {
        $user_id = get_current_user_id();
        $transient_key = 'aitwp_rate_limit_' . $user_id;

        $requests = get_transient( $transient_key );
        if ( false === $requests ) {
            $requests = 0;
        }

        /**
         * Filter the maximum number of AI requests per minute.
         *
         * @param int $max_requests Maximum requests allowed.
         * @param int $user_id      Current user ID.
         */
        $max_requests = apply_filters( 'aitwp_rate_limit_max_requests', self::RATE_LIMIT_MAX_REQUESTS, $user_id );

        if ( $requests >= $max_requests ) {
            return new WP_Error(
                'rate_limit_exceeded',
                __( 'Too many requests. Please wait a moment before trying again.', 'ai-tools-for-wp' ),
                array( 'status' => 429 )
            );
        }

        // Increment counter
        set_transient( $transient_key, $requests + 1, self::RATE_LIMIT_WINDOW );

        return true;
    }

    /**
     * Handle categorize request.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_categorize( $request ) {
        // Check rate limit
        $rate_check = $this->check_rate_limit();
        if ( is_wp_error( $rate_check ) ) {
            return $rate_check;
        }

        // Parameters are already sanitized by REST API schema
        $content     = $request->get_param( 'content' );
        $audience_id = $request->get_param( 'audience_id' );

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
        // Check rate limit
        $rate_check = $this->check_rate_limit();
        if ( is_wp_error( $rate_check ) ) {
            return $rate_check;
        }

        // Parameters are already sanitized by REST API schema
        $content          = $request->get_param( 'content' );
        $voice_profile_id = $request->get_param( 'voice_profile_id' );
        $audience_id      = $request->get_param( 'audience_id' );

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
        // Check rate limit
        $rate_check = $this->check_rate_limit();
        if ( is_wp_error( $rate_check ) ) {
            return $rate_check;
        }

        // Parameters are already sanitized by REST API schema
        $content = $request->get_param( 'content' );

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
