"""
Example: Frontend Integration for Smart Query Router

This shows how to update your frontend components to use the new smart router endpoint.
The smart router provides automatic intent detection and multi-source querying.
"""

# Example 1: Basic React Component Update

## BEFORE (manual endpoint selection):

```javascript
// Old approach - user had to select which database to query
const [selectedDB, setSelectedDB] = useState('business'); // 'business', 'php', 'js', 'blade'

const handleQuery = async () => {
    const endpoint = `/inference/${selectedDB}`;
    const response = await fetch(`http://localhost:8000${endpoint}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            query: userQuery,
            top_k: 5,
            rerank: true,
            conversation_id: conversationId
        })
    });
    
    const result = await response.json();
    setResults(result.results);
    setLLMResponse(result.llm_response);
};
```

## AFTER (automatic routing):

```javascript
// New approach - smart router automatically determines which DB(s) to query
const handleSmartQuery = async () => {
    const response = await fetch('http://localhost:8000/inference/smart', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            query: userQuery,
            top_k: 5,
            confidence_threshold: 0.5,
            conversation_id: conversationId
        })
    });
    
    const result = await response.json();
    
    // New metadata available
    setResults(result.results);
    setLLMResponse(result.llm_response);
    setSourcesQueried(result.sources_queried);  // ['blade_templates', 'php_code']
    setRoutingDecision(result.routing_decision); // Full classification details
};
```

# Example 2: Enhanced UI with Routing Metadata

```javascript
import React, { useState } from 'react';

function SmartQueryComponent() {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState([]);
    const [llmResponse, setLLMResponse] = useState('');
    const [routingInfo, setRoutingInfo] = useState(null);
    const [loading, setLoading] = useState(false);

    const handleSmartQuery = async () => {
        setLoading(true);
        
        try {
            const response = await fetch('http://localhost:8000/inference/smart', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    query: query,
                    top_k: 5,
                    confidence_threshold: 0.5
                })
            });
            
            const result = await response.json();
            
            setResults(result.results);
            setLLMResponse(result.llm_response);
            setRoutingInfo({
                sources: result.sources_queried,
                confidence: result.routing_decision.confidence,
                reasoning: result.routing_decision.reasoning,
                queryType: result.routing_decision.query_type
            });
        } catch (error) {
            console.error('Query failed:', error);
        } finally {
            setLoading(false);
        }
    };

    const getSourceIcon = (source) => {
        const icons = {
            'business_docs': '📚',
            'php_code': '🐘',
            'js_code': '⚛️',
            'blade_templates': '🗡️'
        };
        return icons[source] || '📄';
    };

    const getSourceLabel = (source) => {
        const labels = {
            'business_docs': 'Business Docs',
            'php_code': 'PHP Code',
            'js_code': 'JavaScript',
            'blade_templates': 'Blade Templates'
        };
        return labels[source] || source;
    };

    return (
        <div className="smart-query-container">
            {/* Query Input */}
            <div className="query-input">
                <textarea
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    placeholder="Ask anything about your banking application..."
                    rows={3}
                />
                <button onClick={handleSmartQuery} disabled={loading}>
                    {loading ? 'Searching...' : '🔍 Smart Search'}
                </button>
            </div>

            {/* Routing Information (NEW!) */}
            {routingInfo && (
                <div className="routing-info">
                    <h4>🎯 Query Routing</h4>
                    <div className="sources-queried">
                        {routingInfo.sources.map(source => (
                            <span key={source} className="source-badge">
                                {getSourceIcon(source)} {getSourceLabel(source)}
                            </span>
                        ))}
                    </div>
                    <div className="routing-details">
                        <p>
                            <strong>Confidence:</strong> 
                            <span className={`confidence ${routingInfo.confidence > 0.8 ? 'high' : 'medium'}`}>
                                {(routingInfo.confidence * 100).toFixed(0)}%
                            </span>
                        </p>
                        <p><strong>Type:</strong> {routingInfo.queryType}</p>
                        <p className="reasoning">{routingInfo.reasoning}</p>
                    </div>
                </div>
            )}

            {/* LLM Response */}
            {llmResponse && (
                <div className="llm-response">
                    <h4>💬 Answer</h4>
                    <div className="response-content">
                        {llmResponse}
                    </div>
                </div>
            )}

            {/* Results with Source Attribution */}
            {results.length > 0 && (
                <div className="results-list">
                    <h4>📊 Sources ({results.length})</h4>
                    {results.map((result, idx) => (
                        <div key={idx} className="result-item">
                            <div className="result-header">
                                <span className="source-icon">
                                    {getSourceIcon(result.source)}
                                </span>
                                <span className="file-path">
                                    {result.metadata.file_path || result.metadata.page_name}
                                </span>
                                {result.rrf_score && (
                                    <span className="rrf-score">
                                        Score: {result.rrf_score.toFixed(4)}
                                    </span>
                                )}
                            </div>
                            <div className="result-content">
                                {result.content.substring(0, 300)}...
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

export default SmartQueryComponent;
```

# Example 3: CSS Styling for Routing Info

```css
/* Add to your component CSS */

.routing-info {
    background: #f0f7ff;
    border-left: 4px solid #0066cc;
    padding: 15px;
    margin: 20px 0;
    border-radius: 4px;
}

.routing-info h4 {
    margin-top: 0;
    color: #0066cc;
}

.sources-queried {
    display: flex;
    gap: 10px;
    margin: 10px 0;
}

.source-badge {
    background: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 14px;
    border: 1px solid #ddd;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.routing-details {
    font-size: 14px;
    color: #666;
}

.confidence {
    padding: 2px 8px;
    border-radius: 4px;
    margin-left: 8px;
    font-weight: bold;
}

.confidence.high {
    background: #d4edda;
    color: #155724;
}

.confidence.medium {
    background: #fff3cd;
    color: #856404;
}

.reasoning {
    font-style: italic;
    color: #888;
    margin-top: 8px;
}

.result-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.source-icon {
    font-size: 20px;
}

.rrf-score {
    margin-left: auto;
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-family: monospace;
}
```

# Example 4: Backward Compatible Approach

```javascript
// Support both old and new endpoints during migration

const QueryComponent = ({ useSmartRouter = true }) => {
    const handleQuery = async () => {
        if (useSmartRouter) {
            // New smart router
            return await smartQuery();
        } else {
            // Old direct endpoint
            return await directQuery();
        }
    };

    const smartQuery = async () => {
        const response = await fetch('http://localhost:8000/inference/smart', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                query: userQuery,
                top_k: 5,
                confidence_threshold: 0.5
            })
        });
        return await response.json();
    };

    const directQuery = async () => {
        // Old implementation
        const response = await fetch(`http://localhost:8000/inference/${selectedDB}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                query: userQuery,
                top_k: 5,
                rerank: true
            })
        });
        return await response.json();
    };

    // ... rest of component
};
```

# Example 5: Feature Flag for Gradual Rollout

```javascript
// Use feature flag to gradually enable smart router

import { useFeatureFlag } from './featureFlags';

function QueryInterface() {
    const smartRouterEnabled = useFeatureFlag('smart-router');
    
    const handleQuery = async () => {
        const endpoint = smartRouterEnabled 
            ? '/inference/smart'
            : `/inference/${selectedDB}`;
        
        // ... make request
    };

    return (
        <div>
            {smartRouterEnabled && (
                <div className="feature-badge">
                    ✨ Using Smart Router
                </div>
            )}
            {/* ... rest of UI */}
        </div>
    );
}
```

# Example 6: TypeScript Types

```typescript
// Define types for smart router responses

interface RoutingDecision {
    primary_source: 'business_docs' | 'php_code' | 'js_code' | 'blade_templates';
    secondary_sources: string[];
    confidence: number;
    reasoning: string;
    query_type: 'documentation' | 'implementation' | 'debugging' | 'architecture' | 'mixed';
    requires_code: boolean;
}

interface SmartQueryResult {
    id: string;
    content: string;
    metadata: Record<string, any>;
    source: string;
    rrf_score?: number;
    distance?: number;
    original_rank?: number;
}

interface SmartQueryResponse {
    results: SmartQueryResult[];
    llm_response: string | null;
    context_used: string | null;
    routing_decision: RoutingDecision;
    sources_queried: string[];
}

// Usage
const querySmartRouter = async (query: string): Promise<SmartQueryResponse> => {
    const response = await fetch('http://localhost:8000/inference/smart', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            query,
            top_k: 5,
            confidence_threshold: 0.5
        })
    });
    
    return await response.json();
};
```

# Example 7: Analytics Tracking

```javascript
// Track routing decisions for analytics

const trackRoutingDecision = (routingDecision, query) => {
    // Send to analytics
    analytics.track('Smart Router Query', {
        query: query,
        primary_source: routingDecision.primary_source,
        sources_count: routingDecision.secondary_sources.length + 1,
        confidence: routingDecision.confidence,
        query_type: routingDecision.query_type
    });
};

const handleSmartQuery = async () => {
    const result = await fetch('http://localhost:8000/inference/smart', {
        // ... request details
    }).then(r => r.json());
    
    // Track the routing decision
    trackRoutingDecision(result.routing_decision, query);
    
    // ... rest of handling
};
```

# Summary

## Migration Checklist:

1. ✅ Update API endpoint from `/inference/{type}` to `/inference/smart`
2. ✅ Remove manual database selection UI (dropdown/buttons)
3. ✅ Add routing metadata display (sources queried, confidence)
4. ✅ Update response handling to include new fields
5. ✅ Add source attribution badges to results
6. ✅ Consider feature flag for gradual rollout
7. ✅ Add analytics tracking for routing decisions
8. ✅ Update TypeScript types if using TypeScript
9. ✅ Style routing info UI
10. ✅ Test with various query types

## Benefits for Users:

- ✅ No more guessing which database to search
- ✅ Automatically gets comprehensive answers from multiple sources
- ✅ Sees which sources were used (transparency)
- ✅ Better results for cross-domain questions

## Next Steps:

1. Start with one component (e.g., main search)
2. Add feature flag for safe rollout
3. Collect user feedback
4. Monitor routing decisions
5. Gradually expand to all query interfaces
"""
