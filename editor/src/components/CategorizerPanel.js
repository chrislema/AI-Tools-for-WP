/**
 * Categorizer Panel Component
 *
 * Analyzes post content and suggests categories/tags.
 */

import { useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import {
    PanelBody,
    Button,
    Spinner,
    Notice,
    CheckboxControl,
    ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { categorizeContent } from '../api/endpoints';
import { STORE_NAME } from '../store';

/**
 * CategorizerPanel Component
 */
const CategorizerPanel = () => {
    const [ isAnalyzing, setIsAnalyzing ] = useState( false );
    const [ suggestions, setSuggestions ] = useState( null );
    const [ selectedCategories, setSelectedCategories ] = useState( [] );
    const [ selectedTags, setSelectedTags ] = useState( [] );
    const [ autoApply, setAutoApply ] = useState( window.aitwpData?.autoApply || false );
    const [ error, setError ] = useState( null );

    // Get post content, current terms, and selected audience from stores
    const { postContent, currentCategories, currentTags, selectedAudienceId } = useSelect( ( select ) => {
        const editor = select( 'core/editor' );
        const aitwpStore = select( STORE_NAME );
        return {
            postContent: editor.getEditedPostContent(),
            currentCategories: editor.getEditedPostAttribute( 'categories' ) || [],
            currentTags: editor.getEditedPostAttribute( 'tags' ) || [],
            selectedAudienceId: aitwpStore.getSelectedAudienceId(),
        };
    }, [] );

    // Dispatch for editing post
    const { editPost } = useDispatch( 'core/editor' );

    /**
     * Analyze content for suggestions.
     */
    const handleAnalyze = async () => {
        if ( ! postContent || postContent.trim().length < 50 ) {
            setError( __( 'Please add more content before analyzing.', 'ai-tools-for-wp' ) );
            return;
        }

        try {
            setIsAnalyzing( true );
            setError( null );
            setSuggestions( null );

            const result = await categorizeContent( postContent, selectedAudienceId );

            setSuggestions( result );

            // Pre-select all suggestions
            setSelectedCategories( result.categories?.map( ( c ) => c.id ) || [] );
            setSelectedTags( result.tags?.map( ( t ) => t.id ) || [] );

            // Auto-apply if enabled
            if ( autoApply ) {
                applySelectionsFromResult( result );
            }
        } catch ( err ) {
            setError( err.message || __( 'Failed to analyze content.', 'ai-tools-for-wp' ) );
        } finally {
            setIsAnalyzing( false );
        }
    };

    /**
     * Apply selections from fresh result (for auto-apply).
     */
    const applySelectionsFromResult = ( result ) => {
        const catIds = result.categories?.map( ( c ) => c.id ) || [];
        const tagIds = result.tags?.map( ( t ) => t.id ) || [];

        // Merge with existing
        const newCategories = [ ...new Set( [ ...currentCategories, ...catIds ] ) ];
        const newTags = [ ...new Set( [ ...currentTags, ...tagIds ] ) ];

        editPost( {
            categories: newCategories,
            tags: newTags,
        } );
    };

    /**
     * Toggle category selection.
     */
    const toggleCategory = ( catId ) => {
        setSelectedCategories( ( prev ) =>
            prev.includes( catId )
                ? prev.filter( ( id ) => id !== catId )
                : [ ...prev, catId ]
        );
    };

    /**
     * Toggle tag selection.
     */
    const toggleTag = ( tagId ) => {
        setSelectedTags( ( prev ) =>
            prev.includes( tagId )
                ? prev.filter( ( id ) => id !== tagId )
                : [ ...prev, tagId ]
        );
    };

    /**
     * Apply selected categories and tags.
     */
    const handleApply = () => {
        // Merge with existing
        const newCategories = [ ...new Set( [ ...currentCategories, ...selectedCategories ] ) ];
        const newTags = [ ...new Set( [ ...currentTags, ...selectedTags ] ) ];

        editPost( {
            categories: newCategories,
            tags: newTags,
        } );

        // Clear suggestions after applying
        setSuggestions( null );
    };

    /**
     * Dismiss suggestions.
     */
    const handleDismiss = () => {
        setSuggestions( null );
        setSelectedCategories( [] );
        setSelectedTags( [] );
    };

    return (
        <PanelBody title={ __( 'Auto-Categorize', 'ai-tools-for-wp' ) } initialOpen={ false }>
            { error && (
                <Notice status="error" isDismissible onDismiss={ () => setError( null ) }>
                    { error }
                </Notice>
            ) }

            <p className="aitwp-panel-description">
                { __( 'Analyze your content to get AI-suggested categories and tags.', 'ai-tools-for-wp' ) }
            </p>

            <ToggleControl
                label={ __( 'Auto-apply suggestions', 'ai-tools-for-wp' ) }
                checked={ autoApply }
                onChange={ setAutoApply }
                help={ __( 'Automatically apply suggestions when analyzing.', 'ai-tools-for-wp' ) }
            />

            <Button
                variant="primary"
                onClick={ handleAnalyze }
                disabled={ isAnalyzing }
                className="aitwp-analyze-button"
            >
                { isAnalyzing ? (
                    <>
                        <Spinner />
                        { __( 'Analyzing...', 'ai-tools-for-wp' ) }
                    </>
                ) : (
                    __( 'Analyze Content', 'ai-tools-for-wp' )
                ) }
            </Button>

            { suggestions && (
                <div className="aitwp-suggestions">
                    { suggestions.reasoning && (
                        <p className="aitwp-reasoning">{ suggestions.reasoning }</p>
                    ) }

                    { suggestions.categories?.length > 0 && (
                        <div className="aitwp-suggestion-group">
                            <h4>{ __( 'Suggested Categories', 'ai-tools-for-wp' ) }</h4>
                            { suggestions.categories.map( ( cat ) => (
                                <CheckboxControl
                                    key={ cat.id }
                                    label={ cat.name }
                                    checked={ selectedCategories.includes( cat.id ) }
                                    onChange={ () => toggleCategory( cat.id ) }
                                />
                            ) ) }
                        </div>
                    ) }

                    { suggestions.tags?.length > 0 && (
                        <div className="aitwp-suggestion-group">
                            <h4>{ __( 'Suggested Tags', 'ai-tools-for-wp' ) }</h4>
                            { suggestions.tags.map( ( tag ) => (
                                <CheckboxControl
                                    key={ tag.id }
                                    label={ tag.name }
                                    checked={ selectedTags.includes( tag.id ) }
                                    onChange={ () => toggleTag( tag.id ) }
                                />
                            ) ) }
                        </div>
                    ) }

                    { suggestions.new_tags?.length > 0 && (
                        <div className="aitwp-suggestion-group">
                            <h4>{ __( 'Suggested New Tags', 'ai-tools-for-wp' ) }</h4>
                            <p className="aitwp-new-tags-note">
                                { __( 'These tags don\'t exist yet:', 'ai-tools-for-wp' ) }
                            </p>
                            <ul className="aitwp-new-tags-list">
                                { suggestions.new_tags.map( ( tag, index ) => (
                                    <li key={ index }>{ tag }</li>
                                ) ) }
                            </ul>
                        </div>
                    ) }

                    { ! autoApply && (
                        <div className="aitwp-suggestion-actions">
                            <Button
                                variant="primary"
                                onClick={ handleApply }
                                disabled={ selectedCategories.length === 0 && selectedTags.length === 0 }
                            >
                                { __( 'Apply Selected', 'ai-tools-for-wp' ) }
                            </Button>
                            <Button
                                variant="tertiary"
                                onClick={ handleDismiss }
                            >
                                { __( 'Dismiss', 'ai-tools-for-wp' ) }
                            </Button>
                        </div>
                    ) }
                </div>
            ) }
        </PanelBody>
    );
};

export default CategorizerPanel;
