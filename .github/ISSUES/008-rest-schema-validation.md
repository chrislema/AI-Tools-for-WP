# [Code Quality] Missing REST API schema validation

## Priority: Medium

## Description
REST API endpoints don't define argument schemas, missing out on WordPress's built-in validation.

## Location
`includes/class-plugin.php:82-115`

```php
register_rest_route( $namespace, '/categorize', array(
    'methods'             => 'POST',
    'callback'            => array( $this, 'handle_categorize' ),
    'permission_callback' => array( $this, 'check_edit_permission' ),
    // No 'args' schema defined!
) );
```

## Problem
- No automatic type validation
- No automatic sanitization callbacks
- Missing from REST API documentation
- Inconsistent error messages

## Recommendation
Add `args` parameter with full schema:

```php
register_rest_route( $namespace, '/categorize', array(
    'methods'             => 'POST',
    'callback'            => array( $this, 'handle_categorize' ),
    'permission_callback' => array( $this, 'check_edit_permission' ),
    'args'                => array(
        'content' => array(
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'validate_callback' => function( $value ) {
                return ! empty( trim( $value ) );
            },
        ),
        'audience_id' => array(
            'required'          => false,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ),
    ),
) );
```

## Labels
`enhancement`, `code-quality`
