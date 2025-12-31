/**
 * Rewriter Panel Component
 *
 * Rewrites post content using voice profiles.
 */

import { useState, useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import {
    PanelBody,
    SelectControl,
    Button,
    Spinner,
    Notice,
    Modal,
    TextareaControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { fetchVoiceProfiles, rewriteContent } from '../api/endpoints';

/**
 * RewriterPanel Component
 */
const RewriterPanel = () => {
    const [ profiles, setProfiles ] = useState( [] );
    const [ selectedProfile, setSelectedProfile ] = useState( '' );
    const [ isLoading, setIsLoading ] = useState( true );
    const [ isRewriting, setIsRewriting ] = useState( false );
    const [ rewrittenContent, setRewrittenContent ] = useState( '' );
    const [ showPreview, setShowPreview ] = useState( false );
    const [ error, setError ] = useState( null );

    // Get post content
    const postContent = useSelect( ( select ) => {
        return select( 'core/editor' ).getEditedPostContent();
    }, [] );

    // Dispatch for editing post
    const { editPost } = useDispatch( 'core/editor' );

    // Load voice profiles on mount
    useEffect( () => {
        loadProfiles();
    }, [] );

    /**
     * Load available voice profiles.
     */
    const loadProfiles = async () => {
        try {
            setIsLoading( true );
            setError( null );
            const data = await fetchVoiceProfiles();

            // Convert object to array
            const profileList = Object.values( data || {} );
            setProfiles( profileList );

            // Auto-select first profile if available
            if ( profileList.length > 0 ) {
                setSelectedProfile( profileList[ 0 ].id );
            }
        } catch ( err ) {
            setError( err.message || __( 'Failed to load voice profiles.', 'ai-tools-for-wp' ) );
        } finally {
            setIsLoading( false );
        }
    };

    /**
     * Handle rewrite request.
     */
    const handleRewrite = async () => {
        if ( ! postContent || postContent.trim().length < 50 ) {
            setError( __( 'Please add more content before rewriting.', 'ai-tools-for-wp' ) );
            return;
        }

        if ( ! selectedProfile ) {
            setError( __( 'Please select a voice profile.', 'ai-tools-for-wp' ) );
            return;
        }

        try {
            setIsRewriting( true );
            setError( null );
            setRewrittenContent( '' );

            // Get selected audience
            const audienceId = window.aitwpSelectedAudience || '';

            const result = await rewriteContent( postContent, selectedProfile, audienceId );

            if ( result.rewritten_content ) {
                setRewrittenContent( result.rewritten_content );
                setShowPreview( true );
            }
        } catch ( err ) {
            setError( err.message || __( 'Failed to rewrite content.', 'ai-tools-for-wp' ) );
        } finally {
            setIsRewriting( false );
        }
    };

    /**
     * Accept and apply rewritten content.
     */
    const handleAccept = () => {
        // Replace post content with rewritten version
        editPost( { content: rewrittenContent } );

        // Close modal and clear
        setShowPreview( false );
        setRewrittenContent( '' );
    };

    /**
     * Reject rewritten content.
     */
    const handleReject = () => {
        setShowPreview( false );
        setRewrittenContent( '' );
    };

    /**
     * Build options for profile select.
     */
    const getProfileOptions = () => {
        return profiles.map( ( profile ) => ( {
            value: profile.id,
            label: profile.name,
        } ) );
    };

    /**
     * Get currently selected profile name.
     */
    const getSelectedProfileName = () => {
        const profile = profiles.find( ( p ) => p.id === selectedProfile );
        return profile?.name || '';
    };

    if ( isLoading ) {
        return (
            <PanelBody title={ __( 'Rewrite Content', 'ai-tools-for-wp' ) } initialOpen={ false }>
                <div className="aitwp-loading">
                    <Spinner />
                    <span>{ __( 'Loading voice profiles...', 'ai-tools-for-wp' ) }</span>
                </div>
            </PanelBody>
        );
    }

    if ( profiles.length === 0 ) {
        return (
            <PanelBody title={ __( 'Rewrite Content', 'ai-tools-for-wp' ) } initialOpen={ false }>
                <Notice status="info" isDismissible={ false }>
                    { __( 'No voice profiles defined. Add profiles in Settings → AI Tools.', 'ai-tools-for-wp' ) }
                </Notice>
            </PanelBody>
        );
    }

    return (
        <>
            <PanelBody title={ __( 'Rewrite Content', 'ai-tools-for-wp' ) } initialOpen={ false }>
                { error && (
                    <Notice status="error" isDismissible onDismiss={ () => setError( null ) }>
                        { error }
                    </Notice>
                ) }

                <p className="aitwp-panel-description">
                    { __( 'Rewrite your content using a voice profile to match your desired tone and style.', 'ai-tools-for-wp' ) }
                </p>

                <SelectControl
                    label={ __( 'Voice Profile', 'ai-tools-for-wp' ) }
                    value={ selectedProfile }
                    options={ getProfileOptions() }
                    onChange={ setSelectedProfile }
                />

                <Button
                    variant="primary"
                    onClick={ handleRewrite }
                    disabled={ isRewriting || ! selectedProfile }
                    className="aitwp-rewrite-button"
                >
                    { isRewriting ? (
                        <>
                            <Spinner />
                            { __( 'Rewriting...', 'ai-tools-for-wp' ) }
                        </>
                    ) : (
                        __( 'Rewrite Content', 'ai-tools-for-wp' )
                    ) }
                </Button>
            </PanelBody>

            { showPreview && (
                <Modal
                    title={ __( 'Rewritten Content Preview', 'ai-tools-for-wp' ) }
                    onRequestClose={ handleReject }
                    className="aitwp-preview-modal"
                    size="large"
                >
                    <p className="aitwp-preview-info">
                        { __( 'Using voice profile:', 'ai-tools-for-wp' ) }{ ' ' }
                        <strong>{ getSelectedProfileName() }</strong>
                    </p>

                    <TextareaControl
                        label={ __( 'Preview', 'ai-tools-for-wp' ) }
                        value={ rewrittenContent }
                        onChange={ setRewrittenContent }
                        rows={ 15 }
                        className="aitwp-preview-textarea"
                        help={ __( 'You can edit the content before accepting.', 'ai-tools-for-wp' ) }
                    />

                    <div className="aitwp-preview-actions">
                        <Button variant="primary" onClick={ handleAccept }>
                            { __( 'Accept & Replace', 'ai-tools-for-wp' ) }
                        </Button>
                        <Button variant="secondary" onClick={ handleReject }>
                            { __( 'Reject', 'ai-tools-for-wp' ) }
                        </Button>
                    </div>
                </Modal>
            ) }
        </>
    );
};

export default RewriterPanel;
