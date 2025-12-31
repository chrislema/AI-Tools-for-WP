<?php
/**
 * Settings Page Class
 *
 * @package AI_Tools_For_WP
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AITWP_Settings
 *
 * Handles the plugin settings page with tabbed interface.
 */
class AITWP_Settings {

    /**
     * Available tabs.
     *
     * @var array
     */
    private $tabs = array();

    /**
     * Current active tab.
     *
     * @var string
     */
    private $current_tab = '';

    /**
     * Constructor.
     */
    public function __construct() {
        $this->tabs = array(
            'voice-profiles' => __( 'Voice Profiles', 'ai-tools-for-wp' ),
            'audiences'      => __( 'Audiences', 'ai-tools-for-wp' ),
            'api-keys'       => __( 'API Keys', 'ai-tools-for-wp' ),
        );

        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_ajax_aitwp_save_voice_profile', array( $this, 'ajax_save_voice_profile' ) );
        add_action( 'wp_ajax_aitwp_delete_voice_profile', array( $this, 'ajax_delete_voice_profile' ) );
        add_action( 'wp_ajax_aitwp_get_profiles_data', array( $this, 'ajax_get_profiles_data' ) );
        add_action( 'wp_ajax_aitwp_save_audience', array( $this, 'ajax_save_audience' ) );
        add_action( 'wp_ajax_aitwp_delete_audience', array( $this, 'ajax_delete_audience' ) );
        add_action( 'wp_ajax_aitwp_get_audiences_data', array( $this, 'ajax_get_audiences_data' ) );
    }

    /**
     * Add the settings menu page.
     */
    public function add_menu_page() {
        add_options_page(
            __( 'AI Tools for WP', 'ai-tools-for-wp' ),
            __( 'AI Tools', 'ai-tools-for-wp' ),
            'edit_posts', // Minimum capability for any tab
            'ai-tools-for-wp',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        // API Keys (admin only)
        register_setting( 'aitwp_api_keys', 'aitwp_openai_key', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_api_key' ),
        ) );

        register_setting( 'aitwp_api_keys', 'aitwp_anthropic_key', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_api_key' ),
        ) );

        register_setting( 'aitwp_api_keys', 'aitwp_default_provider', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'openai',
        ) );

        register_setting( 'aitwp_api_keys', 'aitwp_auto_apply_tags', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => false,
        ) );
    }

    /**
     * Sanitize and encrypt API key.
     *
     * @param string $value The API key value.
     * @return string Encrypted API key.
     */
    public function sanitize_api_key( $value ) {
        $value = sanitize_text_field( $value );

        // Don't re-encrypt if value is masked placeholder
        if ( strpos( $value, '***' ) === 0 ) {
            // Get the current option name being processed
            $option_name = current_filter();
            $option_name = str_replace( 'sanitize_option_', '', $option_name );
            return get_option( $option_name );
        }

        if ( empty( $value ) ) {
            return '';
        }

        return AITWP_Encryption::encrypt( $value );
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_scripts( $hook ) {
        if ( 'settings_page_ai-tools-for-wp' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'aitwp-admin',
            AITWP_PLUGIN_URL . 'admin/css/settings.css',
            array(),
            AITWP_VERSION
        );

        wp_enqueue_script(
            'aitwp-admin',
            AITWP_PLUGIN_URL . 'admin/js/settings.js',
            array( 'jquery' ),
            AITWP_VERSION,
            true
        );

        wp_localize_script( 'aitwp-admin', 'aitwpAdmin', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'aitwp_admin_nonce' ),
            'strings' => array(
                'confirmDelete'   => __( 'Are you sure you want to delete this item?', 'ai-tools-for-wp' ),
                'saveSuccess'     => __( 'Saved successfully.', 'ai-tools-for-wp' ),
                'saveError'       => __( 'Error saving. Please try again.', 'ai-tools-for-wp' ),
                'deleteSuccess'   => __( 'Deleted successfully.', 'ai-tools-for-wp' ),
                'deleteError'     => __( 'Error deleting. Please try again.', 'ai-tools-for-wp' ),
            ),
        ) );
    }

    /**
     * Get the current tab based on permissions and request.
     *
     * @return string
     */
    private function get_current_tab() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $requested_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'voice-profiles';

        // Check if user can access API Keys tab
        if ( 'api-keys' === $requested_tab && ! current_user_can( 'manage_options' ) ) {
            $requested_tab = 'voice-profiles';
        }

        // Validate tab exists
        if ( ! isset( $this->tabs[ $requested_tab ] ) ) {
            $requested_tab = 'voice-profiles';
        }

        return $requested_tab;
    }

    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        $this->current_tab = $this->get_current_tab();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <nav class="nav-tab-wrapper">
                <?php foreach ( $this->tabs as $tab_id => $tab_name ) : ?>
                    <?php
                    // Hide API Keys tab from non-admins
                    if ( 'api-keys' === $tab_id && ! current_user_can( 'manage_options' ) ) {
                        continue;
                    }

                    $class = ( $tab_id === $this->current_tab ) ? 'nav-tab nav-tab-active' : 'nav-tab';
                    $url   = add_query_arg( 'tab', $tab_id, admin_url( 'options-general.php?page=ai-tools-for-wp' ) );
                    ?>
                    <a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class ); ?>">
                        <?php echo esc_html( $tab_name ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="aitwp-settings-content">
                <?php
                switch ( $this->current_tab ) {
                    case 'voice-profiles':
                        include AITWP_PLUGIN_DIR . 'admin/views/tab-voice-profiles.php';
                        break;
                    case 'audiences':
                        include AITWP_PLUGIN_DIR . 'admin/views/tab-audiences.php';
                        break;
                    case 'api-keys':
                        if ( current_user_can( 'manage_options' ) ) {
                            include AITWP_PLUGIN_DIR . 'admin/views/tab-api-keys.php';
                        }
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX handler to save a voice profile.
     */
    public function ajax_save_voice_profile() {
        check_ajax_referer( 'aitwp_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'ai-tools-for-wp' ) );
        }

        // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $id   = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

        if ( empty( $name ) ) {
            wp_send_json_error( __( 'Name is required.', 'ai-tools-for-wp' ) );
        }

        $profiles = get_option( 'aitwp_voice_profiles', array() );

        // Generate new ID if not provided
        if ( empty( $id ) ) {
            $id = 'vp_' . uniqid();
        }

        // Helper function to sanitize textarea
        $sanitize_textarea = function( $key ) {
            return isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : '';
        };

        // Helper function to convert textarea lines to array
        $text_to_array = function( $key ) {
            $text = isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : '';
            return AITWP_Migration::text_to_array( $text );
        };

        $profiles[ $id ] = array(
            'id'      => $id,
            'name'    => $name,
            'updated' => current_time( 'mysql' ),
            'version' => AITWP_Migration::CURRENT_VERSION,

            // Voice Identity
            'voice_identity' => $sanitize_textarea( 'voice_identity' ),

            // Tone & Energy
            'tone_energy' => array(
                'energy_level'    => isset( $_POST['tone_energy_level'] ) ? sanitize_text_field( wp_unslash( $_POST['tone_energy_level'] ) ) : 'medium',
                'humor_style'     => isset( $_POST['tone_humor_style'] ) ? sanitize_text_field( wp_unslash( $_POST['tone_humor_style'] ) ) : 'subtle',
                'emotional_range' => isset( $_POST['tone_emotional_range'] ) ? sanitize_text_field( wp_unslash( $_POST['tone_emotional_range'] ) ) : 'balanced',
            ),

            // Language Patterns
            'language_patterns' => array(
                'sentence_structure' => $sanitize_textarea( 'lang_sentence_structure' ),
                'vocabulary'         => $sanitize_textarea( 'lang_vocabulary' ),
                'contractions'       => $sanitize_textarea( 'lang_contractions' ),
                'punctuation'        => $sanitize_textarea( 'lang_punctuation' ),
            ),

            // Additional Patterns
            'additional_patterns' => array(
                'paragraph_structure' => $sanitize_textarea( 'pattern_paragraph_structure' ),
                'opening_moves'       => $sanitize_textarea( 'pattern_opening_moves' ),
                'closing_moves'       => $sanitize_textarea( 'pattern_closing_moves' ),
                'transitions'         => $sanitize_textarea( 'pattern_transitions' ),
                'examples_evidence'   => $sanitize_textarea( 'pattern_examples_evidence' ),
                'distinctive'         => $sanitize_textarea( 'pattern_distinctive' ),
            ),

            // Philosophy sections
            'content_philosophy'    => $sanitize_textarea( 'content_philosophy' ),
            'credibility_authority' => $sanitize_textarea( 'credibility_authority' ),
            'audience_relationship' => $sanitize_textarea( 'audience_relationship' ),
            'handling_disagreement' => $sanitize_textarea( 'handling_disagreement' ),

            // Platform Adaptation
            'platform_adaptation' => array(
                'twitter'  => $sanitize_textarea( 'platform_twitter' ),
                'linkedin' => $sanitize_textarea( 'platform_linkedin' ),
                'facebook' => $sanitize_textarea( 'platform_facebook' ),
                'blog'     => $sanitize_textarea( 'platform_blog' ),
            ),

            // Guardrails (one per line)
            'guardrails' => array(
                'never_words'    => $text_to_array( 'guardrails_never_words' ),
                'never_phrases'  => $text_to_array( 'guardrails_never_phrases' ),
                'never_patterns' => $text_to_array( 'guardrails_never_patterns' ),
                'always_do'      => $text_to_array( 'guardrails_always_do' ),
            ),

            // Quick Reference (one per line)
            'quick_reference' => $text_to_array( 'quick_reference' ),
        );
        // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        update_option( 'aitwp_voice_profiles', $profiles );

        wp_send_json_success( array(
            'id'       => $id,
            'profiles' => $profiles,
        ) );
    }

    /**
     * AJAX handler to delete a voice profile.
     */
    public function ajax_delete_voice_profile() {
        check_ajax_referer( 'aitwp_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'ai-tools-for-wp' ) );
        }

        $id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';

        if ( empty( $id ) ) {
            wp_send_json_error( __( 'Invalid profile ID.', 'ai-tools-for-wp' ) );
        }

        $profiles = get_option( 'aitwp_voice_profiles', array() );

        if ( isset( $profiles[ $id ] ) ) {
            unset( $profiles[ $id ] );
            update_option( 'aitwp_voice_profiles', $profiles );
        }

        wp_send_json_success( array( 'profiles' => $profiles ) );
    }

    /**
     * AJAX handler to save an audience.
     */
    public function ajax_save_audience() {
        check_ajax_referer( 'aitwp_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'ai-tools-for-wp' ) );
        }

        // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $id   = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

        if ( empty( $name ) ) {
            wp_send_json_error( __( 'Name is required.', 'ai-tools-for-wp' ) );
        }

        $audiences = get_option( 'aitwp_audiences', array() );

        // Generate new ID if not provided
        if ( empty( $id ) ) {
            $id = 'aud_' . uniqid();
        }

        // Helper function to convert textarea lines to array
        $text_to_array = function( $key ) {
            $text = isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : '';
            return AITWP_Migration::text_to_array( $text );
        };

        $audiences[ $id ] = array(
            'id'           => $id,
            'name'         => $name,
            'updated'      => current_time( 'mysql' ),
            'version'      => AITWP_Migration::CURRENT_VERSION,
            'definition'   => isset( $_POST['definition'] ) ? sanitize_textarea_field( wp_unslash( $_POST['definition'] ) ) : '',
            'goals'        => $text_to_array( 'goals' ),
            'pains'        => $text_to_array( 'pains' ),
            'hopes_dreams' => $text_to_array( 'hopes_dreams' ),
            'fears'        => $text_to_array( 'fears' ),
        );
        // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        update_option( 'aitwp_audiences', $audiences );

        wp_send_json_success( array(
            'id'        => $id,
            'audiences' => $audiences,
        ) );
    }

    /**
     * AJAX handler to delete an audience.
     */
    public function ajax_delete_audience() {
        check_ajax_referer( 'aitwp_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'ai-tools-for-wp' ) );
        }

        $id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';

        if ( empty( $id ) ) {
            wp_send_json_error( __( 'Invalid audience ID.', 'ai-tools-for-wp' ) );
        }

        $audiences = get_option( 'aitwp_audiences', array() );

        if ( isset( $audiences[ $id ] ) ) {
            unset( $audiences[ $id ] );
            update_option( 'aitwp_audiences', $audiences );
        }

        wp_send_json_success( array( 'audiences' => $audiences ) );
    }

    /**
     * AJAX handler to get all voice profiles data.
     */
    public function ajax_get_profiles_data() {
        check_ajax_referer( 'aitwp_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'ai-tools-for-wp' ) );
        }

        $profiles = get_option( 'aitwp_voice_profiles', array() );
        wp_send_json_success( $profiles );
    }

    /**
     * AJAX handler to get all audiences data.
     */
    public function ajax_get_audiences_data() {
        check_ajax_referer( 'aitwp_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'ai-tools-for-wp' ) );
        }

        $audiences = get_option( 'aitwp_audiences', array() );
        wp_send_json_success( $audiences );
    }
}
