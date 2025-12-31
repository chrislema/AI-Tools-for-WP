<?php
/**
 * API Keys Tab View
 *
 * @package AI_Tools_For_WP
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Double-check admin permissions
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'You do not have permission to access this page.', 'ai-tools-for-wp' ) );
}

$openai_key      = get_option( 'aitwp_openai_key', '' );
$anthropic_key   = get_option( 'aitwp_anthropic_key', '' );
$default_provider = get_option( 'aitwp_default_provider', 'openai' );
$auto_apply_tags  = get_option( 'aitwp_auto_apply_tags', false );

// Decrypt keys for display (masked)
$openai_display    = '';
$anthropic_display = '';

if ( ! empty( $openai_key ) ) {
    $decrypted = AITWP_Encryption::decrypt( $openai_key );
    $openai_display = $decrypted ? AITWP_Encryption::mask( $decrypted ) : '';
}

if ( ! empty( $anthropic_key ) ) {
    $decrypted = AITWP_Encryption::decrypt( $anthropic_key );
    $anthropic_display = $decrypted ? AITWP_Encryption::mask( $decrypted ) : '';
}
?>

<div class="aitwp-api-keys">
    <div class="notice notice-info inline">
        <p>
            <?php esc_html_e( 'API keys are encrypted before being stored in the database. They are never exposed to the browser or included in API responses.', 'ai-tools-for-wp' ); ?>
        </p>
    </div>

    <form method="post" action="options.php">
        <?php settings_fields( 'aitwp_api_keys' ); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="aitwp_default_provider"><?php esc_html_e( 'Default AI Provider', 'ai-tools-for-wp' ); ?></label>
                </th>
                <td>
                    <select id="aitwp_default_provider" name="aitwp_default_provider">
                        <option value="openai" <?php selected( $default_provider, 'openai' ); ?>>
                            <?php esc_html_e( 'OpenAI (GPT)', 'ai-tools-for-wp' ); ?>
                        </option>
                        <option value="anthropic" <?php selected( $default_provider, 'anthropic' ); ?>>
                            <?php esc_html_e( 'Anthropic (Claude)', 'ai-tools-for-wp' ); ?>
                        </option>
                    </select>
                    <p class="description"><?php esc_html_e( 'Select which AI provider to use by default.', 'ai-tools-for-wp' ); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="aitwp_openai_key"><?php esc_html_e( 'OpenAI API Key', 'ai-tools-for-wp' ); ?></label>
                </th>
                <td>
                    <?php if ( ! empty( $openai_display ) ) : ?>
                        <p class="aitwp-current-key">
                            <?php esc_html_e( 'Current key:', 'ai-tools-for-wp' ); ?>
                            <code><?php echo esc_html( $openai_display ); ?></code>
                        </p>
                    <?php endif; ?>
                    <input type="password" id="aitwp_openai_key" name="aitwp_openai_key" class="regular-text" autocomplete="new-password">
                    <p class="description">
                        <?php
                        printf(
                            /* translators: %s: OpenAI API keys URL */
                            esc_html__( 'Enter your OpenAI API key. Get one at %s', 'ai-tools-for-wp' ),
                            '<a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener">platform.openai.com/api-keys</a>'
                        );
                        ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="aitwp_anthropic_key"><?php esc_html_e( 'Anthropic API Key', 'ai-tools-for-wp' ); ?></label>
                </th>
                <td>
                    <?php if ( ! empty( $anthropic_display ) ) : ?>
                        <p class="aitwp-current-key">
                            <?php esc_html_e( 'Current key:', 'ai-tools-for-wp' ); ?>
                            <code><?php echo esc_html( $anthropic_display ); ?></code>
                        </p>
                    <?php endif; ?>
                    <input type="password" id="aitwp_anthropic_key" name="aitwp_anthropic_key" class="regular-text" autocomplete="new-password">
                    <p class="description">
                        <?php
                        printf(
                            /* translators: %s: Anthropic API keys URL */
                            esc_html__( 'Enter your Anthropic API key. Get one at %s', 'ai-tools-for-wp' ),
                            '<a href="https://console.anthropic.com/settings/keys" target="_blank" rel="noopener">console.anthropic.com/settings/keys</a>'
                        );
                        ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php esc_html_e( 'Auto-Apply Settings', 'ai-tools-for-wp' ); ?></th>
                <td>
                    <label for="aitwp_auto_apply_tags">
                        <input type="checkbox" id="aitwp_auto_apply_tags" name="aitwp_auto_apply_tags" value="1" <?php checked( $auto_apply_tags, true ); ?>>
                        <?php esc_html_e( 'Auto-apply category and tag suggestions by default', 'ai-tools-for-wp' ); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e( 'When enabled, AI-suggested categories and tags will be automatically applied. Users can override this per-post in the editor.', 'ai-tools-for-wp' ); ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php submit_button( __( 'Save API Settings', 'ai-tools-for-wp' ) ); ?>
    </form>
</div>
