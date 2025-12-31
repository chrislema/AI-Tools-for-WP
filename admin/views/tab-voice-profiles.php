<?php
/**
 * Voice Profiles Tab View
 *
 * @package AI_Tools_For_WP
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$profiles = get_option( 'aitwp_voice_profiles', array() );
?>

<div class="aitwp-voice-profiles">
    <p class="description">
        <?php esc_html_e( 'Voice profiles define the writing style and tone for content rewriting. Create profiles in markdown format describing the voice characteristics.', 'ai-tools-for-wp' ); ?>
    </p>

    <div class="aitwp-profiles-list" id="aitwp-profiles-list">
        <?php if ( empty( $profiles ) ) : ?>
            <p class="aitwp-no-items"><?php esc_html_e( 'No voice profiles yet. Create your first one below.', 'ai-tools-for-wp' ); ?></p>
        <?php else : ?>
            <?php foreach ( $profiles as $profile ) : ?>
                <div class="aitwp-profile-item" data-id="<?php echo esc_attr( $profile['id'] ); ?>">
                    <div class="aitwp-profile-header">
                        <strong class="aitwp-profile-name"><?php echo esc_html( $profile['name'] ); ?></strong>
                        <div class="aitwp-profile-actions">
                            <button type="button" class="button aitwp-edit-profile"><?php esc_html_e( 'Edit', 'ai-tools-for-wp' ); ?></button>
                            <button type="button" class="button aitwp-delete-profile"><?php esc_html_e( 'Delete', 'ai-tools-for-wp' ); ?></button>
                        </div>
                    </div>
                    <div class="aitwp-profile-preview">
                        <?php echo wp_kses_post( wp_trim_words( $profile['content'], 30 ) ); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <hr>

    <h3 id="aitwp-form-title"><?php esc_html_e( 'Add New Voice Profile', 'ai-tools-for-wp' ); ?></h3>

    <form id="aitwp-profile-form" class="aitwp-form">
        <input type="hidden" id="aitwp-profile-id" name="id" value="">

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="aitwp-profile-name"><?php esc_html_e( 'Profile Name', 'ai-tools-for-wp' ); ?></label>
                </th>
                <td>
                    <input type="text" id="aitwp-profile-name" name="name" class="regular-text" required>
                    <p class="description"><?php esc_html_e( 'A descriptive name for this voice profile (e.g., "Professional Blog Voice").', 'ai-tools-for-wp' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="aitwp-profile-content"><?php esc_html_e( 'Voice Description (Markdown)', 'ai-tools-for-wp' ); ?></label>
                </th>
                <td>
                    <textarea id="aitwp-profile-content" name="content" rows="12" class="large-text code"></textarea>
                    <p class="description">
                        <?php esc_html_e( 'Describe the voice characteristics in markdown. Include tone, style, vocabulary preferences, and example phrases.', 'ai-tools-for-wp' ); ?>
                    </p>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Voice Profile', 'ai-tools-for-wp' ); ?></button>
            <button type="button" id="aitwp-cancel-profile" class="button" style="display:none;"><?php esc_html_e( 'Cancel', 'ai-tools-for-wp' ); ?></button>
        </p>
    </form>
</div>

<script type="text/html" id="tmpl-aitwp-profile-item">
    <div class="aitwp-profile-item" data-id="{{ data.id }}">
        <div class="aitwp-profile-header">
            <strong class="aitwp-profile-name">{{ data.name }}</strong>
            <div class="aitwp-profile-actions">
                <button type="button" class="button aitwp-edit-profile"><?php esc_html_e( 'Edit', 'ai-tools-for-wp' ); ?></button>
                <button type="button" class="button aitwp-delete-profile"><?php esc_html_e( 'Delete', 'ai-tools-for-wp' ); ?></button>
            </div>
        </div>
        <div class="aitwp-profile-preview">{{ data.preview }}</div>
    </div>
</script>
