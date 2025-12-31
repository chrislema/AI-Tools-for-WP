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

// Tone options for dropdowns
$energy_levels = array(
    'low'      => __( 'Low - Calm and measured', 'ai-tools-for-wp' ),
    'medium'   => __( 'Medium - Balanced energy', 'ai-tools-for-wp' ),
    'high'     => __( 'High - Enthusiastic and dynamic', 'ai-tools-for-wp' ),
    'variable' => __( 'Variable - Adapts to context', 'ai-tools-for-wp' ),
);

$humor_styles = array(
    'none'     => __( 'None - Serious tone', 'ai-tools-for-wp' ),
    'subtle'   => __( 'Subtle - Light touches', 'ai-tools-for-wp' ),
    'moderate' => __( 'Moderate - Regular humor', 'ai-tools-for-wp' ),
    'frequent' => __( 'Frequent - Playful throughout', 'ai-tools-for-wp' ),
);

$emotional_ranges = array(
    'reserved'   => __( 'Reserved - Professional distance', 'ai-tools-for-wp' ),
    'balanced'   => __( 'Balanced - Warm but measured', 'ai-tools-for-wp' ),
    'expressive' => __( 'Expressive - Open and emotive', 'ai-tools-for-wp' ),
);
?>

<div class="aitwp-voice-profiles">
    <p class="description">
        <?php esc_html_e( 'Voice profiles define the writing style and tone for content rewriting. Each section captures different aspects of your voice.', 'ai-tools-for-wp' ); ?>
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
                        <?php
                        // Show voice identity preview or fall back to old content field
                        $preview = ! empty( $profile['voice_identity'] ) ? $profile['voice_identity'] : ( $profile['content'] ?? '' );
                        echo esc_html( wp_trim_words( $preview, 30 ) );
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <hr>

    <h3 id="aitwp-form-title"><?php esc_html_e( 'Add New Voice Profile', 'ai-tools-for-wp' ); ?></h3>

    <form id="aitwp-profile-form" class="aitwp-form">
        <input type="hidden" id="aitwp-profile-id" name="id" value="">

        <!-- Basic Info (always visible) -->
        <div class="aitwp-section aitwp-section-open">
            <div class="aitwp-section-header">
                <h4><?php esc_html_e( 'Basic Info', 'ai-tools-for-wp' ); ?></h4>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-profile-name"><?php esc_html_e( 'Profile Name', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="aitwp-profile-name" name="name" class="regular-text" required>
                            <p class="description"><?php esc_html_e( 'A descriptive name for this voice profile (e.g., "Chris Lema Voice").', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Voice Identity -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Voice Identity', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-voice-identity"><?php esc_html_e( 'Core Identity', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-voice-identity" name="voice_identity" rows="6" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'Describe the overall voice identity and communication style. Who is this voice? What role do they play?', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Tone & Energy -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Tone & Energy', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-tone-energy-level"><?php esc_html_e( 'Energy Level', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <select id="aitwp-tone-energy-level" name="tone_energy_level">
                                <?php foreach ( $energy_levels as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-tone-humor-style"><?php esc_html_e( 'Humor Style', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <select id="aitwp-tone-humor-style" name="tone_humor_style">
                                <?php foreach ( $humor_styles as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-tone-emotional-range"><?php esc_html_e( 'Emotional Range', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <select id="aitwp-tone-emotional-range" name="tone_emotional_range">
                                <?php foreach ( $emotional_ranges as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Language Patterns -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Language Patterns', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-lang-sentence-structure"><?php esc_html_e( 'Sentence Structure', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-lang-sentence-structure" name="lang_sentence_structure" rows="3" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'Describe preferred sentence lengths, structures, and rhythms.', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-lang-vocabulary"><?php esc_html_e( 'Vocabulary', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-lang-vocabulary" name="lang_vocabulary" rows="3" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'Describe vocabulary tendencies, jargon usage, and word choices.', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-lang-contractions"><?php esc_html_e( 'Contractions & Formality', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-lang-contractions" name="lang_contractions" rows="2" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'How formal or casual? Use contractions?', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-lang-punctuation"><?php esc_html_e( 'Punctuation', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-lang-punctuation" name="lang_punctuation" rows="2" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'Em dashes, ellipses, exclamation points, etc.', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Additional Patterns -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Additional Patterns', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-pattern-paragraph-structure"><?php esc_html_e( 'Paragraph Structure', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-pattern-paragraph-structure" name="pattern_paragraph_structure" rows="3" class="large-text"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-pattern-opening-moves"><?php esc_html_e( 'Opening Moves', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-pattern-opening-moves" name="pattern_opening_moves" rows="3" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'How do you start content? Hooks, questions, bold statements?', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-pattern-closing-moves"><?php esc_html_e( 'Closing Moves', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-pattern-closing-moves" name="pattern_closing_moves" rows="3" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'How do you end content? CTAs, questions, forward momentum?', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-pattern-transitions"><?php esc_html_e( 'Transitions', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-pattern-transitions" name="pattern_transitions" rows="3" class="large-text"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-pattern-examples-evidence"><?php esc_html_e( 'Examples & Evidence', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-pattern-examples-evidence" name="pattern_examples_evidence" rows="3" class="large-text"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-pattern-distinctive"><?php esc_html_e( 'Distinctive Patterns', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-pattern-distinctive" name="pattern_distinctive" rows="3" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'Signature phrases, metaphors, frameworks, or rhetorical devices.', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Philosophy & Approach -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Philosophy & Approach', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-content-philosophy"><?php esc_html_e( 'Content Philosophy', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-content-philosophy" name="content_philosophy" rows="4" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'Your approach to content creation, perspective on topics.', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-credibility-authority"><?php esc_html_e( 'Credibility & Authority', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-credibility-authority" name="credibility_authority" rows="4" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'How do you establish credibility? Experience, stories, data?', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-audience-relationship"><?php esc_html_e( 'Audience Relationship', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-audience-relationship" name="audience_relationship" rows="4" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'How do you relate to your audience? Mentor, peer, expert?', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-handling-disagreement"><?php esc_html_e( 'Handling Disagreement', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-handling-disagreement" name="handling_disagreement" rows="4" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'How do you address opposing viewpoints or controversy?', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Platform Adaptation -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Platform Adaptation', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-platform-twitter"><?php esc_html_e( 'Twitter/X', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-platform-twitter" name="platform_twitter" rows="4" class="large-text"></textarea>
                            <p class="description"><?php esc_html_e( 'How to adapt your voice for Twitter/X posts and threads.', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-platform-linkedin"><?php esc_html_e( 'LinkedIn', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-platform-linkedin" name="platform_linkedin" rows="4" class="large-text"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-platform-facebook"><?php esc_html_e( 'Facebook', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-platform-facebook" name="platform_facebook" rows="4" class="large-text"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-platform-blog"><?php esc_html_e( 'Blog/Long-form', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-platform-blog" name="platform_blog" rows="4" class="large-text"></textarea>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Anti-AI Guardrails -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Anti-AI Guardrails', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <p class="description" style="margin-bottom: 15px;">
                    <?php esc_html_e( 'Define words, phrases, and patterns that AI should avoid to maintain authenticity.', 'ai-tools-for-wp' ); ?>
                </p>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-guardrails-never-words"><?php esc_html_e( 'Never Use Words', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-guardrails-never-words" name="guardrails_never_words" rows="5" class="large-text" placeholder="delve&#10;leverage&#10;synergy&#10;robust"></textarea>
                            <p class="description"><?php esc_html_e( 'One word per line.', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-guardrails-never-phrases"><?php esc_html_e( 'Never Use Phrases', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-guardrails-never-phrases" name="guardrails_never_phrases" rows="5" class="large-text" placeholder="In today's fast-paced world&#10;At its core&#10;Let's dive in"></textarea>
                            <p class="description"><?php esc_html_e( 'One phrase per line.', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-guardrails-never-patterns"><?php esc_html_e( 'Never Use Patterns', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-guardrails-never-patterns" name="guardrails_never_patterns" rows="5" class="large-text" placeholder="Uniform sentence lengths&#10;Every paragraph starting the same way&#10;Excessive hedging"></textarea>
                            <p class="description"><?php esc_html_e( 'One pattern per line.', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="aitwp-guardrails-always-do"><?php esc_html_e( 'Always Do', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-guardrails-always-do" name="guardrails_always_do" rows="5" class="large-text" placeholder="Use contractions naturally&#10;Vary sentence length&#10;Take clear stances"></textarea>
                            <p class="description"><?php esc_html_e( 'One guideline per line.', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Quick Reference -->
        <div class="aitwp-section">
            <div class="aitwp-section-header aitwp-collapsible">
                <h4><?php esc_html_e( 'Quick Reference', 'ai-tools-for-wp' ); ?></h4>
                <span class="aitwp-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="aitwp-section-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aitwp-quick-reference"><?php esc_html_e( 'Summary Points', 'ai-tools-for-wp' ); ?></label>
                        </th>
                        <td>
                            <textarea id="aitwp-quick-reference" name="quick_reference" rows="6" class="large-text" placeholder="Sound like: Experienced mentor&#10;Perspective: Challenge conventional wisdom&#10;Energy: Passionate but grounded"></textarea>
                            <p class="description"><?php esc_html_e( 'Key summary points for quick reference. One per line.', 'ai-tools-for-wp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

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
