# [Reliability] Admin JS doesn't check jQuery availability

## Priority: Medium

## Description
The admin settings JavaScript assumes jQuery is available without verification.

## Location
`admin/js/settings.js:5`

```javascript
(function($) {
    'use strict';
    // Assumes jQuery is available
```

## Problem
- If jQuery fails to load, the entire admin interface breaks silently
- No error message for debugging
- Could cause confusing behavior

## Recommendation
Add jQuery availability check:

```javascript
(function($) {
    'use strict';

    if ( typeof $ === 'undefined' ) {
        console.error( 'AI Tools for WP: jQuery is required but not available.' );
        return;
    }

    // ... rest of code
})(jQuery);
```

## Labels
`enhancement`, `reliability`
