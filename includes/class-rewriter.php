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

        // Get rewritten content from AI
        $result = $provider->rewrite_content(
            $prepared_content,
            $voice_profile['content'],
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

        // Limit length to avoid token limits
        $max_length = 12000;
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
