<?php
/**
 * OpenAI Provider Class
 *
 * Implementation of AI provider for OpenAI API.
 *
 * @package AI_Tools_For_WP
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AITWP_OpenAI
 *
 * OpenAI API implementation.
 */
class AITWP_OpenAI extends AITWP_AI_Provider {

    /**
     * API base URL.
     *
     * @var string
     */
    private $api_base = 'https://api.openai.com/v1';

    /**
     * Constructor.
     *
     * @param string $api_key The encrypted API key.
     */
    public function __construct( $api_key = '' ) {
        parent::__construct( $api_key );

        $this->provider_id   = 'openai';
        $this->provider_name = 'OpenAI';
        $this->default_model = 'gpt-4o-mini';
    }

    /**
     * Analyze content for categorization suggestions.
     *
     * @param string $content    The post content to analyze.
     * @param array  $categories Available categories.
     * @param array  $tags       Available tags.
     * @param array  $audience   The target audience context.
     * @return array|WP_Error
     */
    public function analyze_for_categories( $content, $categories, $tags, $audience = array() ) {
        if ( ! $this->is_configured() ) {
            return new WP_Error( 'not_configured', __( 'OpenAI API key is not configured.', 'ai-tools-for-wp' ) );
        }

        $system_prompt = $this->build_categorization_prompt( $categories, $tags, $audience );

        $response = $this->make_request( '/chat/completions', array(
            'model'       => $this->default_model,
            'messages'    => array(
                array(
                    'role'    => 'system',
                    'content' => $system_prompt,
                ),
                array(
                    'role'    => 'user',
                    'content' => "Analyze this content:\n\n" . $content,
                ),
            ),
            'temperature' => 0.3,
            'max_tokens'  => 1000,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $message = $response['choices'][0]['message']['content'] ?? '';
        $parsed  = $this->parse_json_response( $message );

        if ( null === $parsed ) {
            return new WP_Error( 'parse_error', __( 'Failed to parse AI response.', 'ai-tools-for-wp' ) );
        }

        return array(
            'categories' => $parsed['categories'] ?? array(),
            'tags'       => $parsed['tags'] ?? array(),
            'new_tags'   => $parsed['new_tags'] ?? array(),
            'reasoning'  => $parsed['reasoning'] ?? '',
        );
    }

    /**
     * Suggest the best audience for content.
     *
     * @param string $content   The post content to analyze.
     * @param array  $audiences Available audiences.
     * @return array|WP_Error
     */
    public function suggest_audience( $content, $audiences ) {
        if ( ! $this->is_configured() ) {
            return new WP_Error( 'not_configured', __( 'OpenAI API key is not configured.', 'ai-tools-for-wp' ) );
        }

        if ( empty( $audiences ) ) {
            return new WP_Error( 'no_audiences', __( 'No audiences defined.', 'ai-tools-for-wp' ) );
        }

        $system_prompt = $this->build_audience_prompt( $audiences );

        $response = $this->make_request( '/chat/completions', array(
            'model'       => $this->default_model,
            'messages'    => array(
                array(
                    'role'    => 'system',
                    'content' => $system_prompt,
                ),
                array(
                    'role'    => 'user',
                    'content' => "Analyze this content:\n\n" . $content,
                ),
            ),
            'temperature' => 0.3,
            'max_tokens'  => 500,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $message = $response['choices'][0]['message']['content'] ?? '';
        $parsed  = $this->parse_json_response( $message );

        if ( null === $parsed ) {
            return new WP_Error( 'parse_error', __( 'Failed to parse AI response.', 'ai-tools-for-wp' ) );
        }

        return array(
            'audience_id' => $parsed['audience_id'] ?? '',
            'confidence'  => $parsed['confidence'] ?? 0,
            'reasoning'   => $parsed['reasoning'] ?? '',
        );
    }

    /**
     * Rewrite content using a voice profile.
     *
     * @param string $content       The content to rewrite.
     * @param string $voice_profile The voice profile markdown.
     * @param array  $audience      The target audience context.
     * @return string|WP_Error
     */
    public function rewrite_content( $content, $voice_profile, $audience = array() ) {
        if ( ! $this->is_configured() ) {
            return new WP_Error( 'not_configured', __( 'OpenAI API key is not configured.', 'ai-tools-for-wp' ) );
        }

        $system_prompt = $this->build_rewrite_prompt( $voice_profile, $audience );

        $response = $this->make_request( '/chat/completions', array(
            'model'       => $this->default_model,
            'messages'    => array(
                array(
                    'role'    => 'system',
                    'content' => $system_prompt,
                ),
                array(
                    'role'    => 'user',
                    'content' => "Rewrite this content:\n\n" . $content,
                ),
            ),
            'temperature' => 0.7,
            'max_tokens'  => 4000,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $rewritten = $response['choices'][0]['message']['content'] ?? '';

        if ( empty( $rewritten ) ) {
            return new WP_Error( 'empty_response', __( 'AI returned empty response.', 'ai-tools-for-wp' ) );
        }

        return trim( $rewritten );
    }

    /**
     * Make an API request to OpenAI.
     *
     * @param string $endpoint The API endpoint.
     * @param array  $body     The request body.
     * @param string $method   The HTTP method.
     * @return array|WP_Error
     */
    protected function make_request( $endpoint, $body, $method = 'POST' ) {
        $url = $this->api_base . $endpoint;

        $args = array(
            'method'  => $method,
            'timeout' => 60,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json',
            ),
        );

        if ( 'POST' === $method && ! empty( $body ) ) {
            $args['body'] = wp_json_encode( $body );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body        = wp_remote_retrieve_body( $response );
        $data        = json_decode( $body, true );

        if ( $status_code >= 400 ) {
            $error_message = $data['error']['message'] ?? __( 'Unknown API error.', 'ai-tools-for-wp' );
            return new WP_Error( 'api_error', $error_message, array( 'status' => $status_code ) );
        }

        return $data;
    }
}
