# [Enhancement] Hardcoded content length limits

## Priority: Medium

## Description
Content length limits for AI processing are hardcoded as magic numbers with no configuration option.

## Location
- `includes/class-categorizer.php:191-194` - 8000 characters
- `includes/class-rewriter.php:115-124` - 12000 characters

```php
$max_length = 8000;  // categorizer
$max_length = 12000; // rewriter
```

## Problem
- Large posts may be truncated unexpectedly
- No way for admins to adjust based on their AI provider limits
- Magic numbers make maintenance difficult

## Recommendation
1. Add filter hooks for customization
2. Consider making it a settings option
3. Define as class constants at minimum

```php
$max_length = apply_filters( 'aitwp_categorizer_max_content_length', 8000 );
```

## Labels
`enhancement`
