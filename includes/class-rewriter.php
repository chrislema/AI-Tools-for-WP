<?php
/**
 * Rewriter Class
 *
 * Handles content rewriting using voice profiles.
 *
 * @package AI_Tools_For_WP
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AITWP_Rewriter
 *
 * Rewrites content using AI and voice profiles.
 */
class AITWP_Rewriter {

    /**
     * Rewrite content using a voice profile.
     *
     * @param string $content          The content to rewrite.
     * @param string $voice_profile_id The voice profile ID.
     * @param string $audience_id      Optional. The target audience ID.
     * @return string|WP_Error
     */
    public function rewrite( $content, $voice_profile_id, $audience_id = '' ) {
        // Get the AI provider
        $provider = AITWP_Provider_Factory::get_configured_provider();

        if ( is_wp_error( $provider ) ) {
            return $provider;
        }

        // Get voice profile
        $voice_profile = $this->get_voice_profile( $voice_profile_id );

        if ( empty( $voice_profile ) ) {
            return new WP_Error(
                'invalid_profile',
                __( 'Voice profile not found.', 'ai-tools-for-wp' )
            );
        }

        // Get audience context if specified
        $audience = array();
        if ( ! empty( $audience_id ) ) {
            $audience = $this->get_audience( $audience_id );
        }

        // Prepare content
        $prepared_content = $this->prepare_content( $content );

        // Compose voice profile into prompt text
        $voice_prompt = $this->compose_voice_profile_prompt( $voice_profile );

        // Get rewritten content from AI
        $result = $provider->rewrite_content(
            $prepared_content,
            $voice_prompt,
            $audience
        );

        return $result;
    }

    /**
     * Get a voice profile by ID.
     *
     * @param string $profile_id The profile ID.
     * @return array|null
     */
    private function get_voice_profile( $profile_id ) {
        $profiles = get_option( 'aitwp_voice_profiles', array() );

        if ( isset( $profiles[ $profile_id ] ) ) {
            return $profiles[ $profile_id ];
        }

        return null;
    }

    /**
     * Get audience by ID.
     *
     * @param string $audience_id The audience ID.
     * @return array
     */
    private function get_audience( $audience_id ) {
        $audiences = get_option( 'aitwp_audiences', array() );

        if ( isset( $audiences[ $audience_id ] ) ) {
            return $audiences[ $audience_id ];
        }

        return array();
    }

    /**
     * Compose a voice profile into a prompt string.
     *
     * Handles both old-style (single content field) and new structured profiles.
     *
     * @param array $profile The voice profile.
     * @return string The composed prompt text.
     */
    private function compose_voice_profile_prompt( $profile ) {
        // Check if this is an old-style profile with just 'content' field
        if ( ! empty( $profile['content'] ) && empty( $profile['voice_identity'] ) ) {
            return $profile['content'];
        }

        $parts = array();

        // Voice Identity
        if ( ! empty( $profile['voice_identity'] ) ) {
            $parts[] = "## Voice Identity\n" . $profile['voice_identity'];
        }

        // Tone & Energy
        if ( ! empty( $profile['tone_energy'] ) && is_array( $profile['tone_energy'] ) ) {
            $tone_parts = array();
            $energy = $profile['tone_energy'];

            if ( ! empty( $energy['energy_level'] ) ) {
                $tone_parts[] = 'Energy Level: ' . ucfirst( $energy['energy_level'] );
            }
            if ( ! empty( $energy['humor_style'] ) ) {
                $tone_parts[] = 'Humor Style: ' . ucfirst( $energy['humor_style'] );
            }
            if ( ! empty( $energy['emotional_range'] ) ) {
                $tone_parts[] = 'Emotional Range: ' . ucfirst( $energy['emotional_range'] );
            }

            if ( ! empty( $tone_parts ) ) {
                $parts[] = "## Tone & Energy\n- " . implode( "\n- ", $tone_parts );
            }
        }

        // Language Patterns
        if ( ! empty( $profile['language_patterns'] ) && is_array( $profile['language_patterns'] ) ) {
            $lang_parts = array();
            $lang = $profile['language_patterns'];

            if ( ! empty( $lang['sentence_structure'] ) ) {
                $lang_parts[] = "**Sentence Structure:** " . $lang['sentence_structure'];
            }
            if ( ! empty( $lang['vocabulary'] ) ) {
                $lang_parts[] = "**Vocabulary:** " . $lang['vocabulary'];
            }
            if ( ! empty( $lang['contractions'] ) ) {
                $lang_parts[] = "**Contractions & Formality:** " . $lang['contractions'];
            }
            if ( ! empty( $lang['punctuation'] ) ) {
                $lang_parts[] = "**Punctuation:** " . $lang['punctuation'];
            }

            if ( ! empty( $lang_parts ) ) {
                $parts[] = "## Language Patterns\n" . implode( "\n\n", $lang_parts );
            }
        }

        // Additional Patterns
        if ( ! empty( $profile['additional_patterns'] ) && is_array( $profile['additional_patterns'] ) ) {
            $add_parts = array();
            $add = $profile['additional_patterns'];

            if ( ! empty( $add['paragraph_structure'] ) ) {
                $add_parts[] = "**Paragraph Structure:** " . $add['paragraph_structure'];
            }
            if ( ! empty( $add['opening_moves'] ) ) {
                $add_parts[] = "**Opening Moves:** " . $add['opening_moves'];
            }
            if ( ! empty( $add['closing_moves'] ) ) {
                $add_parts[] = "**Closing Moves:** " . $add['closing_moves'];
            }
            if ( ! empty( $add['transitions'] ) ) {
                $add_parts[] = "**Transitions:** " . $add['transitions'];
            }
            if ( ! empty( $add['examples_evidence'] ) ) {
                $add_parts[] = "**Examples & Evidence:** " . $add['examples_evidence'];
            }
            if ( ! empty( $add['distinctive'] ) ) {
                $add_parts[] = "**Distinctive Patterns:** " . $add['distinctive'];
            }

            if ( ! empty( $add_parts ) ) {
                $parts[] = "## Additional Writing Patterns\n" . implode( "\n\n", $add_parts );
            }
        }

        // Philosophy sections
        $philosophy_parts = array();
        if ( ! empty( $profile['content_philosophy'] ) ) {
            $philosophy_parts[] = "**Content Philosophy:** " . $profile['content_philosophy'];
        }
        if ( ! empty( $profile['credibility_authority'] ) ) {
            $philosophy_parts[] = "**Credibility & Authority:** " . $profile['credibility_authority'];
        }
        if ( ! empty( $profile['audience_relationship'] ) ) {
            $philosophy_parts[] = "**Audience Relationship:** " . $profile['audience_relationship'];
        }
        if ( ! empty( $profile['handling_disagreement'] ) ) {
            $philosophy_parts[] = "**Handling Disagreement:** " . $profile['handling_disagreement'];
        }

        if ( ! empty( $philosophy_parts ) ) {
            $parts[] = "## Philosophy & Approach\n" . implode( "\n\n", $philosophy_parts );
        }

        // Platform Adaptation
        if ( ! empty( $profile['platform_adaptation'] ) && is_array( $profile['platform_adaptation'] ) ) {
            $platform_parts = array();
            $platform = $profile['platform_adaptation'];

            if ( ! empty( $platform['twitter'] ) ) {
                $platform_parts[] = "**Twitter/X:** " . $platform['twitter'];
            }
            if ( ! empty( $platform['linkedin'] ) ) {
                $platform_parts[] = "**LinkedIn:** " . $platform['linkedin'];
            }
            if ( ! empty( $platform['facebook'] ) ) {
                $platform_parts[] = "**Facebook:** " . $platform['facebook'];
            }
            if ( ! empty( $platform['blog'] ) ) {
                $platform_parts[] = "**Blog/Long-form:** " . $platform['blog'];
            }

            if ( ! empty( $platform_parts ) ) {
                $parts[] = "## Platform Adaptation\n" . implode( "\n\n", $platform_parts );
            }
        }

        // Guardrails
        if ( ! empty( $profile['guardrails'] ) && is_array( $profile['guardrails'] ) ) {
            $guardrails_parts = array();
            $guardrails = $profile['guardrails'];

            if ( ! empty( $guardrails['never_words'] ) && is_array( $guardrails['never_words'] ) ) {
                $guardrails_parts[] = "**Words to NEVER use:** " . implode( ', ', $guardrails['never_words'] );
            }
            if ( ! empty( $guardrails['never_phrases'] ) && is_array( $guardrails['never_phrases'] ) ) {
                $guardrails_parts[] = "**Phrases to NEVER use:**\n- " . implode( "\n- ", $guardrails['never_phrases'] );
            }
            if ( ! empty( $guardrails['never_patterns'] ) && is_array( $guardrails['never_patterns'] ) ) {
                $guardrails_parts[] = "**Patterns to NEVER use:**\n- " . implode( "\n- ", $guardrails['never_patterns'] );
            }
            if ( ! empty( $guardrails['always_do'] ) && is_array( $guardrails['always_do'] ) ) {
                $guardrails_parts[] = "**ALWAYS do these things:**\n- " . implode( "\n- ", $guardrails['always_do'] );
            }

            if ( ! empty( $guardrails_parts ) ) {
                $parts[] = "## Anti-AI Guardrails (CRITICAL)\n" . implode( "\n\n", $guardrails_parts );
            }
        }

        // Quick Reference
        if ( ! empty( $profile['quick_reference'] ) && is_array( $profile['quick_reference'] ) ) {
            $parts[] = "## Quick Reference\n- " . implode( "\n- ", $profile['quick_reference'] );
        }

        // Join all parts with double newlines
        return implode( "\n\n", $parts );
    }

    /**
     * Prepare content for rewriting.
     *
     * Preserves structure but cleans up for AI processing.
     *
     * @param string $content The raw content.
     * @return string
     */
    private function prepare_content( $content ) {
        // Decode HTML entities
        $content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );

        // Normalize line breaks
        $content = str_replace( array( "\r\n", "\r" ), "\n", $content );

        /**
         * Filter the maximum content length for rewriting.
         *
         * @param int $max_length Maximum characters to send to AI.
         */
        $max_length = apply_filters( 'aitwp_rewriter_max_content_length', 12000 );

        if ( strlen( $content ) > $max_length ) {
            $content = substr( $content, 0, $max_length );
            // Try to cut at a sentence boundary
            $last_period = strrpos( $content, '.' );
            if ( $last_period !== false && $last_period > $max_length - 500 ) {
                $content = substr( $content, 0, $last_period + 1 );
            }
            $content .= "\n\n[Content truncated for processing]";
        }

        return trim( $content );
    }
}
