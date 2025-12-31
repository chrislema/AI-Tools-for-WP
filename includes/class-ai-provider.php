<?php
/**
 * Abstract AI Provider Class
 *
 * Base class for all AI provider implementations.
 *
 * @package AI_Tools_For_WP
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AITWP_AI_Provider
 *
 * Abstract base class defining the interface for AI providers.
 */
abstract class AITWP_AI_Provider {

    /**
     * The API key for this provider.
     *
     * @var string
     */
    protected $api_key = '';

    /**
     * Provider identifier.
     *
     * @var string
     */
    protected $provider_id = '';

    /**
     * Provider display name.
     *
     * @var string
     */
    protected $provider_name = '';

    /**
     * Default model to use.
     *
     * @var string
     */
    protected $default_model = '';

    /**
     * Constructor.
     *
     * @param string $api_key The API key (encrypted).
     */
    public function __construct( $api_key = '' ) {
        if ( ! empty( $api_key ) ) {
            $this->api_key = AITWP_Encryption::decrypt( $api_key );
        }
    }

    /**
     * Get the provider identifier.
     *
     * @return string
     */
    public function get_id() {
        return $this->provider_id;
    }

    /**
     * Get the provider display name.
     *
     * @return string
     */
    public function get_name() {
        return $this->provider_name;
    }

    /**
     * Check if the provider is configured with an API key.
     *
     * @return bool
     */
    public function is_configured() {
        return ! empty( $this->api_key );
    }

    /**
     * Analyze content for categorization suggestions.
     *
     * @param string $content     The post content to analyze.
     * @param array  $categories  Available categories.
     * @param array  $tags        Available tags.
     * @param array  $audience    The target audience context.
     * @return array|WP_Error Array of suggestions or error.
     */
    abstract public function analyze_for_categories( $content, $categories, $tags, $audience = array() );

    /**
     * Suggest the best audience for content.
     *
     * @param string $content   The post content to analyze.
     * @param array  $audiences Available audiences.
     * @return array|WP_Error Suggested audience or error.
     */
    abstract public function suggest_audience( $content, $audiences );

    /**
     * Rewrite content using a voice profile.
     *
     * @param string $content       The content to rewrite.
     * @param string $voice_profile The voice profile (markdown).
     * @param array  $audience      The target audience context.
     * @return string|WP_Error Rewritten content or error.
     */
    abstract public function rewrite_content( $content, $voice_profile, $audience = array() );

    /**
     * Make an API request.
     *
     * @param string $endpoint The API endpoint.
     * @param array  $body     The request body.
     * @param string $method   The HTTP method.
     * @return array|WP_Error Response data or error.
     */
    abstract protected function make_request( $endpoint, $body, $method = 'POST' );

    /**
     * Build the system prompt for categorization.
     *
     * @param array $categories Available categories.
     * @param array $tags       Available tags.
     * @param array $audience   Target audience context.
     * @return string
     */
    protected function build_categorization_prompt( $categories, $tags, $audience = array() ) {
        $prompt = "You are a content categorization assistant for a WordPress blog. ";
        $prompt .= "Analyze the given content and suggest appropriate categories and tags.\n\n";

        if ( ! empty( $audience ) ) {
            $prompt .= "Target Audience: " . $audience['name'] . "\n";
            if ( ! empty( $audience['description'] ) ) {
                $prompt .= "Audience Description: " . $audience['description'] . "\n";
            }
            $prompt .= "\nConsider this audience when making suggestions.\n\n";
        }

        $prompt .= "Available Categories:\n";
        foreach ( $categories as $cat ) {
            $prompt .= "- " . $cat['name'] . " (ID: " . $cat['id'] . ")\n";
        }

        $prompt .= "\nAvailable Tags:\n";
        foreach ( $tags as $tag ) {
            $prompt .= "- " . $tag['name'] . " (ID: " . $tag['id'] . ")\n";
        }

        $prompt .= "\nRespond with a JSON object containing:\n";
        $prompt .= "- categories: array of category IDs that best match the content\n";
        $prompt .= "- tags: array of tag IDs that best match the content\n";
        $prompt .= "- new_tags: array of suggested new tag names if existing tags don't cover the topic\n";
        $prompt .= "- reasoning: brief explanation of your choices\n";
        $prompt .= "\nOnly suggest categories and tags that are truly relevant. Quality over quantity.";

        return $prompt;
    }

    /**
     * Build the system prompt for audience suggestion.
     *
     * @param array $audiences Available audiences.
     * @return string
     */
    protected function build_audience_prompt( $audiences ) {
        $prompt = "You are a content analyst. Analyze the given content and determine which target audience it's best suited for.\n\n";

        $prompt .= "Available Audiences:\n";
        foreach ( $audiences as $aud ) {
            $prompt .= "- " . $aud['name'] . " (ID: " . $aud['id'] . ")";
            if ( ! empty( $aud['description'] ) ) {
                $prompt .= ": " . $aud['description'];
            }
            $prompt .= "\n";
        }

        $prompt .= "\nRespond with a JSON object containing:\n";
        $prompt .= "- audience_id: the ID of the best matching audience\n";
        $prompt .= "- confidence: a score from 0-100 indicating how well the content matches\n";
        $prompt .= "- reasoning: brief explanation of why this audience is the best match";

        return $prompt;
    }

    /**
     * Build the system prompt for content rewriting.
     *
     * @param string $voice_profile The voice profile markdown.
     * @param array  $audience      Target audience context.
     * @return string
     */
    protected function build_rewrite_prompt( $voice_profile, $audience = array() ) {
        $prompt = "You are a content rewriter. Rewrite the given content using the specified voice profile while maintaining the core message and information.\n\n";

        $prompt .= "Voice Profile:\n";
        $prompt .= $voice_profile . "\n\n";

        if ( ! empty( $audience ) ) {
            $prompt .= "Target Audience: " . $audience['name'] . "\n";
            if ( ! empty( $audience['description'] ) ) {
                $prompt .= "Audience Description: " . $audience['description'] . "\n";
            }
            $prompt .= "\nTailor the rewritten content for this specific audience.\n\n";
        }

        $prompt .= "Guidelines:\n";
        $prompt .= "- Maintain all factual information and key points\n";
        $prompt .= "- Apply the voice profile's tone, style, and vocabulary\n";
        $prompt .= "- Keep approximately the same length as the original\n";
        $prompt .= "- Preserve any formatting (headings, lists, etc.)\n";
        $prompt .= "- Return only the rewritten content, no explanations";

        return $prompt;
    }

    /**
     * Parse a JSON response from the AI, handling markdown code blocks.
     *
     * @param string $response The raw response text.
     * @return array|null Parsed JSON or null on failure.
     */
    protected function parse_json_response( $response ) {
        // Remove markdown code blocks if present
        $response = preg_replace( '/^```(?:json)?\s*/i', '', $response );
        $response = preg_replace( '/\s*```$/', '', $response );
        $response = trim( $response );

        $decoded = json_decode( $response, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return null;
        }

        return $decoded;
    }
}
