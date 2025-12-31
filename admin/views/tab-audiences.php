<?php
/**
 * Audiences Tab View
 *
 * @package AI_Tools_For_WP
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$audiences = get_option( 'aitwp_audiences', array() );
?>

<div class="aitwp-audiences">
    <p class="description">
        <?php esc_html_e( 'Define your target audiences. The AI will consider these when analyzing content and making suggestions.', 'ai-tools-for-wp' ); ?>
    </p>

    <div class="aitwp-audiences-list" id="aitwp-audiences-list">
        <?php if ( empty( $audiences ) ) : ?>
            <p class="aitwp-no-items"><?php esc_html_e( 'No audiences defined yet. Create your first one below.', 'ai-tools-for-wp' ); ?></p>
        <?php else : ?>
            <?php foreach ( $audiences as $audience ) : ?>
                <div class="aitwp-audience-item" data-id="<?php echo esc_attr( $audience['id'] ); ?>">
                    <div class="aitwp-audience-header">
                        <strong class="aitwp-audience-name"><?php echo esc_html( $audience['name'] ); ?></strong>
                        <div class="aitwp-audience-actions">
                            <button type="button" class="button aitwp-edit-audience"><?php esc_html_e( 'Edit', 'ai-tools-for-wp' ); ?></button>
                            <button type="button" class="button aitwp-delete-audience"><?php esc_html_e( 'Delete', 'ai-tools-for-wp' ); ?></button>
                        </div>
                    </div>
                    <div class="aitwp-audience-description">
                        <?php echo esc_html( $audience['description'] ); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <hr>

    <h3 id="aitwp-audience-form-title"><?php esc_html_e( 'Add New Audience', 'ai-tools-for-wp' ); ?></h3>

    <form id="aitwp-audience-form" class="aitwp-form">
        <input type="hidden" id="aitwp-audience-id" name="id" value="">

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="aitwp-audience-name"><?php esc_html_e( 'Audience Name', 'ai-tools-for-wp' ); ?></label>
                </th>
                <td>
                    <input type="text" id="aitwp-audience-name" name="name" class="regular-text" required>
                    <p class="description"><?php esc_html_e( 'A short name for this audience (e.g., "Marketing Managers", "Small Business Owners").', 'ai-tools-for-wp' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="aitwp-audience-description"><?php esc_html_e( 'Description', 'ai-tools-for-wp' ); ?></label>
                </th>
                <td>
                    <textarea id="aitwp-audience-description" name="description" rows="5" class="large-text"></textarea>
                    <p class="description">
                        <?php esc_html_e( 'Describe this audience: their role, challenges, interests, and what they care about. This helps the AI tailor suggestions.', 'ai-tools-for-wp' ); ?>
                    </p>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Audience', 'ai-tools-for-wp' ); ?></button>
            <button type="button" id="aitwp-cancel-audience" class="button" style="display:none;"><?php esc_html_e( 'Cancel', 'ai-tools-for-wp' ); ?></button>
        </p>
    </form>
</div>

<script type="text/html" id="tmpl-aitwp-audience-item">
    <div class="aitwp-audience-item" data-id="{{ data.id }}">
        <div class="aitwp-audience-header">
            <strong class="aitwp-audience-name">{{ data.name }}</strong>
            <div class="aitwp-audience-actions">
                <button type="button" class="button aitwp-edit-audience"><?php esc_html_e( 'Edit', 'ai-tools-for-wp' ); ?></button>
                <button type="button" class="button aitwp-delete-audience"><?php esc_html_e( 'Delete', 'ai-tools-for-wp' ); ?></button>
            </div>
        </div>
        <div class="aitwp-audience-description">{{ data.description }}</div>
    </div>
</script>
