/**
 * AI Tools for WP - Editor Sidebar
 *
 * Main entry point for the block editor sidebar plugin.
 */

import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { starFilled } from '@wordpress/icons';

// Import store to register it
import './store';

import ErrorBoundary from './components/ErrorBoundary';
import AudienceSelector from './components/AudienceSelector';
import CategorizerPanel from './components/CategorizerPanel';
import RewriterPanel from './components/RewriterPanel';

import './editor.css';

/**
 * AI Tools Sidebar Component
 */
const AIToolsSidebar = () => {
    return (
        <>
            <PluginSidebarMoreMenuItem target="ai-tools-sidebar">
                { __( 'AI Tools', 'ai-tools-for-wp' ) }
            </PluginSidebarMoreMenuItem>
            <PluginSidebar
                name="ai-tools-sidebar"
                title={ __( 'AI Tools', 'ai-tools-for-wp' ) }
                icon={ starFilled }
            >
                <div className="aitwp-sidebar">
                    <ErrorBoundary panelTitle={ __( 'Target Audience', 'ai-tools-for-wp' ) }>
                        <AudienceSelector />
                    </ErrorBoundary>
                    <ErrorBoundary panelTitle={ __( 'Auto-Categorize', 'ai-tools-for-wp' ) }>
                        <CategorizerPanel />
                    </ErrorBoundary>
                    <ErrorBoundary panelTitle={ __( 'Rewrite Content', 'ai-tools-for-wp' ) }>
                        <RewriterPanel />
                    </ErrorBoundary>
                </div>
            </PluginSidebar>
        </>
    );
};

// Register the plugin
registerPlugin( 'ai-tools-for-wp', {
    render: AIToolsSidebar,
    icon: starFilled,
} );
