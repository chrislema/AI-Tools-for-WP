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
        <?php esc_html_e( 'Define your target audience segments. Each segment captures who they are, their goals, pain points, hopes, and fears. The AI uses this context to tailor content recommendations.', 'ai-tools-for-wp' ); ?>
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
                    <div class="aitwp-audience-preview">
                        <?php
                        // Show definition preview or fall back to old description field
                        $preview = ! empty( $audience['definition'] ) ? $audience['definition'] : ( $audience['description'] ?? '' );
                        echo esc_html( wp_trim_words( $preview, 25 ) );

                        // Show counts for goals/pains if available
                        $goals_count = ! empty( $audience['goals'] ) ? count( $audience['goals'] ) : 0;
                        $pains_count = ! empty( $audience['pains'] ) ? count( $audience['pains'] ) : 0;
                        if ( $goals_count > 0 || $pains_count > 0 ) {
                            echo '<span class="aitwp-audience-meta">';
                            if ( $goals_count > 0 ) {
                                /* translators: %d: number of goals */
                                echo ' &bull; ' . esc_html( sprintf( _n( '%d goal', '%d goals', $goals_count, 'ai-tools-for-wp' ), $goals_count ) );
                            }
                            if ( $pains_count > 0 ) {
                                /* translators: %d: number of pain points */
                                echo ' &bull; ' . esc_html( sprintf( _n( '%d pain', '%d pains', $pains_count, 'ai-tools-for-wp' ), $pains_count ) );
                            }
                            echo '</span>';
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <hr>

    <h3 id="aitwp-audience-form-title"><?php esc_html_e( 'Add New Audience', 'ai-tools-for-wp' ); ?></h3>

    <form id="aitwp-audience-form" class="aitwp-form">
        <input type="hidden" id="aitwp-audience-id" name="id" value="">

        <!-- Basic Info (always visible) -->
        <div class="aitwp-section aitwp-section-open">
            <div class="aitwp-section-header">
                <h4><?php esc_html_e( 'Basic Info', 'ai-tools-for-wp' ); ?></h4>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-audience-name"><?php esc_html_e( 'Segment Name', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="aitwp-audience-name" name="name" class="regular-text" required>
                            <p class="description"><?php esc_html_e( 'A descriptive name (e.g., "Technical Founders", "WordPress Freelancers").', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Definition -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Definition', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-audience-definition"><?php esc_html_e( 'Who They Are', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-audience-definition" name="definition" rows="5" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'Describe this audience segment: their role, characteristics, experience level, and context.', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Goals -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Goals', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-audience-goals"><?php esc_html_e( 'What They Want', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-audience-goals" name="goals" rows="8" class="large-text" placeholder="Get first paying customers&#10;Learn to market without feeling inauthentic&#10;Build sustainable revenue&#10;Transition from developer to founder"></textarea>
                            <p class="description"><?php esc_html_e( 'One goal per line. What does this audience want to achieve?', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Pains -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Pains', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-audience-pains"><?php esc_html_e( 'Pain Points', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-audience-pains" name="pains" rows="8" class="large-text" placeholder="Built a great product but can't get noticed&#10;Marketing feels foreign and uncomfortable&#10;Hate selling, don't know how&#10;Burning through savings without clear path"></textarea>
                            <p class="description"><?php esc_html_e( 'One pain point per line. What frustrates or challenges them?', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Hopes & Dreams -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Hopes & Dreams', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-audience-hopes-dreams"><?php esc_html_e( 'Aspirations', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-audience-hopes-dreams" name="hopes_dreams" rows="8" class="large-text" placeholder="Build a profitable product company&#10;Escape the employment treadmill&#10;Be recognized as a successful founder&#10;Create generational wealth"></textarea>
                            <p class="description"><?php esc_html_e( 'One hope or dream per line. What do they aspire to become or achieve?', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Fears -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Fears', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-audience-fears"><?php esc_html_e( 'Worries & Fears', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-audience-fears" name="fears" rows="8" class="large-text" placeholder="Building something nobody wants&#10;Running out of money before figuring it out&#10;Having to give up and go back to a job&#10;Being seen as a failure"></textarea>
                            <p class="description"><?php esc_html_e( 'One fear per line. What keeps them up at night?', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

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
        <div class="aitwp-audience-preview">{{ data.preview }}</div>
    </div>
</script>
