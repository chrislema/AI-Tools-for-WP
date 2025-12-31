# [Enhancement] No server-side error logging for API failures

## Priority: Low

## Description
Failed AI API calls aren't logged server-side, making production debugging difficult.

## Location
- `includes/class-openai.php:197-228`
- `includes/class-anthropic.php:192-225`

## Problem
- No visibility into API failures
- Difficult to diagnose issues in production
- No metrics on error rates

## Recommendation
Add error logging using WordPress debug log:

```php
if ( $status_code >= 400 ) {
    $error_message = $data['error']['message'] ?? __( 'Unknown API error.', 'ai-tools-for-wp' );

    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( sprintf(
            'AI Tools for WP: %s API error (%d): %s',
            $this->provider_name,
            $status_code,
            $error_message
        ) );
    }

    return new WP_Error( 'api_error', $error_message, array( 'status' => $status_code ) );
}
```

## Labels
`enhancement`, `debugging`
