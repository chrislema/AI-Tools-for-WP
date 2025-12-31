<?php
/**
 * Migration Class
 *
 * Handles data structure migrations for voice profiles and audiences.
 *
 * @package AI_Tools_For_WP
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AITWP_Migration
 *
 * Manages schema versioning and data migrations.
 */
class AITWP_Migration {

    /**
     * Current schema version.
     *
     * @var int
     */
    const CURRENT_VERSION = 2;

    /**
     * Option key for tracking migration status.
     *
     * @var string
     */
    const MIGRATION_OPTION = 'aitwp_schema_version';

    /**
     * Run migrations if needed.
     */
    public static function maybe_run_migrations() {
        $current_version = get_option( self::MIGRATION_OPTION, 1 );

        if ( $current_version < self::CURRENT_VERSION ) {
            self::run_migrations( $current_version );
            update_option( self::MIGRATION_OPTION, self::CURRENT_VERSION );
        }
    }

    /**
     * Run all necessary migrations.
     *
     * @param int $from_version The version to migrate from.
     */
    private static function run_migrations( $from_version ) {
        if ( $from_version < 2 ) {
            self::migrate_voice_profiles_v2();
            self::migrate_audiences_v2();
        }
    }

    /**
     * Migrate voice profiles to version 2 schema.
     */
    private static function migrate_voice_profiles_v2() {
        $profiles = get_option( 'aitwp_voice_profiles', array() );
        $updated  = false;

        foreach ( $profiles as $id => $profile ) {
            if ( ! isset( $profile['version'] ) || $profile['version'] < 2 ) {
                // Migrate: content field becomes voice_identity
                $profiles[ $id ] = array_merge(
                    self::get_default_voice_profile(),
                    array(
                        'id'             => $id,
                        'name'           => $profile['name'] ?? '',
                        'updated'        => $profile['updated'] ?? current_time( 'mysql' ),
                        'version'        => self::CURRENT_VERSION,
                        'voice_identity' => $profile['content'] ?? '',
                    )
                );
                $updated = true;
            }
        }

        if ( $updated ) {
            update_option( 'aitwp_voice_profiles', $profiles );
        }
    }

    /**
     * Migrate audiences to version 2 schema.
     */
    private static function migrate_audiences_v2() {
        $audiences = get_option( 'aitwp_audiences', array() );
        $updated   = false;

        foreach ( $audiences as $id => $audience ) {
            if ( ! isset( $audience['version'] ) || $audience['version'] < 2 ) {
                // Migrate: description field becomes definition
                $audiences[ $id ] = array(
                    'id'           => $id,
                    'name'         => $audience['name'] ?? '',
                    'updated'      => $audience['updated'] ?? current_time( 'mysql' ),
                    'version'      => self::CURRENT_VERSION,
                    'definition'   => $audience['description'] ?? '',
                    'goals'        => array(),
                    'pains'        => array(),
                    'hopes_dreams' => array(),
                    'fears'        => array(),
                );
                $updated = true;
            }
        }

        if ( $updated ) {
            update_option( 'aitwp_audiences', $audiences );
        }
    }

    /**
     * Get default voice profile structure.
     *
     * @return array Default voice profile fields.
     */
    public static function get_default_voice_profile() {
        return array(
            'id'      => '',
            'name'    => '',
            'updated' => '',
            'version' => self::CURRENT_VERSION,

            // Voice Identity
            'voice_identity' => '',

            // Tone & Energy
            'tone_energy' => array(
                'energy_level'    => 'medium',
                'humor_style'     => 'subtle',
                'emotional_range' => 'balanced',
            ),

            // Language Patterns
            'language_patterns' => array(
                'sentence_structure' => '',
                'vocabulary'         => '',
                'contractions'       => '',
                'punctuation'        => '',
            ),

            // Additional Patterns
            'additional_patterns' => array(
                'paragraph_structure' => '',
                'opening_moves'       => '',
                'closing_moves'       => '',
                'transitions'         => '',
                'examples_evidence'   => '',
                'distinctive'         => '',
            ),

            // Philosophy sections
            'content_philosophy'    => '',
            'credibility_authority' => '',
            'audience_relationship' => '',
            'handling_disagreement' => '',

            // Platform Adaptation
            'platform_adaptation' => array(
                'twitter'  => '',
                'linkedin' => '',
                'facebook' => '',
                'blog'     => '',
            ),

            // Guardrails
            'guardrails' => array(
                'never_words'    => array(),
                'never_phrases'  => array(),
                'never_patterns' => array(),
                'always_do'      => array(),
            ),

            // Quick Reference
            'quick_reference' => array(),
        );
    }

    /**
     * Get default audience structure.
     *
     * @return array Default audience fields.
     */
    public static function get_default_audience() {
        return array(
            'id'           => '',
            'name'         => '',
            'updated'      => '',
            'version'      => self::CURRENT_VERSION,
            'definition'   => '',
            'goals'        => array(),
            'pains'        => array(),
            'hopes_dreams' => array(),
            'fears'        => array(),
        );
    }

    /**
     * Convert a textarea with one item per line to an array.
     *
     * @param string $text The text with newline-separated items.
     * @return array Array of items.
     */
    public static function text_to_array( $text ) {
        if ( empty( $text ) ) {
            return array();
        }

        $lines = explode( "\n", $text );
        $items = array();

        foreach ( $lines as $line ) {
            $line = trim( $line );
            // Remove leading bullet points or dashes
            $line = preg_replace( '/^[-•*]\s*/', '', $line );
            if ( ! empty( $line ) ) {
                $items[] = $line;
            }
        }

        return $items;
    }

    /**
     * Convert an array to text with one item per line.
     *
     * @param array $items The array of items.
     * @return string Text with newline-separated items.
     */
    public static function array_to_text( $items ) {
        if ( empty( $items ) || ! is_array( $items ) ) {
            return '';
        }

        return implode( "\n", $items );
    }
}
