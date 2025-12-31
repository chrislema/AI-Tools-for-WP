/**
 * AI Tools for WP - API Endpoints
 *
 * REST API helpers for the editor sidebar.
 */

import apiFetch from '@wordpress/api-fetch';

const API_BASE = '/ai-tools/v1';

/**
 * Fetch available voice profiles.
 *
 * @return {Promise<Array>} Array of voice profiles.
 */
export const fetchVoiceProfiles = async () => {
    return apiFetch( {
        path: `${ API_BASE }/voice-profiles`,
        method: 'GET',
    } );
};

/**
 * Fetch available audiences.
 *
 * @return {Promise<Array>} Array of audiences.
 */
export const fetchAudiences = async () => {
    return apiFetch( {
        path: `${ API_BASE }/audiences`,
        method: 'GET',
    } );
};

/**
 * Analyze content for category/tag suggestions.
 *
 * @param {string} content    The post content.
 * @param {string} audienceId Optional audience ID.
 * @return {Promise<Object>} Categorization results.
 */
export const categorizeContent = async ( content, audienceId = '' ) => {
    return apiFetch( {
        path: `${ API_BASE }/categorize`,
        method: 'POST',
        data: {
            content,
            audience_id: audienceId,
        },
    } );
};

/**
 * Get AI suggestion for best audience.
 *
 * @param {string} content The post content.
 * @return {Promise<Object>} Audience suggestion.
 */
export const suggestAudience = async ( content ) => {
    return apiFetch( {
        path: `${ API_BASE }/suggest-audience`,
        method: 'POST',
        data: {
            content,
        },
    } );
};

/**
 * Rewrite content using a voice profile.
 *
 * @param {string} content        The content to rewrite.
 * @param {string} voiceProfileId The voice profile ID.
 * @param {string} audienceId     Optional audience ID.
 * @return {Promise<Object>} Rewrite result with rewritten_content.
 */
export const rewriteContent = async ( content, voiceProfileId, audienceId = '' ) => {
    return apiFetch( {
        path: `${ API_BASE }/rewrite`,
        method: 'POST',
        data: {
            content,
            voice_profile_id: voiceProfileId,
            audience_id: audienceId,
        },
    } );
};
