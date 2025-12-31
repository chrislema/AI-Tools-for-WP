/**
 * Error Boundary Component
 *
 * Catches JavaScript errors in child components and displays a fallback UI.
 */

import { Component } from '@wordpress/element';
import { Notice, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * ErrorBoundary Component
 *
 * Wraps child components and catches any errors that occur during rendering.
 */
class ErrorBoundary extends Component {
    constructor( props ) {
        super( props );
        this.state = {
            hasError: false,
            error: null,
            errorInfo: null,
        };
    }

    /**
     * Update state when an error is caught.
     *
     * @param {Error} error The error that was thrown.
     * @return {Object} New state.
     */
    static getDerivedStateFromError( error ) {
        return {
            hasError: true,
            error,
        };
    }

    /**
     * Log error details for debugging.
     *
     * @param {Error}  error     The error that was thrown.
     * @param {Object} errorInfo React error info with component stack.
     */
    componentDidCatch( error, errorInfo ) {
        this.setState( { errorInfo } );

        // Log to console for debugging
        console.error( 'AI Tools Error:', error );
        console.error( 'Component Stack:', errorInfo?.componentStack );
    }

    /**
     * Reset the error state to try again.
     */
    handleRetry = () => {
        this.setState( {
            hasError: false,
            error: null,
            errorInfo: null,
        } );
    };

    render() {
        const { hasError, error } = this.state;
        const { children, fallback, panelTitle } = this.props;

        if ( hasError ) {
            // Use custom fallback if provided
            if ( fallback ) {
                return fallback;
            }

            // Default error UI
            return (
                <div className="aitwp-error-boundary">
                    <Notice status="error" isDismissible={ false }>
                        <p>
                            <strong>
                                { panelTitle
                                    ? sprintf(
                                        /* translators: %s: panel title */
                                        __( '%s encountered an error.', 'ai-tools-for-wp' ),
                                        panelTitle
                                    )
                                    : __( 'Something went wrong.', 'ai-tools-for-wp' )
                                }
                            </strong>
                        </p>
                        { error?.message && (
                            <p className="aitwp-error-message">
                                { error.message }
                            </p>
                        ) }
                    </Notice>
                    <Button
                        variant="secondary"
                        onClick={ this.handleRetry }
                        className="aitwp-retry-button"
                    >
                        { __( 'Try Again', 'ai-tools-for-wp' ) }
                    </Button>
                </div>
            );
        }

        return children;
    }
}

export default ErrorBoundary;
