/**
 * AI Tools for WP - WordPress Data Store
 *
 * Centralized state management using @wordpress/data.
 */

import { createReduxStore, register } from '@wordpress/data';

/**
 * Store name
 */
export const STORE_NAME = 'aitwp/editor';

/**
 * Default state
 */
const DEFAULT_STATE = {
    selectedAudienceId: '',
    audiences: [],
    voiceProfiles: [],
    isLoadingAudiences: false,
    isLoadingProfiles: false,
};

/**
 * Action types
 */
const SET_SELECTED_AUDIENCE = 'SET_SELECTED_AUDIENCE';
const SET_AUDIENCES = 'SET_AUDIENCES';
const SET_VOICE_PROFILES = 'SET_VOICE_PROFILES';
const SET_LOADING_AUDIENCES = 'SET_LOADING_AUDIENCES';
const SET_LOADING_PROFILES = 'SET_LOADING_PROFILES';

/**
 * Actions
 */
const actions = {
    setSelectedAudience( audienceId ) {
        return {
            type: SET_SELECTED_AUDIENCE,
            audienceId,
        };
    },

    setAudiences( audiences ) {
        return {
            type: SET_AUDIENCES,
            audiences,
        };
    },

    setVoiceProfiles( profiles ) {
        return {
            type: SET_VOICE_PROFILES,
            profiles,
        };
    },

    setLoadingAudiences( isLoading ) {
        return {
            type: SET_LOADING_AUDIENCES,
            isLoading,
        };
    },

    setLoadingProfiles( isLoading ) {
        return {
            type: SET_LOADING_PROFILES,
            isLoading,
        };
    },
};

/**
 * Selectors
 */
const selectors = {
    getSelectedAudienceId( state ) {
        return state.selectedAudienceId;
    },

    getSelectedAudience( state ) {
        if ( ! state.selectedAudienceId ) {
            return null;
        }
        return state.audiences.find( ( a ) => a.id === state.selectedAudienceId ) || null;
    },

    getAudiences( state ) {
        return state.audiences;
    },

    getVoiceProfiles( state ) {
        return state.voiceProfiles;
    },

    isLoadingAudiences( state ) {
        return state.isLoadingAudiences;
    },

    isLoadingProfiles( state ) {
        return state.isLoadingProfiles;
    },
};

/**
 * Reducer
 */
function reducer( state = DEFAULT_STATE, action ) {
    switch ( action.type ) {
        case SET_SELECTED_AUDIENCE:
            return {
                ...state,
                selectedAudienceId: action.audienceId,
            };

        case SET_AUDIENCES:
            return {
                ...state,
                audiences: action.audiences,
            };

        case SET_VOICE_PROFILES:
            return {
                ...state,
                voiceProfiles: action.profiles,
            };

        case SET_LOADING_AUDIENCES:
            return {
                ...state,
                isLoadingAudiences: action.isLoading,
            };

        case SET_LOADING_PROFILES:
            return {
                ...state,
                isLoadingProfiles: action.isLoading,
            };

        default:
            return state;
    }
}

/**
 * Create and register the store
 */
const store = createReduxStore( STORE_NAME, {
    reducer,
    actions,
    selectors,
} );

register( store );

export default store;
