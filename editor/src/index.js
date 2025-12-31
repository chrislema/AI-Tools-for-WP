/**
 * AI Tools for WP - Editor Sidebar
 *
 * Main entry point for the block editor sidebar plugin.
 */

import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { starFilled } from '@wordpress/icons';

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
                    <AudienceSelector />
                    <CategorizerPanel />
                    <RewriterPanel />
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
