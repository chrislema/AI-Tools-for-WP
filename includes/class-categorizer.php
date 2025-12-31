<?php
/**
 * Categorizer Class
 *
 * Handles content analysis for category and tag suggestions.
 *
 * @package AI_Tools_For_WP
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AITWP_Categorizer
 *
 * Analyzes content and suggests categories/tags using AI.
 */
class AITWP_Categorizer {

    /**
     * Analyze content and suggest categories/tags.
     *
     * @param string $content     The post content to analyze.
     * @param string $audience_id Optional. The target audience ID.
     * @return array|WP_Error
     */
    public function analyze( $content, $audience_id = '' ) {
        // Get the AI provider
        $provider = AITWP_Provider_Factory::get_configured_provider();

        if ( is_wp_error( $provider ) ) {
            return $provider;
        }

        // Get available categories
        $categories = $this->get_categories();

        // Get available tags
        $tags = $this->get_tags();

        // Get audience context if specified
        $audience = array();
        if ( ! empty( $audience_id ) ) {
            $audience = $this->get_audience( $audience_id );
        }

        // Strip HTML and prepare content
        $clean_content = $this->prepare_content( $content );

        // Get AI suggestions
        $result = $provider->analyze_for_categories( $clean_content, $categories, $tags, $audience );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Enrich the results with full category/tag data
        return $this->enrich_results( $result, $categories, $tags );
    }

    /**
     * Suggest the best audience for content.
     *
     * @param string $content The post content to analyze.
     * @return array|WP_Error
     */
    public function suggest_audience( $content ) {
        // Get the AI provider
        $provider = AITWP_Provider_Factory::get_configured_provider();

        if ( is_wp_error( $provider ) ) {
            return $provider;
        }

        // Get available audiences
        $audiences = get_option( 'aitwp_audiences', array() );

        if ( empty( $audiences ) ) {
            return new WP_Error( 'no_audiences', __( 'No audiences have been defined.', 'ai-tools-for-wp' ) );
        }

        // Prepare content
        $clean_content = $this->prepare_content( $content );

        // Get AI suggestion
        $result = $provider->suggest_audience( $clean_content, $audiences );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Add audience details to response
        $audience_id = $result['audience_id'] ?? '';
        if ( ! empty( $audience_id ) && isset( $audiences[ $audience_id ] ) ) {
            $result['audience'] = $audiences[ $audience_id ];
        }

        return $result;
    }

    /**
     * Get all categories formatted for AI.
     *
     * @return array
     */
    private function get_categories() {
        $categories = get_categories( array(
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );

        $formatted = array();
        foreach ( $categories as $category ) {
            $formatted[] = array(
                'id'   => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
            );
        }

        return $formatted;
    }

    /**
     * Get all tags formatted for AI.
     *
     * @return array
     */
    private function get_tags() {
        $tags = get_tags( array(
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );

        if ( is_wp_error( $tags ) || empty( $tags ) ) {
            return array();
        }

        $formatted = array();
        foreach ( $tags as $tag ) {
            $formatted[] = array(
                'id'   => $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            );
        }

        return $formatted;
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
     * Prepare content for AI analysis.
     *
     * @param string $content The raw content.
     * @return string
     */
    private function prepare_content( $content ) {
        // Strip HTML tags
        $content = wp_strip_all_tags( $content );

        // Decode HTML entities
        $content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );

        // Normalize whitespace
        $content = preg_replace( '/\s+/', ' ', $content );

        // Trim
        $content = trim( $content );

        /**
         * Filter the maximum content length for categorization.
         *
         * @param int $max_length Maximum characters to send to AI.
         */
        $max_length = apply_filters( 'aitwp_categorizer_max_content_length', 8000 );

        if ( strlen( $content ) > $max_length ) {
            $content = substr( $content, 0, $max_length ) . '...';
        }

        return $content;
    }

    /**
     * Enrich AI results with full term data.
     *
     * @param array $result     The AI result.
     * @param array $categories Available categories.
     * @param array $tags       Available tags.
     * @return array
     */
    private function enrich_results( $result, $categories, $tags ) {
        // Create lookup maps
        $cat_map = array();
        foreach ( $categories as $cat ) {
            $cat_map[ $cat['id'] ] = $cat;
        }

        $tag_map = array();
        foreach ( $tags as $tag ) {
            $tag_map[ $tag['id'] ] = $tag;
        }

        // Enrich categories
        $enriched_categories = array();
        foreach ( $result['categories'] as $cat_id ) {
            if ( isset( $cat_map[ $cat_id ] ) ) {
                $enriched_categories[] = $cat_map[ $cat_id ];
            }
        }

        // Enrich tags
        $enriched_tags = array();
        foreach ( $result['tags'] as $tag_id ) {
            if ( isset( $tag_map[ $tag_id ] ) ) {
                $enriched_tags[] = $tag_map[ $tag_id ];
            }
        }

        return array(
            'categories' => $enriched_categories,
            'tags'       => $enriched_tags,
            'new_tags'   => $result['new_tags'] ?? array(),
            'reasoning'  => $result['reasoning'] ?? '',
        );
    }
}
