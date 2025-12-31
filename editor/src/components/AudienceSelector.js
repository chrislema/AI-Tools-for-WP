/**
 * Audience Selector Component
 *
 * Allows users to select a target audience for the current post.
 */

import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import {
    PanelBody,
    SelectControl,
    Button,
    Spinner,
    Notice,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { fetchAudiences, suggestAudience } from '../api/endpoints';

/**
 * AudienceSelector Component
 */
const AudienceSelector = () => {
    const [ audiences, setAudiences ] = useState( [] );
    const [ selectedAudience, setSelectedAudience ] = useState( '' );
    const [ isLoading, setIsLoading ] = useState( true );
    const [ isSuggesting, setIsSuggesting ] = useState( false );
    const [ suggestion, setSuggestion ] = useState( null );
    const [ error, setError ] = useState( null );

    // Get post content from editor
    const postContent = useSelect( ( select ) => {
        const { getEditedPostContent } = select( 'core/editor' );
        return getEditedPostContent();
    }, [] );

    // Load audiences on mount
    useEffect( () => {
        loadAudiences();
    }, [] );

    /**
     * Load available audiences from API.
     */
    const loadAudiences = async () => {
        try {
            setIsLoading( true );
            setError( null );
            const data = await fetchAudiences();

            // Convert object to array
            const audienceList = Object.values( data || {} );
            setAudiences( audienceList );
        } catch ( err ) {
            setError( err.message || __( 'Failed to load audiences.', 'ai-tools-for-wp' ) );
        } finally {
            setIsLoading( false );
        }
    };

    /**
     * Get AI suggestion for best audience.
     */
    const handleSuggest = async () => {
        if ( ! postContent || postContent.trim().length < 50 ) {
            setError( __( 'Please add more content before requesting a suggestion.', 'ai-tools-for-wp' ) );
            return;
        }

        try {
            setIsSuggesting( true );
            setError( null );
            setSuggestion( null );

            const result = await suggestAudience( postContent );

            setSuggestion( result );

            // Auto-select the suggested audience
            if ( result.audience_id ) {
                setSelectedAudience( result.audience_id );
            }
        } catch ( err ) {
            setError( err.message || __( 'Failed to get suggestion.', 'ai-tools-for-wp' ) );
        } finally {
            setIsSuggesting( false );
        }
    };

    /**
     * Build options for select control.
     */
    const getAudienceOptions = () => {
        const options = [
            { value: '', label: __( '— Select Audience —', 'ai-tools-for-wp' ) },
        ];

        audiences.forEach( ( audience ) => {
            options.push( {
                value: audience.id,
                label: audience.name,
            } );
        } );

        return options;
    };

    // Store selected audience in a way other components can access
    useEffect( () => {
        window.aitwpSelectedAudience = selectedAudience;
    }, [ selectedAudience ] );

    if ( isLoading ) {
        return (
            <PanelBody title={ __( 'Target Audience', 'ai-tools-for-wp' ) } initialOpen={ true }>
                <div className="aitwp-loading">
                    <Spinner />
                    <span>{ __( 'Loading audiences...', 'ai-tools-for-wp' ) }</span>
                </div>
            </PanelBody>
        );
    }

    if ( audiences.length === 0 ) {
        return (
            <PanelBody title={ __( 'Target Audience', 'ai-tools-for-wp' ) } initialOpen={ true }>
                <Notice status="info" isDismissible={ false }>
                    { __( 'No audiences defined. Add audiences in Settings → AI Tools.', 'ai-tools-for-wp' ) }
                </Notice>
            </PanelBody>
        );
    }

    return (
        <PanelBody title={ __( 'Target Audience', 'ai-tools-for-wp' ) } initialOpen={ true }>
            { error && (
                <Notice status="error" isDismissible onDismiss={ () => setError( null ) }>
                    { error }
                </Notice>
            ) }

            <SelectControl
                label={ __( 'Select Audience', 'ai-tools-for-wp' ) }
                value={ selectedAudience }
                options={ getAudienceOptions() }
                onChange={ ( value ) => {
                    setSelectedAudience( value );
                    setSuggestion( null );
                } }
            />

            <Button
                variant="secondary"
                onClick={ handleSuggest }
                disabled={ isSuggesting }
                className="aitwp-suggest-button"
            >
                { isSuggesting ? (
                    <>
                        <Spinner />
                        { __( 'Analyzing...', 'ai-tools-for-wp' ) }
                    </>
                ) : (
                    __( 'Suggest Audience', 'ai-tools-for-wp' )
                ) }
            </Button>

            { suggestion && (
                <div className="aitwp-suggestion">
                    <p className="aitwp-suggestion-label">
                        { __( 'AI Suggestion:', 'ai-tools-for-wp' ) }
                    </p>
                    <p className="aitwp-suggestion-value">
                        <strong>{ suggestion.audience?.name }</strong>
                        { suggestion.confidence && (
                            <span className="aitwp-confidence">
                                { ` (${ suggestion.confidence }% match)` }
                            </span>
                        ) }
                    </p>
                    { suggestion.reasoning && (
                        <p className="aitwp-suggestion-reasoning">
                            { suggestion.reasoning }
                        </p>
                    ) }
                </div>
            ) }
        </PanelBody>
    );
};

export default AudienceSelector;
