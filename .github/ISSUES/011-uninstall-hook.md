# [Enhancement] No uninstall hook to clean up data

## Priority: Low

## Description
The plugin doesn't clean up its options and data when uninstalled.

## Location
`ai-tools-for-wp.php` - missing uninstall handling

## Problem
- Plugin options remain in database after uninstall
- API keys (even encrypted) persist
- Poor hygiene for users who remove the plugin

## Data to Clean
- `aitwp_openai_key`
- `aitwp_anthropic_key`
- `aitwp_default_provider`
- `aitwp_auto_apply_tags`
- `aitwp_voice_profiles`
- `aitwp_audiences`

## Recommendation
Create `uninstall.php` in plugin root:

```php
<?php
// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin options
$options = array(
    'aitwp_openai_key',
    'aitwp_anthropic_key',
    'aitwp_default_provider',
    'aitwp_auto_apply_tags',
    'aitwp_voice_profiles',
    'aitwp_audiences',
);

foreach ( $options as $option ) {
    delete_option( $option );
}
```

## Labels
`enhancement`, `cleanup`
