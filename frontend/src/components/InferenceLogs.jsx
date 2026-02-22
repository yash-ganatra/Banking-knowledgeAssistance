import { useState, useEffect } from 'react';
import {
  Database, Clock, CheckCircle, XCircle, ChevronDown, ChevronRight,
  Activity, Zap, Filter, RefreshCw, Trash2, Eye, BarChart2
} from 'lucide-react';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

// Badge component for sources
const SourceBadge = ({ source }) => {
  const colors = {
    php_code: 'bg-purple-500/20 text-purple-300 border-purple-500/30',
    js_code: 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30',
    blade_templates: 'bg-green-500/20 text-green-300 border-green-500/30',
    business_docs: 'bg-blue-500/20 text-blue-300 border-blue-500/30',
  };

  return (
    <span className={`px-2 py-0.5 rounded-full text-xs border ${colors[source] || 'bg-gray-500/20 text-gray-300 border-gray-500/30'}`}>
      {source?.replace('_', ' ')}
    </span>
  );
};

// Summary stats card
const StatCard = ({ icon: Icon, label, value, subtext, color = 'blue' }) => {
  const colorClasses = {
    blue: 'bg-blue-500/10 text-blue-400 border-blue-500/30',
    green: 'bg-green-500/10 text-green-400 border-green-500/30',
    red: 'bg-red-500/10 text-red-400 border-red-500/30',
    yellow: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
  };

  return (
    <div className={`rounded-lg border p-4 ${colorClasses[color]}`}>
      <div className="flex items-center gap-2 mb-2">
        <Icon className="w-4 h-4" />
        <span className="text-sm opacity-70">{label}</span>
      </div>
      <div className="text-2xl font-bold">{value}</div>
      {subtext && <div className="text-xs opacity-50 mt-1">{subtext}</div>}
    </div>
  );
};

// Pipeline visualization component
const PipelineStage = ({ stage, isLast }) => {
  const getStageIcon = (stageName) => {
    switch (stageName) {
      case 'preprocessing': return '📝';
      case 'routing': return '🔀';
      case 'retrieval': return '🔍';
      case 'graph_enhancement': return '🕸️';
      case 'reranking': return '⚡';
      case 'llm_generation': return '🤖';
      default: return '▸';
    }
  };

  return (
    <div className="flex items-start">
      <div className="flex flex-col items-center">
        <div className="w-10 h-10 rounded-full bg-blue-500/20 border border-blue-500/30 flex items-center justify-center text-lg">
          {getStageIcon(stage.stage)}
        </div>
        {!isLast && <div className="w-0.5 h-8 bg-blue-500/30 mt-1" />}
      </div>
      <div className="ml-4 flex-1">
        <div className="font-medium text-gray-200">{stage.name}</div>
        {stage.time_ms && (
          <div className="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
            <Clock className="w-3 h-3" /> {stage.time_ms.toFixed(0)}ms
          </div>
        )}
        {stage.sources && (
          <div className="flex flex-wrap gap-1 mt-1">
            {stage.sources.map(src => <SourceBadge key={src} source={src} />)}
          </div>
        )}
        {stage.total_chunks !== undefined && (
          <div className="text-xs text-gray-400 mt-1">
            Retrieved: {stage.total_chunks} chunks
            {stage.hybrid_search_used && (
              <span className="ml-2 text-green-400">
                (Hybrid: {stage.dense_results} dense + {stage.sparse_results} sparse,
                {stage.found_by_both} overlap)
              </span>
            )}
          </div>
        )}
        {stage.chunks_before !== undefined && (
          <div className="text-xs text-gray-400 mt-1">
            Filtered: {stage.chunks_before} → {stage.chunks_after} chunks
          </div>
        )}
        {stage.graph_context && (
          <div className="mt-3 space-y-3">
            {/* Header */}
            <div className="flex items-center justify-between">
              <div className="text-xs font-semibold text-purple-300 flex items-center gap-2">
                <span>🕸️ Graph Context</span>
                {stage.graph_context.cypher_analytics_text ? (
                  <span className="px-1.5 py-0.5 rounded bg-green-500/20 text-green-300 text-[10px] border border-green-500/30">
                    ✨ TextToCypher
                  </span>
                ) : (
                  <span className="px-1.5 py-0.5 rounded bg-purple-500/20 text-purple-300 text-[10px] border border-purple-500/30">
                    {stage.graph_context.related_entities?.length || 0} entities
                  </span>
                )}
                {stage.graph_context.call_graph?.length > 0 && (
                  <span className="px-1.5 py-0.5 rounded bg-cyan-500/20 text-cyan-300 text-[10px] border border-cyan-500/30">
                    {stage.graph_context.call_graph.length} in call graph
                  </span>
                )}
                {stage.graph_context.relationships?.length > 0 && (
                  <span className="px-1.5 py-0.5 rounded bg-orange-500/20 text-orange-300 text-[10px] border border-orange-500/30">
                    {stage.graph_context.relationships.length} relationships
                  </span>
                )}
              </div>
              <div className="flex items-center gap-3 text-[10px] text-gray-500">
                {stage.graph_context.traversal_depth !== undefined && (
                  <span>Depth: {stage.graph_context.traversal_depth}</span>
                )}
                {stage.graph_context.query_time_ms !== undefined && (
                  <span>⏱ {stage.graph_context.query_time_ms.toFixed(0)}ms</span>
                )}
              </div>
            </div>

            {/* Cypher Queries */}
            {stage.graph_context.cypher_queries?.length > 0 && (
              <div className="bg-gray-900/60 rounded-lg p-3 border border-yellow-500/20">
                <div className="text-[10px] text-yellow-400 uppercase tracking-wider font-semibold mb-2">
                  Cypher Queries Executed ({stage.graph_context.cypher_queries.length})
                </div>
                <div className="space-y-2">
                  {stage.graph_context.cypher_queries.map((query, idx) => (
                    <pre
                      key={idx}
                      className="text-xs text-yellow-200/90 font-mono bg-gray-950/80 p-2.5 rounded border border-yellow-500/10 overflow-x-auto whitespace-pre-wrap"
                    >
                      {query}
                    </pre>
                  ))}
                </div>
              </div>
            )}

            {/* TextToCypher Analytics Results */}
            {stage.graph_context.cypher_analytics_text && (
              <div className="bg-gray-900/60 rounded-lg p-3 border border-green-500/20">
                <div className="text-[10px] text-green-400 uppercase tracking-wider font-semibold mb-2">
                  Graph Analytics Results
                </div>
                <pre className="text-xs text-green-200/90 font-mono bg-gray-950/80 p-2.5 rounded border border-green-500/10 overflow-x-auto whitespace-pre-wrap">
                  {stage.graph_context.cypher_analytics_text}
                </pre>
              </div>
            )}

            {/* Route Flow (if present) */}
            {stage.graph_context.route_flow && (
              <div className="bg-gray-900/60 rounded-lg p-3 border border-indigo-500/20">
                <div className="text-[10px] text-indigo-400 uppercase tracking-wider font-semibold mb-2">Request Flow</div>
                <div className="flex items-center flex-wrap gap-1">
                  {stage.graph_context.route_flow.route && (
                    <>
                      <div className="flex items-center gap-1 px-2 py-1 rounded bg-indigo-500/15 border border-indigo-500/30">
                        <span className="text-indigo-300 text-[10px] font-semibold">ROUTE</span>
                        <span className="text-indigo-200 text-xs font-mono">
                          {stage.graph_context.route_flow.route.method || 'GET'} {stage.graph_context.route_flow.route.uri}
                        </span>
                      </div>
                      <span className="text-gray-500 text-xs">→</span>
                    </>
                  )}
                  {stage.graph_context.route_flow.controller && (
                    <>
                      <div className="flex items-center gap-1 px-2 py-1 rounded bg-blue-500/15 border border-blue-500/30">
                        <span className="text-blue-300 text-[10px] font-semibold">CTRL</span>
                        <span className="text-blue-200 text-xs font-mono">{stage.graph_context.route_flow.controller.name}</span>
                      </div>
                      <span className="text-gray-500 text-xs">→</span>
                    </>
                  )}
                  {stage.graph_context.route_flow.action && (
                    <>
                      <div className="flex items-center gap-1 px-2 py-1 rounded bg-green-500/15 border border-green-500/30">
                        <span className="text-green-300 text-[10px] font-semibold">ACTION</span>
                        <span className="text-green-200 text-xs font-mono">{stage.graph_context.route_flow.action.name}()</span>
                      </div>
                    </>
                  )}
                  {stage.graph_context.route_flow.models?.length > 0 && (
                    <>
                      <span className="text-gray-500 text-xs">→</span>
                      {stage.graph_context.route_flow.models.map((m, i) => (
                        <div key={i} className="flex items-center gap-1 px-2 py-1 rounded bg-amber-500/15 border border-amber-500/30">
                          <span className="text-amber-300 text-[10px] font-semibold">MODEL</span>
                          <span className="text-amber-200 text-xs font-mono">{m.name}</span>
                        </div>
                      ))}
                    </>
                  )}
                  {stage.graph_context.route_flow.views?.length > 0 && (
                    <>
                      <span className="text-gray-500 text-xs">→</span>
                      {stage.graph_context.route_flow.views.map((v, i) => (
                        <div key={i} className="flex items-center gap-1 px-2 py-1 rounded bg-emerald-500/15 border border-emerald-500/30">
                          <span className="text-emerald-300 text-[10px] font-semibold">VIEW</span>
                          <span className="text-emerald-200 text-xs font-mono">{v.name}</span>
                        </div>
                      ))}
                    </>
                  )}
                </div>
              </div>
            )}

            {/* Call Graph */}
            {stage.graph_context.call_graph?.length > 0 && (
              <div className="bg-gray-900/60 rounded-lg p-3 border border-cyan-500/20">
                <div className="text-[10px] text-cyan-400 uppercase tracking-wider font-semibold mb-2">Call Graph (Functions & Dependencies)</div>
                <div className="space-y-1.5">
                  {stage.graph_context.call_graph.map((item, idx) => {
                    const relType = item.rel_type || item.relationship || '';
                    const relLabel = relType.replace(/_/g, ' ').toLowerCase();
                    const typeColor = {
                      'Action': 'text-green-300 bg-green-500/15 border-green-500/30',
                      'Model': 'text-amber-300 bg-amber-500/15 border-amber-500/30',
                      'BladeView': 'text-emerald-300 bg-emerald-500/15 border-emerald-500/30',
                      'DBTable': 'text-red-300 bg-red-500/15 border-red-500/30',
                      'Controller': 'text-blue-300 bg-blue-500/15 border-blue-500/30',
                      'Route': 'text-indigo-300 bg-indigo-500/15 border-indigo-500/30',
                    }[item.type] || 'text-purple-300 bg-purple-500/15 border-purple-500/30';
                    return (
                      <div key={idx} className="flex items-center gap-2 text-xs group">
                        <span className={`px-1.5 py-0.5 rounded border text-[10px] font-semibold uppercase ${typeColor}`}>
                          {item.type || 'Entity'}
                        </span>
                        <span className="text-gray-200 font-mono font-medium">{item.name}</span>
                        {item.file && (
                          <span className="text-gray-600 font-mono text-[10px] truncate max-w-[200px] opacity-0 group-hover:opacity-100 transition-opacity">
                            {item.file}
                          </span>
                        )}
                        {relLabel && (
                          <span className="ml-auto px-1.5 py-0.5 rounded bg-gray-700/50 text-gray-400 text-[10px] italic">
                            {relLabel}
                          </span>
                        )}
                      </div>
                    );
                  })}
                </div>
              </div>
            )}

            {/* Neo4j Relationships (the key detail users want) */}
            {stage.graph_context.relationships?.length > 0 && (
              <div className="bg-gray-900/60 rounded-lg p-3 border border-orange-500/20">
                <div className="text-[10px] text-orange-400 uppercase tracking-wider font-semibold mb-2">
                  Neo4j Relationships Retrieved ({stage.graph_context.relationships.length})
                </div>
                <div className="space-y-1">
                  {stage.graph_context.relationships.map((rel, idx) => {
                    const srcColor = {
                      'Action': 'text-green-300 bg-green-500/15 border-green-500/30',
                      'Controller': 'text-blue-300 bg-blue-500/15 border-blue-500/30',
                      'Model': 'text-amber-300 bg-amber-500/15 border-amber-500/30',
                      'BladeView': 'text-emerald-300 bg-emerald-500/15 border-emerald-500/30',
                      'UIElement': 'text-pink-300 bg-pink-500/15 border-pink-500/30',
                      'Route': 'text-indigo-300 bg-indigo-500/15 border-indigo-500/30',
                    }[rel.source_type] || 'text-gray-300 bg-gray-500/15 border-gray-500/30';
                    const tgtColor = {
                      'Action': 'text-green-300 bg-green-500/15 border-green-500/30',
                      'Controller': 'text-blue-300 bg-blue-500/15 border-blue-500/30',
                      'Model': 'text-amber-300 bg-amber-500/15 border-amber-500/30',
                      'BladeView': 'text-emerald-300 bg-emerald-500/15 border-emerald-500/30',
                      'UIElement': 'text-pink-300 bg-pink-500/15 border-pink-500/30',
                      'DBTable': 'text-red-300 bg-red-500/15 border-red-500/30',
                      'TargetAction': 'text-teal-300 bg-teal-500/15 border-teal-500/30',
                      'Route': 'text-indigo-300 bg-indigo-500/15 border-indigo-500/30',
                    }[rel.target_type] || 'text-gray-300 bg-gray-500/15 border-gray-500/30';
                    const relLabel = (rel.relationship || '').replace(/_/g, ' ');
                    return (
                      <div key={idx} className="flex items-center gap-1.5 text-xs">
                        <span className={`px-1.5 py-0.5 rounded border font-mono text-[11px] ${srcColor}`}>
                          {rel.source}
                        </span>
                        <span className="px-1.5 py-0.5 rounded bg-orange-500/10 text-orange-300 text-[9px] font-semibold border border-orange-500/20 whitespace-nowrap">
                          {relLabel}
                        </span>
                        <span className="text-gray-500">→</span>
                        <span className={`px-1.5 py-0.5 rounded border font-mono text-[11px] ${tgtColor}`}>
                          {rel.target}
                        </span>
                      </div>
                    );
                  })}
                </div>
              </div>
            )}

            {/* Entities Grouped by Type */}
            {stage.graph_context.related_entities?.length > 0 && (() => {
              const grouped = {};
              stage.graph_context.related_entities.forEach(e => {
                const t = e.type || e.labels?.[0] || 'Unknown';
                if (!grouped[t]) grouped[t] = [];
                grouped[t].push(e);
              });
              const typeStyles = {
                'Action': { label: 'text-green-400', badge: 'text-green-300 bg-green-500/10 border-green-500/20', icon: '⚡' },
                'Controller': { label: 'text-blue-400', badge: 'text-blue-300 bg-blue-500/10 border-blue-500/20', icon: '🎮' },
                'Model': { label: 'text-amber-400', badge: 'text-amber-300 bg-amber-500/10 border-amber-500/20', icon: '📦' },
                'BladeView': { label: 'text-emerald-400', badge: 'text-emerald-300 bg-emerald-500/10 border-emerald-500/20', icon: '🖼️' },
                'UIElement': { label: 'text-pink-400', badge: 'text-pink-300 bg-pink-500/10 border-pink-500/20', icon: '🔘' },
                'DBTable': { label: 'text-red-400', badge: 'text-red-300 bg-red-500/10 border-red-500/20', icon: '🗄️' },
                'Route': { label: 'text-indigo-400', badge: 'text-indigo-300 bg-indigo-500/10 border-indigo-500/20', icon: '🛤️' },
                'JSFunction': { label: 'text-yellow-400', badge: 'text-yellow-300 bg-yellow-500/10 border-yellow-500/20', icon: '📜' },
                'TargetAction': { label: 'text-teal-400', badge: 'text-teal-300 bg-teal-500/10 border-teal-500/20', icon: '🎯' },
              };
              return (
                <div className="bg-gray-900/60 rounded-lg p-3 border border-purple-500/20">
                  <div className="text-[10px] text-purple-400 uppercase tracking-wider font-semibold mb-2">Discovered Entities by Type</div>
                  <div className="space-y-2">
                    {Object.entries(grouped).map(([type, entities]) => {
                      const style = typeStyles[type] || { label: 'text-gray-400', badge: 'text-gray-300 bg-gray-500/10 border-gray-500/20', icon: '•' };
                      return (
                        <div key={type}>
                          <div className="flex items-center gap-1.5 mb-1">
                            <span className="text-sm">{style.icon}</span>
                            <span className={`${style.label} text-[10px] font-semibold uppercase tracking-wider`}>{type}</span>
                            <span className="text-gray-600 text-[10px]">({entities.length})</span>
                          </div>
                          <div className="flex flex-wrap gap-1 ml-5">
                            {entities.map((entity, i) => (
                              <div
                                key={i}
                                className={`group relative flex items-center gap-1 px-2 py-0.5 rounded border cursor-default ${style.badge}`}
                                title={entity.file ? `File: ${entity.file}` : ''}
                              >
                                <span className="text-xs font-mono">{entity.name}</span>
                                {entity.file && (
                                  <span className="text-gray-600 text-[9px] font-mono hidden group-hover:inline truncate max-w-[150px]">
                                    ({entity.file.split('/').pop()})
                                  </span>
                                )}
                              </div>
                            ))}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              );
            })()}
          </div>
        )}
      </div>
    </div >
  );
};

// Log row component
const LogRow = ({ log, onViewDetails }) => {
  const [expanded, setExpanded] = useState(false);

  return (
    <div className="border border-gray-700 rounded-lg bg-gray-800/50 overflow-hidden">
      <div
        className="p-4 cursor-pointer hover:bg-gray-700/50 transition-colors"
        onClick={() => setExpanded(!expanded)}
      >
        <div className="flex items-start justify-between">
          <div className="flex items-start gap-3">
            <button className="mt-1 text-gray-400 hover:text-white">
              {expanded ? <ChevronDown className="w-4 h-4" /> : <ChevronRight className="w-4 h-4" />}
            </button>
            <div className="flex-1">
              <div className="flex items-center gap-2 mb-1">
                {log.success ? (
                  <CheckCircle className="w-4 h-4 text-green-400" />
                ) : (
                  <XCircle className="w-4 h-4 text-red-400" />
                )}
                <span className="text-gray-200 line-clamp-1">{log.query}</span>
                {log.graph_used && (
                  <span className="px-1.5 py-0.5 rounded bg-purple-500/20 text-purple-300 text-[10px] border border-purple-500/30 flex-shrink-0" title="Graph Enhancement Used">
                    🕸️ Graph
                  </span>
                )}
              </div>
              <div className="flex flex-wrap items-center gap-2 text-xs text-gray-400">
                <span className="flex items-center gap-1">
                  <Clock className="w-3 h-3" />
                  {log.total_time_ms?.toFixed(0) || '?'}ms
                </span>
                <span>•</span>
                <span className="flex items-center gap-1">
                  <Database className="w-3 h-3" />
                  {log.chunks_after_reranking || 0} chunks
                </span>
                <span>•</span>
                <span>{new Date(log.created_at).toLocaleString()}</span>
              </div>
            </div>
          </div>
          <div className="flex items-center gap-2">
            {log.sources_queried?.map(src => (
              <SourceBadge key={src} source={src} />
            ))}
            <button
              onClick={(e) => {
                e.stopPropagation();
                onViewDetails(log.id);
              }}
              className="p-1.5 rounded-lg bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 transition-colors"
              title="View details"
            >
              <Eye className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      {expanded && (
        <div className="border-t border-gray-700 p-4 bg-gray-900/50">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
              <div className="text-gray-500 text-xs mb-1">Query Type</div>
              <div className="text-gray-200">{log.query_type || 'N/A'}</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">Routing Time</div>
              <div className="text-gray-200">{log.routing_time_ms?.toFixed(0) || '?'}ms</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">Retrieval Time</div>
              <div className="text-gray-200">{log.retrieval_time_ms?.toFixed(0) || '?'}ms</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">Reranking Time</div>
              <div className="text-gray-200">{log.reranking_time_ms?.toFixed(0) || '?'}ms</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">Primary Source</div>
              <div className="text-gray-200">{log.primary_source || 'N/A'}</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">Total Retrieved</div>
              <div className="text-gray-200">{log.total_chunks_retrieved || 0} chunks</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">After Filtering</div>
              <div className="text-gray-200">{log.chunks_after_filtering || 0} chunks</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">Hybrid Search</div>
              <div className={log.hybrid_search_used ? "text-green-400" : "text-gray-500"}>
                {log.hybrid_search_used ? 'Enabled' : 'Disabled'}
              </div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">Graph Enhancement</div>
              <div className={log.graph_used ? "text-purple-400" : "text-gray-500"}>
                {log.graph_used ? '🕸️ Active' : 'Not Used'}
              </div>
            </div>
          </div>
          {/* Query Expansion Info */}
          {log.query_expansion_applied !== null && log.query_expansion_applied !== undefined && (
            <div className="mt-4 p-3 bg-gray-800/50 rounded-lg border border-gray-700">
              <div className="flex items-center gap-2 mb-2">
                <span className="text-gray-400 text-xs font-medium">Query Expansion (BM25)</span>
                <span className={`px-2 py-0.5 rounded text-xs ${log.query_expansion_applied ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'}`}>
                  {log.query_expansion_applied ? 'Applied' : 'Skipped'}
                </span>
              </div>
              {log.query_expansion_applied && log.expanded_query && (
                <div className="mt-2">
                  <div className="text-gray-500 text-xs mb-1">Expanded Query</div>
                  <div className="text-cyan-300 text-sm bg-gray-900 p-2 rounded font-mono">
                    {log.expanded_query}
                  </div>
                </div>
              )}
              {log.expansion_reason && (
                <div className="mt-2 text-xs text-gray-500">
                  Reason: {log.expansion_reason}
                </div>
              )}
            </div>
          )}
          {log.routing_reasoning && (
            <div className="mt-4">
              <div className="text-gray-500 text-xs mb-1">Routing Reasoning</div>
              <div className="text-gray-300 text-sm bg-gray-800 p-2 rounded">{log.routing_reasoning}</div>
            </div>
          )}
          {log.error_message && (
            <div className="mt-4">
              <div className="text-red-400 text-xs mb-1">Error</div>
              <div className="text-red-300 text-sm bg-red-900/20 p-2 rounded border border-red-500/30">
                {log.error_message}
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

// Chunk card component for before/after display
const ChunkCard = ({ chunk, type }) => {
  const [expanded, setExpanded] = useState(false);
  const isBefore = type === 'before';
  const isAfter = type === 'after';
  const displayRank = isBefore ? (chunk.rrf_rank || '?') : (chunk.final_rank || '?');
  const borderColor = isBefore ? 'border-amber-500/30' : isAfter ? 'border-emerald-500/30' : 'border-gray-700';
  const rankBg = isBefore ? 'bg-amber-500/20 text-amber-400' : 'bg-emerald-500/20 text-emerald-400';

  const displayName = chunk.file_path
    ? chunk.file_path.split(/[\\\/]/).pop()
    : (chunk.file_name || chunk.chunk_id?.replace('_before', '') || 'Unknown');

  return (
    <div className={`bg-gray-900/50 rounded-lg border ${borderColor} overflow-hidden`}>
      <div
        className="p-3 cursor-pointer hover:bg-gray-800/50 transition-colors"
        onClick={() => setExpanded(!expanded)}
      >
        <div className="flex items-center justify-between gap-2">
          <div className="flex items-center gap-2 min-w-0">
            <span className={`flex-shrink-0 w-6 h-6 rounded text-xs font-bold flex items-center justify-center ${rankBg}`}>
              {displayRank}
            </span>
            <SourceBadge source={chunk.source} />
            <span className="font-mono text-sm text-blue-300 truncate">{displayName}</span>
          </div>
          <div className="flex items-center gap-2 text-xs">
            {chunk.rrf_score !== null && chunk.rrf_score !== undefined && (
              <span className="px-2 py-0.5 bg-gray-700 text-gray-300 rounded">
                RRF: {chunk.rrf_score.toFixed(3)}
              </span>
            )}
            {isAfter && chunk.cross_encoder_score !== null && chunk.cross_encoder_score !== undefined && (
              <span className="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded font-medium">
                CE: {chunk.cross_encoder_score?.toFixed(2) || 'N/A'}
              </span>
            )}
            {expanded ? <ChevronDown className="w-4 h-4 text-gray-500" /> : <ChevronRight className="w-4 h-4 text-gray-500" />}
          </div>
        </div>
        {chunk.class_name && (
          <div className="text-xs text-gray-500 mt-1 ml-8">
            {chunk.class_name}{chunk.method_name && ` → ${chunk.method_name}`}
          </div>
        )}
      </div>
      {expanded && (
        <div className="border-t border-gray-700 p-3 bg-gray-900/80">
          {chunk.file_path && (
            <div className="text-xs text-gray-500 mb-2 font-mono">📁 {chunk.file_path}</div>
          )}
          {chunk.content_preview && (
            <pre className="text-xs text-gray-300 font-mono bg-gray-800 p-2 rounded overflow-x-auto whitespace-pre-wrap max-h-40 overflow-y-auto mb-2">
              {chunk.content_preview}
            </pre>
          )}
          <div className="flex flex-wrap gap-2 text-xs">
            {chunk.rrf_rank && (
              <span className="px-2 py-1 bg-amber-500/10 text-amber-300 rounded border border-amber-500/20">
                RRF Rank: #{chunk.rrf_rank}
              </span>
            )}
            {chunk.final_rank && (
              <span className="px-2 py-1 bg-emerald-500/10 text-emerald-300 rounded border border-emerald-500/20">
                Final Rank: #{chunk.final_rank}
              </span>
            )}
            {chunk.initial_distance !== null && chunk.initial_distance !== undefined && (
              <span className="px-2 py-1 bg-gray-700 text-gray-300 rounded">
                Distance: {chunk.initial_distance.toFixed(4)}
              </span>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

// Detail modal for viewing full pipeline
const DetailModal = ({ logId, onClose }) => {
  const [pipelineData, setPipelineData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('pipeline');

  useEffect(() => {
    const fetchDetails = async () => {
      try {
        const response = await fetch(`${API_BASE_URL}/inference-logs/${logId}/pipeline`);
        if (response.ok) {
          const data = await response.json();
          setPipelineData(data);
        }
      } catch (error) {
        console.error('Failed to fetch pipeline details:', error);
      } finally {
        setLoading(false);
      }
    };
    fetchDetails();
  }, [logId]);

  return (
    <div className="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
      <div className="bg-gray-800 rounded-xl max-w-5xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div className="p-4 border-b border-gray-700 flex items-center justify-between">
          <h2 className="text-xl font-bold text-white">Inference Pipeline Details</h2>
          <button
            onClick={onClose}
            className="p-2 hover:bg-gray-700 rounded-lg transition-colors text-gray-400 hover:text-white"
          >
            ✕
          </button>
        </div>

        <div className="flex-1 overflow-y-auto p-4">
          {loading ? (
            <div className="flex items-center justify-center h-48">
              <div className="w-8 h-8 border-2 border-blue-400 border-t-transparent rounded-full animate-spin" />
            </div>
          ) : pipelineData ? (
            <div className="space-y-6">
              {/* Query info */}
              <div className="bg-gray-900/50 rounded-lg p-4 border border-gray-700">
                <div className="text-sm text-gray-400 mb-1">Query</div>
                <div className="text-white">{pipelineData.query}</div>
                <div className="flex items-center gap-4 mt-2 text-sm text-gray-400">
                  <span className="flex items-center gap-1">
                    <Clock className="w-4 h-4" />
                    {pipelineData.total_time_ms?.toFixed(0)}ms total
                  </span>
                  {pipelineData.success ? (
                    <span className="text-green-400 flex items-center gap-1">
                      <CheckCircle className="w-4 h-4" /> Success
                    </span>
                  ) : (
                    <span className="text-red-400 flex items-center gap-1">
                      <XCircle className="w-4 h-4" /> Failed
                    </span>
                  )}
                </div>
              </div>

              {/* Tabs */}
              <div className="flex gap-2 border-b border-gray-700 pb-3">
                {['pipeline', 'before', 'after', 'comparison'].map(tab => (
                  <button
                    key={tab}
                    onClick={() => setActiveTab(tab)}
                    className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${activeTab === tab
                      ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30'
                      : 'text-gray-400 hover:text-white hover:bg-gray-700'
                      }`}
                  >
                    {tab === 'pipeline' ? 'Pipeline' :
                      tab === 'before' ? `Before Rerank (${pipelineData.rerank_summary?.before_count || 0})` :
                        tab === 'after' ? `After Rerank (${pipelineData.rerank_summary?.after_count || 0})` :
                          'Comparison'}
                  </button>
                ))}
              </div>

              {/* Rerank Summary Banner */}
              {pipelineData.rerank_summary && (
                <div className="p-3 bg-amber-500/10 border border-amber-500/20 rounded-lg flex items-center gap-3 text-sm">
                  <Zap className="w-5 h-5 text-amber-400" />
                  <span className="text-amber-300">
                    Cross-encoder filtered <span className="font-bold">{pipelineData.rerank_summary.before_count}</span> chunks
                    down to <span className="font-bold text-emerald-400">{pipelineData.rerank_summary.after_count}</span>
                    {pipelineData.rerank_summary.filtered_out > 0 && (
                      <span className="text-red-400"> ({pipelineData.rerank_summary.filtered_out} removed)</span>
                    )}
                  </span>
                </div>
              )}

              {/* Tab Content */}
              {activeTab === 'pipeline' ? (
                <div>
                  <h3 className="text-lg font-semibold text-white mb-4">Pipeline Stages</h3>
                  <div className="space-y-2">
                    {pipelineData.stages?.map((stage, idx) => (
                      <PipelineStage
                        key={idx}
                        stage={stage}
                        isLast={idx === pipelineData.stages.length - 1}
                      />
                    ))}
                  </div>
                </div>
              ) : activeTab === 'before' ? (
                <div>
                  <div className="text-gray-400 text-sm mb-4">
                    Chunks after RRF fusion, <span className="text-amber-400 font-medium">before cross-encoder reranking</span>
                  </div>
                  <div className="space-y-2">
                    {pipelineData.chunks_before_rerank?.length > 0 ? (
                      pipelineData.chunks_before_rerank.map((chunk, idx) => (
                        <ChunkCard key={idx} chunk={chunk} type="before" />
                      ))
                    ) : (
                      <div className="text-center py-8 text-gray-500">No chunks recorded before reranking</div>
                    )}
                  </div>
                </div>
              ) : activeTab === 'after' ? (
                <div>
                  <div className="text-gray-400 text-sm mb-4">
                    Final chunks <span className="text-emerald-400 font-medium">after cross-encoder reranking</span> (used in LLM context)
                  </div>
                  <div className="space-y-2">
                    {pipelineData.chunks_after_rerank?.length > 0 ? (
                      pipelineData.chunks_after_rerank.map((chunk, idx) => (
                        <ChunkCard key={idx} chunk={chunk} type="after" />
                      ))
                    ) : (
                      <div className="text-center py-8 text-gray-500">No chunks after reranking</div>
                    )}
                  </div>
                </div>
              ) : (
                /* Comparison View */
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  {/* Before Column */}
                  <div>
                    <div className="flex items-center gap-2 mb-4 pb-2 border-b border-gray-700">
                      <div className="w-3 h-3 rounded-full bg-amber-500"></div>
                      <h3 className="font-semibold text-amber-400">Before Reranking</h3>
                      <span className="text-gray-500 text-sm">({pipelineData.chunks_before_rerank?.length || 0})</span>
                    </div>
                    <div className="space-y-2 max-h-[400px] overflow-y-auto pr-2">
                      {pipelineData.chunks_before_rerank?.map((chunk, idx) => (
                        <ChunkCard key={idx} chunk={chunk} type="before" />
                      ))}
                    </div>
                  </div>

                  {/* After Column */}
                  <div>
                    <div className="flex items-center gap-2 mb-4 pb-2 border-b border-gray-700">
                      <div className="w-3 h-3 rounded-full bg-emerald-500"></div>
                      <h3 className="font-semibold text-emerald-400">After Reranking</h3>
                      <span className="text-gray-500 text-sm">({pipelineData.chunks_after_rerank?.length || 0})</span>
                    </div>
                    <div className="space-y-2 max-h-[400px] overflow-y-auto pr-2">
                      {pipelineData.chunks_after_rerank?.map((chunk, idx) => (
                        <ChunkCard key={idx} chunk={chunk} type="after" />
                      ))}
                    </div>
                  </div>
                </div>
              )}
            </div>
          ) : (
            <div className="text-center text-gray-400 py-8">
              Failed to load pipeline details
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

// Main component
export default function InferenceLogs() {
  const [logs, setLogs] = useState([]);
  const [summary, setSummary] = useState(null);
  const [loading, setLoading] = useState(true);
  const [selectedLogId, setSelectedLogId] = useState(null);
  const [filters, setFilters] = useState({
    hoursAgo: 24,
    successOnly: null,
    source: '',
  });

  const fetchLogs = async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (filters.hoursAgo) params.append('hours_ago', filters.hoursAgo);
      if (filters.successOnly !== null) params.append('success_only', filters.successOnly);
      if (filters.source) params.append('source', filters.source);
      params.append('limit', '100');

      const [logsRes, summaryRes] = await Promise.all([
        fetch(`${API_BASE_URL}/inference-logs/?${params}`),
        fetch(`${API_BASE_URL}/inference-logs/summary?hours_ago=${filters.hoursAgo || 24}`)
      ]);

      if (logsRes.ok) setLogs(await logsRes.json());
      if (summaryRes.ok) setSummary(await summaryRes.json());
    } catch (error) {
      console.error('Failed to fetch logs:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLogs();
  }, [filters]);

  return (
    <div className="min-h-full bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 p-4 md:p-8">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1 className="text-2xl font-bold text-white flex items-center gap-2">
              <Activity className="w-6 h-6 text-blue-400" />
              Inference Logs
            </h1>
            <p className="text-gray-400 text-sm mt-1">
              Track and analyze RAG pipeline performance
            </p>
          </div>
          <button
            onClick={fetchLogs}
            disabled={loading}
            className="flex items-center gap-2 px-4 py-2 bg-blue-500/20 hover:bg-blue-500/30 border border-blue-500/30 rounded-lg text-blue-400 transition-colors disabled:opacity-50"
          >
            <RefreshCw className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} />
            Refresh
          </button>
        </div>

        {/* Summary cards */}
        {summary && (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <StatCard
              icon={BarChart2}
              label="Total Queries"
              value={summary.total_queries}
              subtext={`Last ${filters.hoursAgo || 24}h`}
              color="blue"
            />
            <StatCard
              icon={CheckCircle}
              label="Successful"
              value={summary.successful_queries}
              subtext={`${summary.total_queries > 0 ? ((summary.successful_queries / summary.total_queries) * 100).toFixed(0) : 0}% success rate`}
              color="green"
            />
            <StatCard
              icon={Clock}
              label="Avg Response"
              value={`${summary.avg_response_time_ms?.toFixed(0) || 0}ms`}
              subtext="Average latency"
              color="yellow"
            />
            <StatCard
              icon={Database}
              label="Avg Chunks"
              value={summary.avg_chunks_retrieved?.toFixed(1) || 0}
              subtext="Per query"
              color="blue"
            />
            <StatCard
              icon={Zap}
              label="Avg Input Tokens"
              value={summary.avg_input_tokens?.toFixed(0) || '–'}
              subtext="Prompt tokens"
              color="yellow"
            />
            <StatCard
              icon={Activity}
              label="Avg Output Tokens"
              value={summary.avg_output_tokens?.toFixed(0) || '–'}
              subtext="Completion tokens"
              color="green"
            />
          </div>
        )}

        {/* Filters */}
        <div className="flex flex-wrap gap-4 mb-6">
          <select
            value={filters.hoursAgo || ''}
            onChange={(e) => setFilters({ ...filters, hoursAgo: e.target.value ? parseInt(e.target.value) : null })}
            className="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-gray-200 text-sm focus:border-blue-500 focus:outline-none"
          >
            <option value="1">Last hour</option>
            <option value="6">Last 6 hours</option>
            <option value="24">Last 24 hours</option>
            <option value="48">Last 48 hours</option>
            <option value="168">Last week</option>
          </select>

          <select
            value={filters.successOnly === null ? '' : filters.successOnly.toString()}
            onChange={(e) => setFilters({ ...filters, successOnly: e.target.value === '' ? null : e.target.value === 'true' })}
            className="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-gray-200 text-sm focus:border-blue-500 focus:outline-none"
          >
            <option value="">All results</option>
            <option value="true">Successful only</option>
            <option value="false">Failed only</option>
          </select>

          <select
            value={filters.source}
            onChange={(e) => setFilters({ ...filters, source: e.target.value })}
            className="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-gray-200 text-sm focus:border-blue-500 focus:outline-none"
          >
            <option value="">All sources</option>
            <option value="php_code">PHP Code</option>
            <option value="js_code">JS Code</option>
            <option value="blade_templates">Blade Templates</option>
            <option value="business_docs">Business Docs</option>
          </select>
        </div>

        {/* Logs list */}
        <div className="space-y-3">
          {loading ? (
            <div className="flex items-center justify-center h-48">
              <div className="w-8 h-8 border-2 border-blue-400 border-t-transparent rounded-full animate-spin" />
            </div>
          ) : logs.length === 0 ? (
            <div className="text-center py-12 text-gray-400">
              <Database className="w-12 h-12 mx-auto mb-4 opacity-50" />
              <p>No inference logs found for the selected filters.</p>
            </div>
          ) : (
            logs.map(log => (
              <LogRow
                key={log.id}
                log={log}
                onViewDetails={setSelectedLogId}
              />
            ))
          )}
        </div>
      </div>

      {/* Detail modal */}
      {selectedLogId && (
        <DetailModal
          logId={selectedLogId}
          onClose={() => setSelectedLogId(null)}
        />
      )}
    </div>
  );
}
