# [Code Quality] Global window variable used for cross-component state

## Priority: High

## Description
The AudienceSelector component stores its selected value in `window.aitwpSelectedAudience`, which is then read by other components (CategorizerPanel, RewriterPanel). This is an anti-pattern in React.

## Location
`editor/src/components/AudienceSelector.js:109-111`

```javascript
useEffect( () => {
    window.aitwpSelectedAudience = selectedAudience;
}, [ selectedAudience ] );
```

## Problems
- Not reactive - other components won't re-render when the value changes
- Stale data issues possible
- Violates React's unidirectional data flow
- Makes testing difficult
- Not type-safe

## Recommendation
Use WordPress data stores (`@wordpress/data`) for proper state management:
1. Create a custom store for AI Tools state
2. Use `useSelect` and `useDispatch` hooks in components
3. State changes will properly trigger re-renders

## Labels
`enhancement`, `code-quality`
