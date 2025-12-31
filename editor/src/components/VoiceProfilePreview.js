/**
 * Voice Profile Preview Component
 *
 * Shows a preview of a voice profile's details.
 */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { chevronDown, chevronUp } from '@wordpress/icons';

/**
 * Get energy level display label.
 */
const getEnergyLabel = ( level ) => {
    const labels = {
        low: __( 'Low - Calm', 'ai-tools-for-wp' ),
        medium: __( 'Medium', 'ai-tools-for-wp' ),
        high: __( 'High - Dynamic', 'ai-tools-for-wp' ),
        variable: __( 'Variable', 'ai-tools-for-wp' ),
    };
    return labels[ level ] || level;
};

/**
 * Get humor style display label.
 */
const getHumorLabel = ( style ) => {
    const labels = {
        none: __( 'Serious', 'ai-tools-for-wp' ),
        subtle: __( 'Subtle humor', 'ai-tools-for-wp' ),
        moderate: __( 'Moderate', 'ai-tools-for-wp' ),
        frequent: __( 'Playful', 'ai-tools-for-wp' ),
    };
    return labels[ style ] || style;
};

/**
 * VoiceProfilePreview Component
 */
const VoiceProfilePreview = ( { profile } ) => {
    const [ isExpanded, setIsExpanded ] = useState( false );

    if ( ! profile ) {
        return null;
    }

    // Get tone settings
    const toneEnergy = profile.tone_energy || {};
    const guardrails = profile.guardrails || {};

    // Check if we have structured data
    const hasStructuredData = (
        profile.voice_identity ||
        toneEnergy.energy_level ||
        ( guardrails.never_words && guardrails.never_words.length > 0 )
    );

    // Fall back to old content field
    const voiceIdentity = profile.voice_identity || profile.content || '';

    const renderBadges = () => {
        const badges = [];

        if ( toneEnergy.energy_level ) {
            badges.push(
                <span key="energy" className="aitwp-profile-badge">
                    { getEnergyLabel( toneEnergy.energy_level ) }
                </span>
            );
        }

        if ( toneEnergy.humor_style && toneEnergy.humor_style !== 'none' ) {
            badges.push(
                <span key="humor" className="aitwp-profile-badge">
                    { getHumorLabel( toneEnergy.humor_style ) }
                </span>
            );
        }

        return badges.length > 0 ? (
            <div className="aitwp-profile-badges">{ badges }</div>
        ) : null;
    };

    const renderGuardrails = () => {
        const neverWords = guardrails.never_words || [];
        const alwaysDo = guardrails.always_do || [];

        if ( neverWords.length === 0 && alwaysDo.length === 0 ) {
            return null;
        }

        return (
            <div className="aitwp-profile-guardrails">
                { neverWords.length > 0 && (
                    <div className="aitwp-guardrail-section">
                        <p className="aitwp-guardrail-label">
                            { __( 'Avoids:', 'ai-tools-for-wp' ) }
                        </p>
                        <p className="aitwp-guardrail-value">
                            { neverWords.slice( 0, 5 ).join( ', ' ) }
                            { neverWords.length > 5 && ` +${ neverWords.length - 5 } more` }
                        </p>
                    </div>
                ) }
                { alwaysDo.length > 0 && (
                    <div className="aitwp-guardrail-section">
                        <p className="aitwp-guardrail-label">
                            { __( 'Always:', 'ai-tools-for-wp' ) }
                        </p>
                        <p className="aitwp-guardrail-value">
                            { alwaysDo.slice( 0, 3 ).join( ', ' ) }
                            { alwaysDo.length > 3 && ` +${ alwaysDo.length - 3 } more` }
                        </p>
                    </div>
                ) }
            </div>
        );
    };

    const truncateWords = ( text, maxWords = 30 ) => {
        if ( ! text ) return '';
        const words = text.split( /\s+/ );
        if ( words.length <= maxWords ) return text;
        return words.slice( 0, maxWords ).join( ' ' ) + '...';
    };

    return (
        <div className="aitwp-profile-preview">
            <button
                className="aitwp-preview-toggle"
                onClick={ () => setIsExpanded( ! isExpanded ) }
                type="button"
            >
                <span className="aitwp-preview-title">
                    { __( 'About this voice', 'ai-tools-for-wp' ) }
                </span>
                { isExpanded ? chevronUp : chevronDown }
            </button>

            { isExpanded && (
                <div className="aitwp-preview-content">
                    { renderBadges() }

                    { voiceIdentity && (
                        <div className="aitwp-profile-section">
                            <p className="aitwp-profile-section-label">
                                { __( 'Voice identity:', 'ai-tools-for-wp' ) }
                            </p>
                            <p className="aitwp-profile-description">
                                { truncateWords( voiceIdentity, 40 ) }
                            </p>
                        </div>
                    ) }

                    { renderGuardrails() }

                    { ! hasStructuredData && ! voiceIdentity && (
                        <p className="aitwp-no-details">
                            { __( 'No detailed information available for this profile.', 'ai-tools-for-wp' ) }
                        </p>
                    ) }
                </div>
            ) }
        </div>
    );
};

export default VoiceProfilePreview;
