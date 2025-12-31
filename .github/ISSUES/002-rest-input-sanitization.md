# [Security] Missing input sanitization on REST endpoints

## Priority: Critical

## Description
REST endpoint handlers receive user input but don't sanitize it before processing. While some sanitization happens later in the processing chain, raw unsanitized content passes through initially.

## Location
`includes/class-plugin.php:132-148`

```php
public function handle_categorize( $request ) {
    $content     = $request->get_param( 'content' );
    $audience_id = $request->get_param( 'audience_id' );

    if ( empty( $content ) ) {
        return new WP_Error(...);
    }
    // Content used without sanitization
```

## Affected Endpoints
- `/ai-tools/v1/categorize` - `content`, `audience_id`
- `/ai-tools/v1/rewrite` - `content`, `voice_profile_id`, `audience_id`
- `/ai-tools/v1/suggest-audience` - `content`

## Recommendation
Add explicit sanitization in REST handlers:
```php
$content = wp_kses_post( $request->get_param( 'content' ) );
$audience_id = sanitize_text_field( $request->get_param( 'audience_id' ) );
```

## Labels
`security`, `bug`, `critical`
