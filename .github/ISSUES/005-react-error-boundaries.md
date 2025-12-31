# [Reliability] Missing React error boundaries

## Priority: High

## Description
The React components have no error boundaries. If an API call throws an unhandled error or a component crashes, the entire AI Tools sidebar will fail without graceful recovery.

## Location
All React components in `editor/src/components/`

## Problem
- Unhandled errors crash the entire sidebar
- No user feedback when components fail
- Poor user experience
- Difficult to debug production issues

## Recommendation
1. Create an ErrorBoundary component
2. Wrap each panel component with the error boundary
3. Display user-friendly error messages with retry options

```javascript
class ErrorBoundary extends Component {
    state = { hasError: false, error: null };

    static getDerivedStateFromError( error ) {
        return { hasError: true, error };
    }

    render() {
        if ( this.state.hasError ) {
            return <Notice status="error">Something went wrong. Please refresh.</Notice>;
        }
        return this.props.children;
    }
}
```

## Labels
`enhancement`, `reliability`
