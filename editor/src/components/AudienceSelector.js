/**
 * Audience Selector Component
 *
 * Allows users to select a target audience for the current post.
 */

import { useState, useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import {
    PanelBody,
    PanelRow,
    SelectControl,
    Button,
    Spinner,
    Notice,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { chevronDown, chevronUp } from '@wordpress/icons';

import { fetchAudiences, suggestAudience } from '../api/endpoints';
import { STORE_NAME } from '../store';

/**
 * Truncate text to a certain number of words.
 */
const truncateWords = ( text, maxWords = 20 ) => {
    if ( ! text ) return '';
    const words = text.split( /\s+/ );
    if ( words.length <= maxWords ) return text;
    return words.slice( 0, maxWords ).join( ' ' ) + '...';
};

/**
 * AudiencePreview Component
 *
 * Shows a preview of the selected audience's details.
 */
const AudiencePreview = ( { audience } ) => {
    const [ isExpanded, setIsExpanded ] = useState( false );

    if ( ! audience ) {
        return null;
    }

    // Get definition (handles both old 'description' and new 'definition' field)
    const definition = audience.definition || audience.description || '';
    const goals = audience.goals || [];
    const pains = audience.pains || [];
    const hopesDreams = audience.hopes_dreams || [];
    const fears = audience.fears || [];

    // Check if we have any detailed data
    const hasDetails = goals.length > 0 || pains.length > 0 || hopesDreams.length > 0 || fears.length > 0;

    const renderList = ( items, label, maxShow = 3 ) => {
        if ( ! items || items.length === 0 ) return null;
        const shown = items.slice( 0, maxShow );
        const remaining = items.length - maxShow;

        return (
            <div className="aitwp-audience-section">
                <p className="aitwp-audience-section-label">{ label }</p>
                <ul className="aitwp-audience-list">
                    { shown.map( ( item, index ) => (
                        <li key={ index }>{ item }</li>
                    ) ) }
                </ul>
                { remaining > 0 && (
                    <p className="aitwp-audience-more">
                        { `+${ remaining } more` }
                    </p>
                ) }
            </div>
        );
    };

    return (
        <div className="aitwp-audience-preview">
            <button
                className="aitwp-preview-toggle"
                onClick={ () => setIsExpanded( ! isExpanded ) }
                type="button"
            >
                <span className="aitwp-preview-title">
                    { __( 'About this audience', 'ai-tools-for-wp' ) }
                </span>
                { isExpanded ? chevronUp : chevronDown }
            </button>

            { isExpanded && (
                <div className="aitwp-preview-content">
                    { definition && (
                        <div className="aitwp-audience-section">
                            <p className="aitwp-audience-section-label">
                                { __( 'Who they are:', 'ai-tools-for-wp' ) }
                            </p>
                            <p className="aitwp-audience-definition">
                                { truncateWords( definition, 40 ) }
                            </p>
                        </div>
                    ) }

                    { hasDetails && (
                        <>
                            { renderList( goals, __( 'Goals:', 'ai-tools-for-wp' ) ) }
                            { renderList( pains, __( 'Pain points:', 'ai-tools-for-wp' ) ) }
                        </>
                    ) }

                    { ! definition && ! hasDetails && (
                        <p className="aitwp-no-details">
                            { __( 'No detailed information available for this audience.', 'ai-tools-for-wp' ) }
                        </p>
                    ) }
                </div>
            ) }
        </div>
    );
};

/**
 * AudienceSelector Component
 */
const AudienceSelector = () => {
    const [ isSuggesting, setIsSuggesting ] = useState( false );
    const [ suggestion, setSuggestion ] = useState( null );
    const [ error, setError ] = useState( null );

    // Get state from our custom store
    const { audiences, selectedAudienceId, isLoading } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            audiences: store.getAudiences(),
            selectedAudienceId: store.getSelectedAudienceId(),
            isLoading: store.isLoadingAudiences(),
        };
    }, [] );

    // Get post content from editor
    const postContent = useSelect( ( select ) => {
        const { getEditedPostContent } = select( 'core/editor' );
        return getEditedPostContent();
    }, [] );

    // Dispatch actions
    const { setSelectedAudience, setAudiences, setLoadingAudiences } = useDispatch( STORE_NAME );

    // Load audiences on mount
    useEffect( () => {
        loadAudiences();
    }, [] );

    /**
     * Get the currently selected audience object.
     */
    const getSelectedAudience = () => {
        if ( ! selectedAudienceId ) return null;
        return audiences.find( ( a ) => a.id === selectedAudienceId ) || null;
    };

    /**
     * Load available audiences from API.
     */
    const loadAudiences = async () => {
        try {
            setLoadingAudiences( true );
            setError( null );
            const data = await fetchAudiences();

            // Convert object to array
            const audienceList = Object.values( data || {} );
            setAudiences( audienceList );
        } catch ( err ) {
            setError( err.message || __( 'Failed to load audiences.', 'ai-tools-for-wp' ) );
        } finally {
            setLoadingAudiences( false );
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

    const selectedAudience = getSelectedAudience();

    return (
        <PanelBody title={ __( 'Target Audience', 'ai-tools-for-wp' ) } initialOpen={ true }>
            { error && (
                <Notice status="error" isDismissible onDismiss={ () => setError( null ) }>
                    { error }
                </Notice>
            ) }

            <SelectControl
                label={ __( 'Select Audience', 'ai-tools-for-wp' ) }
                value={ selectedAudienceId }
                options={ getAudienceOptions() }
                onChange={ ( value ) => {
                    setSelectedAudience( value );
                    setSuggestion( null );
                } }
            />

            { selectedAudience && (
                <AudiencePreview audience={ selectedAudience } />
            ) }

            <PanelRow>
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
            </PanelRow>

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
