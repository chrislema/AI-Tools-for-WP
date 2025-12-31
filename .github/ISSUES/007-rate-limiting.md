# [Enhancement] No rate limiting for AI API calls

## Priority: Medium

## Description
There's no protection against excessive AI API calls. Users could accidentally trigger many expensive requests by rapidly clicking buttons.

## Location
All REST endpoints in `includes/class-plugin.php`

## Problem
- Accidental rapid clicks can trigger multiple API calls
- No protection against runaway costs
- Could hit API rate limits and cause failures
- No visibility into usage

## Recommendation
1. Add client-side debouncing on buttons
2. Implement server-side rate limiting per user
3. Consider adding usage tracking/limits in settings

```php
// Server-side rate limiting
$transient_key = 'aitwp_rate_limit_' . get_current_user_id();
$requests = get_transient( $transient_key ) ?: 0;

if ( $requests >= 10 ) { // 10 requests per minute
    return new WP_Error( 'rate_limited', 'Too many requests. Please wait.' );
}

set_transient( $transient_key, $requests + 1, MINUTE_IN_SECONDS );
```

## Labels
`enhancement`, `cost-control`
