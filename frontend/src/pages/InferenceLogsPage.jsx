import { useState, useEffect } from 'react';
import { 
  Database, Clock, CheckCircle, XCircle, ChevronDown, ChevronRight, 
  Activity, Zap, Filter, RefreshCw, Trash2, Eye, BarChart2, Search,
  ArrowLeft, X, ExternalLink, FileCode, Code, FileText, Layout
} from 'lucide-react';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

// ============================================================================
// Utility Components
// ============================================================================

const SourceBadge = ({ source }) => {
  const config = {
    php_code: { bg: 'bg-purple-500/20', text: 'text-purple-300', border: 'border-purple-500/30', icon: Code },
    js_code: { bg: 'bg-yellow-500/20', text: 'text-yellow-300', border: 'border-yellow-500/30', icon: FileCode },
    blade_templates: { bg: 'bg-green-500/20', text: 'text-green-300', border: 'border-green-500/30', icon: Layout },
    business_docs: { bg: 'bg-blue-500/20', text: 'text-blue-300', border: 'border-blue-500/30', icon: FileText },
  };
  
  const c = config[source] || { bg: 'bg-gray-500/20', text: 'text-gray-300', border: 'border-gray-500/30', icon: Database };
  const Icon = c.icon;
  
  return (
    <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border ${c.bg} ${c.text} ${c.border}`}>
      <Icon className="w-3 h-3" />
      {source?.replace(/_/g, ' ')}
    </span>
  );
};

const StatCard = ({ icon: Icon, label, value, subtext, color = 'blue' }) => {
  const colorClasses = {
    blue: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    green: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    red: 'bg-red-500/10 text-red-400 border-red-500/20',
    yellow: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    purple: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
  };
  
  return (
    <div className={`rounded-xl border p-5 ${colorClasses[color]} backdrop-blur-sm`}>
      <div className="flex items-center gap-2 mb-3">
        <Icon className="w-5 h-5 opacity-70" />
        <span className="text-sm font-medium opacity-80">{label}</span>
      </div>
      <div className="text-3xl font-bold tracking-tight">{value}</div>
      {subtext && <div className="text-xs opacity-60 mt-2">{subtext}</div>}
    </div>
  );
};

const LoadingSpinner = ({ size = 'md' }) => {
  const sizes = { sm: 'w-4 h-4', md: 'w-8 h-8', lg: 'w-12 h-12' };
  return (
    <div className={`${sizes[size]} border-2 border-blue-400 border-t-transparent rounded-full animate-spin`} />
  );
};

// ============================================================================
// Pipeline Visualization
// ============================================================================

const PipelineStage = ({ stage, isLast, isExpanded, onToggle }) => {
  const getStageConfig = (stageName) => {
    const configs = {
      preprocessing: { icon: '📝', color: 'blue' },
      routing: { icon: '🔀', color: 'purple' },
      retrieval: { icon: '🔍', color: 'green' },
      reranking: { icon: '⚡', color: 'yellow' },
      llm_generation: { icon: '🤖', color: 'pink' },
    };
    return configs[stageName] || { icon: '▸', color: 'gray' };
  };

  const config = getStageConfig(stage.stage);
  
  return (
    <div className="relative">
      <div 
        className="flex items-start cursor-pointer group"
        onClick={onToggle}
      >
        <div className="flex flex-col items-center">
          <div className={`w-12 h-12 rounded-xl bg-${config.color}-500/20 border border-${config.color}-500/30 flex items-center justify-center text-xl group-hover:scale-105 transition-transform`}>
            {config.icon}
          </div>
          {!isLast && <div className="w-0.5 h-6 bg-gradient-to-b from-gray-600 to-transparent mt-2" />}
        </div>
        <div className="ml-4 flex-1 pb-4">
          <div className="flex items-center gap-3">
            <span className="font-semibold text-gray-200">{stage.name}</span>
            {stage.time_ms !== null && stage.time_ms !== undefined && (
              <span className="text-xs px-2 py-0.5 rounded-full bg-gray-700 text-gray-300">
                {stage.time_ms.toFixed(0)}ms
              </span>
            )}
          </div>
          
          {/* Stage-specific content */}
          {stage.sources && stage.sources.length > 0 && (
            <div className="flex flex-wrap gap-1.5 mt-2">
              {stage.sources.map(src => <SourceBadge key={src} source={src} />)}
            </div>
          )}
          
          {stage.total_chunks !== undefined && (
            <div className="mt-2 text-sm text-gray-400">
              <span className="text-gray-300 font-medium">{stage.total_chunks}</span> chunks retrieved
              {stage.hybrid_search_used && (
                <span className="ml-2 text-emerald-400">
                  • Hybrid: {stage.dense_results || 0} dense + {stage.sparse_results || 0} sparse
                  {stage.found_by_both > 0 && ` (${stage.found_by_both} overlap)`}
                </span>
              )}
            </div>
          )}
          
          {stage.chunks_before !== undefined && (
            <div className="mt-2 text-sm">
              <span className="text-gray-400">Filtered: </span>
              <span className="text-gray-300">{stage.chunks_before}</span>
              <span className="text-gray-500 mx-1">→</span>
              <span className="text-emerald-400 font-medium">{stage.chunks_after}</span>
              <span className="text-gray-500 ml-1">chunks</span>
            </div>
          )}
          
          {stage.output?.primary_source && (
            <div className="mt-2 p-3 bg-gray-800/50 rounded-lg text-sm">
              <div className="flex items-center gap-2 mb-1">
                <span className="text-gray-400">Primary:</span>
                <SourceBadge source={stage.output.primary_source} />
              </div>
              {stage.output.confidence && (
                <div className="text-gray-400">
                  Confidence: <span className="text-white font-medium">{(stage.output.confidence * 100).toFixed(0)}%</span>
                </div>
              )}
              {stage.output.reasoning && (
                <div className="text-gray-400 mt-1 text-xs italic">"{stage.output.reasoning}"</div>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

// ============================================================================
// Chunk Details Component
// ============================================================================

const ChunkCard = ({ chunk, index, showStage }) => {
  const [expanded, setExpanded] = useState(false);
  
  // Determine rank and styling based on stage
  const isBefore = showStage === 'before';
  const isAfter = showStage === 'after';
  const displayRank = isBefore ? (chunk.rrf_rank || index + 1) : (chunk.final_rank || index + 1);
  const rankColorClass = isBefore 
    ? 'bg-amber-500/20 text-amber-400' 
    : isAfter 
      ? 'bg-emerald-500/20 text-emerald-400' 
      : 'bg-blue-500/20 text-blue-400';
  const borderColorClass = isBefore 
    ? 'border-amber-500/30' 
    : isAfter 
      ? 'border-emerald-500/30' 
      : 'border-gray-700/50';
  
  // Get display name - prefer file_path, then file_name, then chunk_id
  const displayName = chunk.file_path 
    ? chunk.file_path.split(/[\\\/]/).pop() 
    : (chunk.file_name || chunk.chunk_id?.replace('_before', '') || 'Unknown');
  
  return (
    <div className={`bg-gray-800/60 rounded-lg border ${borderColorClass} overflow-hidden`}>
      <div 
        className="p-3 cursor-pointer hover:bg-gray-700/30 transition-colors"
        onClick={() => setExpanded(!expanded)}
      >
        <div className="flex items-start justify-between gap-3">
          <div className="flex items-center gap-2 min-w-0 flex-1">
            <span className={`flex-shrink-0 w-6 h-6 rounded ${rankColorClass} text-xs font-bold flex items-center justify-center`}>
              {displayRank}
            </span>
            <SourceBadge source={chunk.source} />
            <span className="font-mono text-sm text-blue-300 truncate">
              {displayName}
            </span>
          </div>
          <div className="flex items-center gap-2 flex-shrink-0">
            {chunk.cross_encoder_score !== null && chunk.cross_encoder_score !== undefined && (
              <span className="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded text-xs font-medium">
                {chunk.cross_encoder_score.toFixed(2)}
              </span>
            )}
            {chunk.found_by_both && (
              <span className="px-2 py-0.5 bg-purple-500/20 text-purple-300 rounded text-xs">
                Both
              </span>
            )}
            {expanded ? <ChevronDown className="w-4 h-4 text-gray-500" /> : <ChevronRight className="w-4 h-4 text-gray-500" />}
          </div>
        </div>
        
        {chunk.class_name && (
          <div className="mt-1 text-xs text-gray-500 ml-8">
            {chunk.class_name}{chunk.method_name && ` → ${chunk.method_name}`}
          </div>
        )}
      </div>
      
      {expanded && (
        <div className="border-t border-gray-700/50 p-3 bg-gray-900/50">
          {/* File path if available */}
          {chunk.file_path && (
            <div className="text-xs text-gray-500 mb-2 font-mono truncate">
              📁 {chunk.file_path}
            </div>
          )}
          
          {chunk.content_preview && (
            <pre className="text-xs text-gray-300 font-mono bg-gray-800 p-3 rounded overflow-x-auto mb-3 whitespace-pre-wrap max-h-48 overflow-y-auto">
              {chunk.content_preview}
            </pre>
          )}
          
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            {chunk.rrf_rank && (
              <div className="bg-amber-500/10 border border-amber-500/20 p-2 rounded">
                <div className="text-amber-400 mb-0.5">RRF Rank</div>
                <div className="text-white font-bold">#{chunk.rrf_rank}</div>
              </div>
            )}
            {chunk.rrf_score !== null && chunk.rrf_score !== undefined && (
              <div className="bg-gray-800 p-2 rounded">
                <div className="text-gray-500 mb-0.5">RRF Score</div>
                <div className="text-gray-200 font-medium">{chunk.rrf_score.toFixed(4)}</div>
              </div>
            )}
            {chunk.cross_encoder_score !== null && chunk.cross_encoder_score !== undefined && (
              <div className="bg-emerald-500/10 border border-emerald-500/20 p-2 rounded">
                <div className="text-emerald-400 mb-0.5">CE Score</div>
                <div className="text-white font-bold">{chunk.cross_encoder_score.toFixed(3)}</div>
              </div>
            )}
            {chunk.final_rank && (
              <div className="bg-emerald-500/10 border border-emerald-500/20 p-2 rounded">
                <div className="text-emerald-400 mb-0.5">Final Rank</div>
                <div className="text-white font-bold">#{chunk.final_rank}</div>
              </div>
            )}
            {chunk.initial_distance !== null && chunk.initial_distance !== undefined && (
              <div className="bg-gray-800 p-2 rounded">
                <div className="text-gray-500 mb-0.5">Distance</div>
                <div className="text-gray-200 font-medium">{chunk.initial_distance.toFixed(4)}</div>
              </div>
            )}
            {chunk.bm25_score !== null && chunk.bm25_score !== undefined && (
              <div className="bg-gray-800 p-2 rounded">
                <div className="text-gray-500 mb-0.5">BM25 Score</div>
                <div className="text-gray-200 font-medium">{chunk.bm25_score.toFixed(2)}</div>
              </div>
            )}
            {chunk.initial_rank && (
              <div className="bg-gray-800 p-2 rounded">
                <div className="text-gray-500 mb-0.5">Initial Rank</div>
                <div className="text-gray-200 font-medium">#{chunk.initial_rank}</div>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

// Compact chunk card for comparison view
const ChunkCardCompact = ({ chunk, type }) => {
  const isAfter = type === 'after';
  const borderColor = isAfter ? 'border-emerald-500/30' : 'border-amber-500/30';
  const bgColor = isAfter ? 'bg-emerald-500/5' : 'bg-amber-500/5';
  
  // Get display name 
  const displayName = chunk.file_path 
    ? chunk.file_path.split(/[\\\/]/).pop() 
    : (chunk.file_name || chunk.chunk_id?.replace('_before', '') || 'Unknown');
  
  return (
    <div className={`rounded-lg border ${borderColor} ${bgColor} p-3 text-sm`}>
      <div className="flex items-start justify-between gap-2 mb-2">
        <div className="flex items-center gap-2 min-w-0">
          <span className={`flex-shrink-0 w-5 h-5 rounded text-xs font-bold flex items-center justify-center ${
            isAfter ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'
          }`}>
            {isAfter ? chunk.final_rank : chunk.rrf_rank || '?'}
          </span>
          <SourceBadge source={chunk.source} />
          <span className="font-mono text-xs text-gray-300 truncate" title={chunk.file_path || displayName}>
            {displayName}
          </span>
        </div>
        <div className="flex items-center gap-1.5 flex-shrink-0">
          {chunk.rrf_score !== null && chunk.rrf_score !== undefined && (
            <span className="px-1.5 py-0.5 bg-gray-700 text-gray-300 rounded text-xs">
              RRF: {chunk.rrf_score.toFixed(3)}
            </span>
          )}
          {isAfter && chunk.cross_encoder_score !== null && chunk.cross_encoder_score !== undefined && (
            <span className="px-1.5 py-0.5 bg-emerald-500/20 text-emerald-300 rounded text-xs font-medium">
              CE: {chunk.cross_encoder_score.toFixed(2)}
            </span>
          )}
        </div>
      </div>
      {chunk.class_name && (
        <div className="text-xs text-gray-500 mb-1">
          {chunk.class_name}{chunk.method_name && ` → ${chunk.method_name}`}
        </div>
      )}
      {chunk.content_preview && (
        <div className="text-xs text-gray-400 line-clamp-2 font-mono bg-gray-800/50 p-1.5 rounded">
          {chunk.content_preview.substring(0, 150)}...
        </div>
      )}
    </div>
  );
};

// ============================================================================
// Log Detail Modal
// ============================================================================

const LogDetailModal = ({ logId, onClose }) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('pipeline');
  
  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(`${API_BASE_URL}/inference-logs/${logId}/pipeline`);
        if (response.ok) {
          setData(await response.json());
        }
      } catch (error) {
        console.error('Failed to fetch:', error);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [logId]);
  
  return (
    <div className="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div className="bg-gray-900 rounded-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden flex flex-col border border-gray-700/50 shadow-2xl">
        {/* Header */}
        <div className="p-5 border-b border-gray-700/50 flex items-center justify-between bg-gray-800/50">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
              <Activity className="w-5 h-5 text-blue-400" />
            </div>
            <div>
              <h2 className="text-xl font-bold text-white">Inference Details</h2>
              <p className="text-sm text-gray-400">Log #{logId}</p>
            </div>
          </div>
          <button 
            onClick={onClose}
            className="p-2 hover:bg-gray-700 rounded-lg transition-colors text-gray-400 hover:text-white"
          >
            <X className="w-5 h-5" />
          </button>
        </div>
        
        {/* Content */}
        <div className="flex-1 overflow-y-auto">
          {loading ? (
            <div className="flex items-center justify-center h-64">
              <LoadingSpinner />
            </div>
          ) : data ? (
            <div className="p-5">
              {/* Query Info */}
              <div className="bg-gray-800/50 rounded-xl p-4 border border-gray-700/50 mb-6">
                <div className="text-sm text-gray-400 mb-2">Query</div>
                <div className="text-lg text-white font-medium">{data.query}</div>
                <div className="flex items-center gap-4 mt-3 text-sm">
                  <span className="flex items-center gap-1.5 text-gray-400">
                    <Clock className="w-4 h-4" />
                    {data.total_time_ms?.toFixed(0)}ms total
                  </span>
                  <span className="text-gray-600">•</span>
                  <span className="text-gray-400">
                    {new Date(data.created_at).toLocaleString()}
                  </span>
                  {data.success ? (
                    <span className="flex items-center gap-1 text-emerald-400">
                      <CheckCircle className="w-4 h-4" /> Success
                    </span>
                  ) : (
                    <span className="flex items-center gap-1 text-red-400">
                      <XCircle className="w-4 h-4" /> Failed
                    </span>
                  )}
                </div>
              </div>
              
              {/* Tabs */}
              <div className="flex gap-2 mb-6 border-b border-gray-700/50 pb-3">
                {['pipeline', 'before', 'after', 'comparison'].map(tab => (
                  <button
                    key={tab}
                    onClick={() => setActiveTab(tab)}
                    className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                      activeTab === tab 
                        ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' 
                        : 'text-gray-400 hover:text-white hover:bg-gray-800'
                    }`}
                  >
                    {tab === 'pipeline' ? 'Pipeline' : 
                     tab === 'before' ? `Before Rerank (${data.rerank_summary?.before_count || 0})` :
                     tab === 'after' ? `After Rerank (${data.rerank_summary?.after_count || 0})` :
                     'Comparison'}
                  </button>
                ))}
              </div>
              
              {/* Rerank Summary */}
              {data.rerank_summary && (
                <div className="mb-4 p-3 bg-amber-500/10 border border-amber-500/20 rounded-lg flex items-center gap-4 text-sm">
                  <Zap className="w-5 h-5 text-amber-400" />
                  <span className="text-amber-300">
                    Cross-encoder filtered <span className="font-bold">{data.rerank_summary.before_count}</span> chunks 
                    down to <span className="font-bold text-emerald-400">{data.rerank_summary.after_count}</span>
                    {data.rerank_summary.filtered_out > 0 && (
                      <span className="text-red-400"> ({data.rerank_summary.filtered_out} removed)</span>
                    )}
                  </span>
                </div>
              )}
              
              {/* Tab Content */}
              {activeTab === 'pipeline' ? (
                <div className="space-y-1">
                  {data.stages?.map((stage, idx) => (
                    <PipelineStage 
                      key={idx} 
                      stage={stage} 
                      isLast={idx === data.stages.length - 1}
                    />
                  ))}
                </div>
              ) : activeTab === 'before' ? (
                <div className="space-y-3">
                  <div className="text-gray-400 text-sm mb-4">
                    Chunks after RRF fusion, <span className="text-amber-400 font-medium">before cross-encoder reranking</span>
                  </div>
                  {data.chunks_before_rerank?.length > 0 ? (
                    data.chunks_before_rerank.map((chunk, idx) => (
                      <ChunkCard key={idx} chunk={chunk} index={idx} showStage="before" />
                    ))
                  ) : (
                    <div className="text-center py-8 text-gray-500">No chunks recorded before reranking</div>
                  )}
                </div>
              ) : activeTab === 'after' ? (
                <div className="space-y-3">
                  <div className="text-gray-400 text-sm mb-4">
                    Final chunks <span className="text-emerald-400 font-medium">after cross-encoder reranking</span> (used in LLM context)
                  </div>
                  {data.chunks_after_rerank?.length > 0 ? (
                    data.chunks_after_rerank.map((chunk, idx) => (
                      <ChunkCard key={idx} chunk={chunk} index={idx} showStage="after" />
                    ))
                  ) : (
                    <div className="text-center py-8 text-gray-500">No chunks after reranking</div>
                  )}
                </div>
              ) : (
                /* Comparison View */
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  {/* Before Column */}
                  <div>
                    <div className="flex items-center gap-2 mb-4 pb-2 border-b border-gray-700">
                      <div className="w-3 h-3 rounded-full bg-amber-500"></div>
                      <h3 className="font-semibold text-amber-400">Before Reranking</h3>
                      <span className="text-gray-500 text-sm">({data.chunks_before_rerank?.length || 0} chunks)</span>
                    </div>
                    <div className="space-y-2 max-h-[500px] overflow-y-auto pr-2">
                      {data.chunks_before_rerank?.map((chunk, idx) => (
                        <ChunkCardCompact key={idx} chunk={chunk} type="before" />
                      ))}
                    </div>
                  </div>
                  
                  {/* After Column */}
                  <div>
                    <div className="flex items-center gap-2 mb-4 pb-2 border-b border-gray-700">
                      <div className="w-3 h-3 rounded-full bg-emerald-500"></div>
                      <h3 className="font-semibold text-emerald-400">After Reranking</h3>
                      <span className="text-gray-500 text-sm">({data.chunks_after_rerank?.length || 0} chunks)</span>
                    </div>
                    <div className="space-y-2 max-h-[500px] overflow-y-auto pr-2">
                      {data.chunks_after_rerank?.map((chunk, idx) => (
                        <ChunkCardCompact key={idx} chunk={chunk} type="after" />
                      ))}
                    </div>
                  </div>
                </div>
              )}
            </div>
          ) : (
            <div className="flex flex-col items-center justify-center h-64 text-gray-400">
              <XCircle className="w-12 h-12 mb-3 opacity-50" />
              <p>Failed to load details</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

// ============================================================================
// Log Row Component
// ============================================================================

const LogRow = ({ log, onViewDetails, isSelected }) => {
  const [expanded, setExpanded] = useState(false);
  
  return (
    <div className={`rounded-xl border transition-all ${
      isSelected 
        ? 'border-blue-500/50 bg-blue-500/5' 
        : 'border-gray-700/50 bg-gray-800/30 hover:bg-gray-800/50 hover:border-gray-600/50'
    }`}>
      <div 
        className="p-4 cursor-pointer"
        onClick={() => setExpanded(!expanded)}
      >
        <div className="flex items-start justify-between gap-4">
          <div className="flex items-start gap-3 min-w-0 flex-1">
            <button className="mt-0.5 p-1 rounded hover:bg-gray-700/50 text-gray-500">
              {expanded ? <ChevronDown className="w-4 h-4" /> : <ChevronRight className="w-4 h-4" />}
            </button>
            <div className="min-w-0 flex-1">
              <div className="flex items-center gap-2 mb-2">
                {log.success ? (
                  <CheckCircle className="w-4 h-4 text-emerald-400 flex-shrink-0" />
                ) : (
                  <XCircle className="w-4 h-4 text-red-400 flex-shrink-0" />
                )}
                <span className="text-gray-100 truncate font-medium">{log.query}</span>
              </div>
              <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                <span className="flex items-center gap-1">
                  <Clock className="w-3 h-3" />
                  {log.total_time_ms?.toFixed(0) || '?'}ms
                </span>
                <span className="flex items-center gap-1">
                  <Database className="w-3 h-3" />
                  {log.chunks_after_reranking || 0} chunks
                </span>
                <span>{new Date(log.created_at).toLocaleString()}</span>
              </div>
            </div>
          </div>
          
          <div className="flex items-center gap-2 flex-shrink-0">
            <div className="hidden md:flex flex-wrap gap-1.5">
              {log.sources_queried?.slice(0, 2).map(src => (
                <SourceBadge key={src} source={src} />
              ))}
              {log.sources_queried?.length > 2 && (
                <span className="px-2 py-1 bg-gray-700 text-gray-400 rounded-full text-xs">
                  +{log.sources_queried.length - 2}
                </span>
              )}
            </div>
            <button
              onClick={(e) => {
                e.stopPropagation();
                onViewDetails(log.id);
              }}
              className="p-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-colors border border-blue-500/20"
              title="View full details"
            >
              <Eye className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
      
      {expanded && (
        <div className="border-t border-gray-700/50 p-4 bg-gray-900/30">
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm mb-4">
            <div>
              <div className="text-gray-500 text-xs mb-1">Query Type</div>
              <div className="text-gray-200 font-medium">{log.query_type || 'N/A'}</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">Routing</div>
              <div className="text-gray-200 font-medium">{log.routing_time_ms?.toFixed(0) || '?'}ms</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">Retrieval</div>
              <div className="text-gray-200 font-medium">{log.retrieval_time_ms?.toFixed(0) || '?'}ms</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">Reranking</div>
              <div className="text-gray-200 font-medium">{log.reranking_time_ms?.toFixed(0) || '?'}ms</div>
            </div>
          </div>
          
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
              <div className="text-gray-500 text-xs mb-1">Primary Source</div>
              <div className="text-gray-200">{log.primary_source || 'N/A'}</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">Initial Chunks</div>
              <div className="text-gray-200">{log.total_chunks_retrieved || 0}</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">After Filter</div>
              <div className="text-gray-200">{log.chunks_after_filtering || 0}</div>
            </div>
            <div>
              <div className="text-gray-500 text-xs mb-1">Hybrid Search</div>
              <div className={log.hybrid_search_used ? "text-emerald-400" : "text-gray-500"}>
                {log.hybrid_search_used ? '✓ Enabled' : 'Disabled'}
              </div>
            </div>
          </div>
          
          {log.routing_reasoning && (
            <div className="mt-4 p-3 bg-gray-800/50 rounded-lg">
              <div className="text-gray-500 text-xs mb-1">Routing Reasoning</div>
              <div className="text-gray-300 text-sm italic">"{log.routing_reasoning}"</div>
            </div>
          )}
          
          {log.error_message && (
            <div className="mt-4 p-3 bg-red-500/10 border border-red-500/30 rounded-lg">
              <div className="text-red-400 text-xs mb-1">Error Message</div>
              <div className="text-red-300 text-sm">{log.error_message}</div>
            </div>
          )}
          
          {/* Mobile source badges */}
          <div className="md:hidden flex flex-wrap gap-1.5 mt-4">
            {log.sources_queried?.map(src => (
              <SourceBadge key={src} source={src} />
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

// ============================================================================
// Main Page Component
// ============================================================================

export default function InferenceLogsPage() {
  const [logs, setLogs] = useState([]);
  const [summary, setSummary] = useState(null);
  const [loading, setLoading] = useState(true);
  const [selectedLogId, setSelectedLogId] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
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
  
  // Filter logs by search query
  const filteredLogs = logs.filter(log => 
    !searchQuery || log.query?.toLowerCase().includes(searchQuery.toLowerCase())
  );
  
  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-950 via-gray-900 to-slate-950">
      {/* Header */}
      <header className="sticky top-0 z-40 bg-gray-900/80 backdrop-blur-xl border-b border-gray-800/50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center gap-4">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                  <Activity className="w-5 h-5 text-white" />
                </div>
                <div>
                  <h1 className="text-xl font-bold text-white">Inference Logs</h1>
                  <p className="text-xs text-gray-500">RAG Pipeline Monitor</p>
                </div>
              </div>
            </div>
            
            <div className="flex items-center gap-3">
              <button 
                onClick={fetchLogs}
                disabled={loading}
                className="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-gray-300 text-sm font-medium transition-colors disabled:opacity-50"
              >
                <RefreshCw className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} />
                <span className="hidden sm:inline">Refresh</span>
              </button>
            </div>
          </div>
        </div>
      </header>
      
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Summary Cards */}
        {summary && (
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <StatCard 
              icon={BarChart2}
              label="Total Queries"
              value={summary.total_queries}
              subtext={`Last ${filters.hoursAgo || 24} hours`}
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
              color="purple"
            />
          </div>
        )}
        
        {/* Filters */}
        <div className="bg-gray-800/30 rounded-xl border border-gray-700/50 p-4 mb-6">
          <div className="flex flex-wrap gap-4">
            {/* Search */}
            <div className="flex-1 min-w-[200px]">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                <input
                  type="text"
                  placeholder="Search queries..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full pl-10 pr-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-lg text-gray-200 text-sm placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500/50"
                />
              </div>
            </div>
            
            {/* Time Filter */}
            <select
              value={filters.hoursAgo || ''}
              onChange={(e) => setFilters({...filters, hoursAgo: e.target.value ? parseInt(e.target.value) : null})}
              className="px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-lg text-gray-200 text-sm focus:border-blue-500 focus:outline-none appearance-none cursor-pointer min-w-[140px]"
            >
              <option value="1">Last hour</option>
              <option value="6">Last 6 hours</option>
              <option value="24">Last 24 hours</option>
              <option value="48">Last 48 hours</option>
              <option value="168">Last week</option>
            </select>
            
            {/* Status Filter */}
            <select
              value={filters.successOnly === null ? '' : filters.successOnly.toString()}
              onChange={(e) => setFilters({...filters, successOnly: e.target.value === '' ? null : e.target.value === 'true'})}
              className="px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-lg text-gray-200 text-sm focus:border-blue-500 focus:outline-none appearance-none cursor-pointer min-w-[130px]"
            >
              <option value="">All status</option>
              <option value="true">✓ Successful</option>
              <option value="false">✗ Failed</option>
            </select>
            
            {/* Source Filter */}
            <select
              value={filters.source}
              onChange={(e) => setFilters({...filters, source: e.target.value})}
              className="px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-lg text-gray-200 text-sm focus:border-blue-500 focus:outline-none appearance-none cursor-pointer min-w-[150px]"
            >
              <option value="">All sources</option>
              <option value="php_code">PHP Code</option>
              <option value="js_code">JS Code</option>
              <option value="blade_templates">Blade Templates</option>
              <option value="business_docs">Business Docs</option>
            </select>
          </div>
        </div>
        
        {/* Logs List */}
        <div className="space-y-3">
          {loading ? (
            <div className="flex flex-col items-center justify-center py-16">
              <LoadingSpinner size="lg" />
              <p className="text-gray-500 mt-4">Loading inference logs...</p>
            </div>
          ) : filteredLogs.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-16 text-gray-500">
              <Database className="w-16 h-16 mb-4 opacity-30" />
              <p className="text-lg font-medium">No logs found</p>
              <p className="text-sm mt-1">Try adjusting your filters or time range</p>
            </div>
          ) : (
            <>
              <div className="flex items-center justify-between mb-2 text-sm text-gray-500">
                <span>Showing {filteredLogs.length} log{filteredLogs.length !== 1 ? 's' : ''}</span>
              </div>
              {filteredLogs.map(log => (
                <LogRow 
                  key={log.id} 
                  log={log} 
                  onViewDetails={setSelectedLogId}
                  isSelected={selectedLogId === log.id}
                />
              ))}
            </>
          )}
        </div>
      </main>
      
      {/* Detail Modal */}
      {selectedLogId && (
        <LogDetailModal 
          logId={selectedLogId} 
          onClose={() => setSelectedLogId(null)} 
        />
      )}
    </div>
  );
}
