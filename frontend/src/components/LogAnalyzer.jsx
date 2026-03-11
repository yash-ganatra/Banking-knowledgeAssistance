import { useState, useRef, useCallback, useEffect } from 'react';
import { Upload, FileText, AlertTriangle, AlertCircle, ChevronDown, ChevronRight, Loader2, Search, X, CheckCircle2, XCircle, FileCode } from 'lucide-react';
import ReactMarkdown from 'react-markdown';
import { motion, AnimatePresence } from 'framer-motion';
import { cn } from '../lib/utils';

const API_BASE = 'http://localhost:8000';

const SEVERITY_CONFIG = {
    CRITICAL: { color: 'text-red-600 dark:text-red-400', bg: 'bg-red-50 dark:bg-red-900/20', border: 'border-red-200 dark:border-red-800', badge: 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300' },
    HIGH: { color: 'text-orange-600 dark:text-orange-400', bg: 'bg-orange-50 dark:bg-orange-900/20', border: 'border-orange-200 dark:border-orange-800', badge: 'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300' },
    MEDIUM: { color: 'text-yellow-600 dark:text-yellow-400', bg: 'bg-yellow-50 dark:bg-yellow-900/20', border: 'border-yellow-200 dark:border-yellow-800', badge: 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300' },
    LOW: { color: 'text-blue-600 dark:text-blue-400', bg: 'bg-blue-50 dark:bg-blue-900/20', border: 'border-blue-200 dark:border-blue-800', badge: 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300' },
};

function LogAnalyzer({ isDarkMode }) {
    // ---- State ----
    const [file, setFile] = useState(null);
    const [isDragging, setIsDragging] = useState(false);
    const [parseResult, setParseResult] = useState(null);
    const [analysisResult, setAnalysisResult] = useState(null);
    const [isParsing, setIsParsing] = useState(false);
    const [isAnalyzing, setIsAnalyzing] = useState(false);
    const [selectedErrors, setSelectedErrors] = useState(new Set());
    const [expandedErrors, setExpandedErrors] = useState(new Set());
    const [expandedAnalysis, setExpandedAnalysis] = useState(new Set());
    const [filterText, setFilterText] = useState('');
    // Tracks which view to show: 'parse' = error list, 'analysis' = results
    const [activeTab, setActiveTab] = useState('parse');
    const fileInputRef = useRef(null);
    const analysisRef = useRef(null);

    // Auto-scroll to analysis section when results arrive
    useEffect(() => {
        if (analysisResult && !analysisResult.error && analysisRef.current) {
            analysisRef.current.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, [analysisResult]);

    // ---- Drag & Drop ----
    const handleDragOver = useCallback((e) => {
        e.preventDefault();
        setIsDragging(true);
    }, []);

    const handleDragLeave = useCallback((e) => {
        e.preventDefault();
        setIsDragging(false);
    }, []);

    const handleDrop = useCallback((e) => {
        e.preventDefault();
        setIsDragging(false);
        const droppedFile = e.dataTransfer.files[0];
        if (droppedFile && (droppedFile.name.endsWith('.log') || droppedFile.name.endsWith('.txt'))) {
            setFile(droppedFile);
            setParseResult(null);
            setAnalysisResult(null);
            setActiveTab('parse');
        }
    }, []);

    const handleFileSelect = (e) => {
        const selected = e.target.files[0];
        if (selected) {
            setFile(selected);
            setParseResult(null);
            setAnalysisResult(null);
            setActiveTab('parse');
        }
    };

    // ---- Parse (preview) ----
    const handleParse = async () => {
        if (!file) return;
        setIsParsing(true);
        setParseResult(null);
        setAnalysisResult(null);
        setActiveTab('parse');

        try {
            const formData = new FormData();
            formData.append('file', file);

            const res = await fetch(`${API_BASE}/api/log-analyzer/parse`, {
                method: 'POST',
                body: formData,
            });

            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.detail || 'Failed to parse log file');
            }

            const data = await res.json();
            setParseResult(data);
            setSelectedErrors(new Set(data.errors.map(e => e.fingerprint)));
        } catch (err) {
            console.error('Parse error:', err);
            setParseResult({ error: err.message });
        } finally {
            setIsParsing(false);
        }
    };

    // ---- Analyze (full LLM) ----
    const handleAnalyze = async () => {
        if (!file || selectedErrors.size === 0) return;
        setIsAnalyzing(true);
        setAnalysisResult(null);
        // Switch to analysis tab immediately so user sees progress
        setActiveTab('analysis');

        const selectedList = Array.from(selectedErrors);
        console.log('[LogAnalyzer] Starting analysis for', selectedList.length, 'errors:', selectedList);

        try {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('selected_errors', selectedList.join(','));
            formData.append('top_k_context', '5');

            const res = await fetch(`${API_BASE}/api/log-analyzer/analyze`, {
                method: 'POST',
                body: formData,
            });

            console.log('[LogAnalyzer] Response status:', res.status);

            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                console.error('[LogAnalyzer] Error response:', err);
                throw new Error(err.detail || 'Failed to analyze log file');
            }

            const data = await res.json();
            console.log('[LogAnalyzer] Analysis complete:', {
                analyzed_count: data.analyzed_count,
                analyses_length: data.analyses?.length,
                first_rca_len: data.analyses?.[0]?.root_cause_analysis?.length || 0,
            });

            setAnalysisResult(data);
            if (data.analyses?.length > 0) {
                setExpandedAnalysis(new Set(data.analyses.map(a => a.fingerprint)));
            }
        } catch (err) {
            console.error('[LogAnalyzer] Analysis error:', err);
            setAnalysisResult({ error: err.message });
        } finally {
            setIsAnalyzing(false);
        }
    };

    // ---- Helpers ----
    const toggleError = (fp) => {
        setSelectedErrors(prev => {
            const next = new Set(prev);
            next.has(fp) ? next.delete(fp) : next.add(fp);
            return next;
        });
    };

    const toggleAllErrors = () => {
        if (!parseResult?.errors) return;
        if (selectedErrors.size === parseResult.errors.length) {
            setSelectedErrors(new Set());
        } else {
            setSelectedErrors(new Set(parseResult.errors.map(e => e.fingerprint)));
        }
    };

    const toggleExpandError = (fp) => {
        setExpandedErrors(prev => {
            const next = new Set(prev);
            next.has(fp) ? next.delete(fp) : next.add(fp);
            return next;
        });
    };

    const toggleExpandAnalysis = (fp) => {
        setExpandedAnalysis(prev => {
            const next = new Set(prev);
            next.has(fp) ? next.delete(fp) : next.add(fp);
            return next;
        });
    };

    const getShortFile = (path) => {
        if (!path) return 'Unknown';
        const parts = path.split('/');
        return parts[parts.length - 1];
    };

    const filteredErrors = parseResult?.errors?.filter(e => {
        if (!filterText) return true;
        const q = filterText.toLowerCase();
        return (
            e.error_message?.toLowerCase().includes(q) ||
            e.origin_file?.toLowerCase().includes(q) ||
            e.triggering_function?.toLowerCase().includes(q) ||
            e.exception_class?.toLowerCase().includes(q)
        );
    });

    const handleReset = () => {
        setFile(null);
        setParseResult(null);
        setAnalysisResult(null);
        setSelectedErrors(new Set());
        setExpandedErrors(new Set());
        setExpandedAnalysis(new Set());
        setFilterText('');
        setActiveTab('parse');
        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    // ============================================================
    // RENDER
    // ============================================================
    return (
        <div className="flex-1 overflow-y-auto p-6 lg:p-10">
            <div className="max-w-5xl mx-auto space-y-8">

                {/* Header */}
                <div className="flex items-center gap-3 pb-2 border-b border-gray-100 dark:border-[#2e2e2e]">
                    <div className={cn(
                        "p-2 rounded-lg",
                        isDarkMode ? "bg-gray-800 text-gray-300" : "bg-gray-100 text-gray-700"
                    )}>
                        <FileText size={20} />
                    </div>
                    <div>
                        <h2 className={cn("text-lg font-semibold", isDarkMode ? "text-gray-200" : "text-gray-900")}>
                            Log Analyzer
                        </h2>
                        <p className={cn("text-xs font-medium mt-0.5", isDarkMode ? "text-gray-500" : "text-gray-500")}>
                            Upload a server log file to identify, deduplicate, and analyze errors using codebase context.
                        </p>
                    </div>
                </div>

                {/* Upload Area — only show when no results */}
                {!parseResult && !analysisResult && (
                    <motion.div
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        className={cn(
                            "border border-dashed rounded-xl p-10 text-center transition-all cursor-pointer",
                            isDragging
                                ? (isDarkMode ? "border-gray-500 bg-gray-800/50" : "border-gray-400 bg-gray-50")
                                : file
                                    ? (isDarkMode ? "border-gray-600 bg-[#1e1e1e]" : "border-gray-300 bg-gray-50")
                                    : (isDarkMode ? "border-[#2e2e2e] bg-[#1a1a1a] hover:border-gray-500" : "border-gray-200 bg-gray-50/50 hover:border-gray-300")
                        )}
                        onDragOver={handleDragOver}
                        onDragLeave={handleDragLeave}
                        onDrop={handleDrop}
                        onClick={() => fileInputRef.current?.click()}
                    >
                        <input
                            ref={fileInputRef}
                            type="file"
                            accept=".log,.txt"
                            onChange={handleFileSelect}
                            className="hidden"
                        />
                        {file ? (
                            <div className="space-y-4">
                                <div className={cn("mx-auto w-12 h-12 flex items-center justify-center rounded-full", isDarkMode ? "bg-gray-800 text-gray-300" : "bg-gray-100 text-gray-600")}>
                                    <CheckCircle2 size={24} />
                                </div>
                                <div>
                                    <p className={cn("text-base font-medium", isDarkMode ? "text-gray-200" : "text-gray-900")}>{file.name}</p>
                                    <p className={cn("text-xs mt-1", isDarkMode ? "text-gray-500" : "text-gray-500")}>
                                        {(file.size / 1024).toFixed(1)} KB • Ready to parse
                                    </p>
                                </div>
                                <button
                                    onClick={(e) => { e.stopPropagation(); handleParse(); }}
                                    disabled={isParsing}
                                    className={cn(
                                        "mt-6 px-6 py-2 rounded-lg text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 flex items-center gap-2 mx-auto disabled:opacity-50 disabled:cursor-not-allowed",
                                        isDarkMode
                                            ? "bg-white text-black hover:bg-gray-100 focus:ring-gray-300 focus:ring-offset-[#1e1e1e]"
                                            : "bg-[#111827] text-white hover:bg-black focus:ring-gray-900"
                                    )}
                                >
                                    {isParsing ? (
                                        <><Loader2 size={18} className="animate-spin" /> Parsing...</>
                                    ) : (
                                        <><Search size={18} /> Parse & Preview Errors</>
                                    )}
                                </button>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                <div className={cn("mx-auto w-12 h-12 flex items-center justify-center rounded-full", isDarkMode ? "bg-gray-800 text-gray-400" : "bg-gray-100 text-gray-500")}>
                                    <Upload size={24} />
                                </div>
                                <div>
                                    <p className={cn("text-sm font-medium", isDarkMode ? "text-gray-300" : "text-gray-700")}>
                                        Drop your log file here or click to browse
                                    </p>
                                    <p className={cn("text-xs mt-1", isDarkMode ? "text-gray-500" : "text-gray-400")}>
                                        Supports .log and .txt files up to 10 MB
                                    </p>
                                </div>
                            </div>
                        )}
                    </motion.div>
                )}

                {/* Parse Error */}
                {parseResult?.error && (
                    <div className="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-300 flex items-start gap-3">
                        <XCircle size={20} className="mt-0.5 shrink-0" />
                        <div>
                            <p className="font-medium">Failed to parse log file</p>
                            <p className="text-sm mt-1">{parseResult.error}</p>
                        </div>
                    </div>
                )}

                {/* Main content — only show when we have parse results */}
                {parseResult && !parseResult.error && (
                    <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-6">
                        {/* Stats bar */}
                        <div className={cn(
                            "flex flex-wrap items-center gap-4 p-4 rounded-xl border",
                            isDarkMode ? "bg-[#1e1e1e] border-[#2e2e2e]" : "bg-white border-gray-200"
                        )}>
                            <div className={cn("flex flex-col px-3 border-r", isDarkMode ? "border-gray-800" : "border-gray-100")}>
                                <span className={cn("text-[10px] uppercase font-semibold tracking-wider", isDarkMode ? "text-gray-500" : "text-gray-400")}>Entries</span>
                                <span className={cn("text-lg font-medium leading-none mt-1", isDarkMode ? "text-gray-200" : "text-gray-800")}>{parseResult.total_entries}</span>
                            </div>
                            <div className={cn("flex flex-col px-3 border-r", isDarkMode ? "border-gray-800" : "border-gray-100")}>
                                <span className={cn("text-[10px] uppercase font-semibold tracking-wider", isDarkMode ? "text-gray-500" : "text-gray-400")}>Unique</span>
                                <span className={cn("text-lg font-medium leading-none mt-1 text-blue-500")}>{parseResult.unique_errors}</span>
                            </div>
                            <div className="flex flex-col px-3">
                                <span className={cn("text-[10px] uppercase font-semibold tracking-wider", isDarkMode ? "text-gray-500" : "text-gray-400")}>Selected</span>
                                <span className={cn("text-lg font-medium leading-none mt-1", isDarkMode ? "text-gray-200" : "text-gray-800")}>{selectedErrors.size}</span>
                            </div>
                            <div className="ml-auto flex items-center gap-3">
                                <button onClick={handleReset} className={cn(
                                    "px-4 py-2 text-sm font-medium rounded-lg transition-colors",
                                    isDarkMode ? "text-gray-400 hover:text-gray-200 hover:bg-gray-800" : "text-gray-500 hover:text-gray-900 hover:bg-gray-100"
                                )}>
                                    Reset
                                </button>
                                <button
                                    onClick={handleAnalyze}
                                    disabled={isAnalyzing || selectedErrors.size === 0}
                                    className={cn(
                                        "px-5 py-2 rounded-lg text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed",
                                        isDarkMode
                                            ? "bg-white text-black hover:bg-gray-100 focus:ring-gray-300 focus:ring-offset-[#1e1e1e]"
                                            : "bg-[#111827] text-white hover:bg-black focus:ring-gray-900"
                                    )}
                                >
                                    {isAnalyzing ? (
                                        <><Loader2 size={16} className="animate-spin" /> Analyzing...</>
                                    ) : (
                                        <><AlertTriangle size={16} /> Analyze {selectedErrors.size} Error{selectedErrors.size !== 1 ? 's' : ''}</>
                                    )}
                                </button>
                            </div>
                        </div>

                        {/* Tab switcher */}
                        {(analysisResult || isAnalyzing) && (
                            <div className={cn(
                                "flex gap-1 p-1 rounded-lg border w-fit",
                                isDarkMode ? "bg-[#1a1a1a] border-[#2e2e2e]" : "bg-gray-50 border-gray-200"
                            )}>
                                <button
                                    onClick={() => setActiveTab('parse')}
                                    className={cn(
                                        "px-5 py-1.5 text-xs font-semibold rounded-md transition-all uppercase tracking-wider flex items-center gap-2",
                                        activeTab === 'parse'
                                            ? (isDarkMode ? "bg-[#2e2e2e] text-white shadow-sm" : "bg-white text-gray-900 shadow-sm border border-gray-200")
                                            : "text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                                    )}
                                >
                                    <span className="flex items-center justify-center gap-2">
                                        <AlertTriangle size={15} />
                                        Errors ({parseResult.unique_errors})
                                    </span>
                                </button>
                                <button
                                    onClick={() => setActiveTab('analysis')}
                                    className={cn(
                                        "px-5 py-1.5 text-xs font-semibold rounded-md transition-all uppercase tracking-wider flex items-center gap-2",
                                        activeTab === 'analysis'
                                            ? (isDarkMode ? "bg-[#2e2e2e] text-white shadow-sm" : "bg-white text-gray-900 shadow-sm border border-gray-200")
                                            : "text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                                    )}
                                >
                                    <span className="flex items-center justify-center gap-2">
                                        <AlertCircle size={14} />
                                        Analysis {analysisResult?.analyzed_count ? `(${analysisResult.analyzed_count})` : isAnalyzing ? '...' : ''}
                                    </span>
                                </button>
                            </div>
                        )}

                        {/* ===== ERRORS LIST TAB ===== */}
                        {activeTab === 'parse' && (
                            <>
                                {/* Filter + select all */}
                                <div className="flex items-center gap-3">
                                    <div className="relative flex-1">
                                        <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                                        <input
                                            type="text"
                                            placeholder="Filter errors..."
                                            value={filterText}
                                            onChange={(e) => setFilterText(e.target.value)}
                                            className="w-full pl-9 pr-8 py-2 text-sm bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-amber-300 dark:focus:ring-amber-800 focus:outline-none text-gray-800 dark:text-gray-200"
                                        />
                                        {filterText && (
                                            <button onClick={() => setFilterText('')} className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                <X size={14} />
                                            </button>
                                        )}
                                    </div>
                                    <button
                                        onClick={toggleAllErrors}
                                        className="px-3 py-2 text-sm font-medium text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                                    >
                                        {selectedErrors.size === parseResult.errors.length ? 'Deselect All' : 'Select All'}
                                    </button>
                                </div>

                                {/* Error list */}
                                <div className="space-y-3">
                                    {filteredErrors?.map((err) => {
                                        const sev = SEVERITY_CONFIG[err.severity_hint] || SEVERITY_CONFIG.MEDIUM;
                                        const isExpanded = expandedErrors.has(err.fingerprint);
                                        const isSelected = selectedErrors.has(err.fingerprint);

                                        return (
                                            <div
                                                key={err.fingerprint}
                                                className={cn(
                                                    "border rounded-xl transition-all overflow-hidden",
                                                    isSelected && (isDarkMode ? "border-blue-500/50 bg-blue-900/10" : "border-blue-300 bg-blue-50/50"),
                                                    !isSelected && (isDarkMode ? "border-[#2e2e2e] bg-[#1e1e1e]" : "border-gray-200 bg-white")
                                                )}
                                            >
                                                <div className="flex items-start gap-4 p-4">
                                                    <button
                                                        onClick={() => toggleError(err.fingerprint)}
                                                        className={cn(
                                                            "w-5 h-5 rounded border flex items-center justify-center shrink-0 transition-colors mt-0.5",
                                                            isSelected
                                                                ? "bg-blue-500 border-blue-500 text-white"
                                                                : (isDarkMode ? "border-gray-600 hover:border-gray-400" : "border-gray-300 hover:border-gray-400")
                                                        )}
                                                    >
                                                        {isSelected && <CheckCircle2 size={14} />}
                                                    </button>

                                                    <div className="flex-1 min-w-0">
                                                        <div className="flex items-center gap-2 flex-wrap mb-1.5">
                                                            <span className={cn("px-2 py-0.5 text-[10px] uppercase tracking-wider font-bold rounded", sev.badge)}>
                                                                {err.severity_hint}
                                                            </span>
                                                            <span className={cn(
                                                                "px-2 py-0.5 text-[10px] font-mono rounded",
                                                                isDarkMode ? "bg-gray-800 text-gray-400" : "bg-gray-100 text-gray-600"
                                                            )}>
                                                                {err.occurrence_count} {err.occurrence_count === 1 ? 'occurrence' : 'occurrences'}
                                                            </span>
                                                            {err.exception_class && (
                                                                <span className={cn(
                                                                    "text-[10px] font-mono",
                                                                    isDarkMode ? "text-gray-500" : "text-gray-500"
                                                                )}>
                                                                    {err.exception_class}
                                                                </span>
                                                            )}
                                                        </div>
                                                        <p className={cn(
                                                            "text-sm font-medium leading-relaxed break-words",
                                                            isDarkMode ? "text-gray-200" : "text-gray-900"
                                                        )}>
                                                            {err.error_message}
                                                        </p>
                                                        <div className={cn(
                                                            "mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs font-mono",
                                                            isDarkMode ? "text-gray-500" : "text-gray-500"
                                                        )}>
                                                            {err.origin_file && (
                                                                <span>{getShortFile(err.origin_file)}:{err.origin_line}</span>
                                                            )}
                                                            {err.triggering_function && (
                                                                <span>{err.triggering_function}</span>
                                                            )}
                                                            <span className="font-sans ml-auto text-gray-400 text-[11px]">{err.first_seen}</span>
                                                        </div>
                                                    </div>

                                                    <button
                                                        onClick={() => toggleExpandError(err.fingerprint)}
                                                        className={cn(
                                                            "p-1.5 rounded-md transition-colors mt-0.5",
                                                            isDarkMode ? "text-gray-500 hover:bg-gray-800" : "text-gray-400 hover:bg-gray-100"
                                                        )}
                                                    >
                                                        {isExpanded ? <ChevronDown size={14} /> : <ChevronRight size={14} />}
                                                    </button>
                                                </div>

                                                <AnimatePresence>
                                                    {isExpanded && (
                                                        <motion.div
                                                            initial={{ height: 0, opacity: 0 }}
                                                            animate={{ height: 'auto', opacity: 1 }}
                                                            exit={{ height: 0, opacity: 0 }}
                                                            className="overflow-hidden"
                                                        >
                                                            <div className="px-4 pb-4 pt-1 ml-9 space-y-2 text-xs">
                                                                {err.origin_file && (
                                                                    <div className="flex gap-2"><span className={cn("w-20 shrink-0", isDarkMode ? "text-gray-500" : "text-gray-400")}>Full path:</span><span className={cn("font-mono", isDarkMode ? "text-gray-300" : "text-gray-700")}>{err.origin_file}</span></div>
                                                                )}
                                                                {err.view_file && (
                                                                    <div className="flex gap-2"><span className={cn("w-20 shrink-0", isDarkMode ? "text-gray-500" : "text-gray-400")}>View:</span><span className={cn("font-mono", isDarkMode ? "text-gray-300" : "text-gray-700")}>{err.view_file}</span></div>
                                                                )}
                                                                {err.app_stack_frames?.length > 0 && (
                                                                    <div className="pt-2">
                                                                        <span className={cn("block mb-1", isDarkMode ? "text-gray-500" : "text-gray-400")}>Application Stack:</span>
                                                                        <div className={cn(
                                                                            "font-mono text-[10px] p-2 rounded border space-y-1 max-h-32 overflow-y-auto w-full",
                                                                            isDarkMode ? "bg-gray-900 border-gray-800 text-gray-400" : "bg-gray-50 border-gray-200 text-gray-600"
                                                                        )}>
                                                                            {err.app_stack_frames.map((f, i) => (
                                                                                <div key={i} className="truncate">{f}</div>
                                                                            ))}
                                                                        </div>
                                                                    </div>
                                                                )}
                                                                <div className={cn("flex gap-6 mt-3 pt-3 border-t", isDarkMode ? "border-[#2e2e2e] text-gray-500" : "border-gray-100 text-gray-400")}>
                                                                    <span>First: {err.first_seen}</span>
                                                                    <span>Last: {err.last_seen}</span>
                                                                </div>
                                                            </div>
                                                        </motion.div>
                                                    )}
                                                </AnimatePresence>
                                            </div>
                                        );
                                    })}
                                </div>
                            </>
                        )}

                        {/* ===== ANALYSIS TAB ===== */}
                        {activeTab === 'analysis' && (
                            <div ref={analysisRef} className="space-y-6">
                                {/* Loading state */}
                                {isAnalyzing && (
                                    <div className={cn("flex flex-col items-center justify-center py-16 space-y-4 rounded-xl border", isDarkMode ? "bg-[#1e1e1e] border-[#2e2e2e]" : "bg-white border-gray-200")}>
                                        <div className="flex gap-2">
                                            <Loader2 size={24} className={cn("animate-spin", isDarkMode ? "text-gray-400" : "text-gray-600")} />
                                        </div>
                                        <div className="text-center">
                                            <p className={cn("text-sm font-semibold", isDarkMode ? "text-gray-200" : "text-gray-800")}>Analyzing errors...</p>
                                            <p className={cn("text-xs mt-1", isDarkMode ? "text-gray-500" : "text-gray-500")}>
                                                Retrieving vector context and generating root cause
                                            </p>
                                        </div>
                                    </div>
                                )}

                                {/* Analysis Error */}
                                {analysisResult?.error && (
                                    <div className="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-300 flex items-start gap-3">
                                        <XCircle size={20} className="mt-0.5 shrink-0" />
                                        <div>
                                            <p className="font-medium">Analysis failed</p>
                                            <p className="text-sm mt-1">{analysisResult.error}</p>
                                        </div>
                                    </div>
                                )}

                                {/* Analysis Results */}
                                {analysisResult && !analysisResult.error && (
                                    <>
                                        <div className="flex items-center gap-3">
                                            <h3 className="text-lg font-bold text-gray-800 dark:text-white">Root Cause Analysis</h3>
                                            <span className="px-2 py-0.5 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full">
                                                {analysisResult.analyzed_count} analyzed
                                            </span>
                                        </div>

                                        <div className="space-y-4">
                                            {analysisResult.analyses.map((analysis) => {
                                                const sev = SEVERITY_CONFIG[analysis.severity_hint] || SEVERITY_CONFIG.MEDIUM;
                                                const isExpanded = expandedAnalysis.has(analysis.fingerprint);

                                                return (
                                                    <div
                                                        key={analysis.fingerprint}
                                                        className={cn(
                                                            "border rounded-xl overflow-hidden shadow-sm",
                                                            isDarkMode ? "bg-[#1e1e1e] border-[#2e2e2e]" : "bg-white border-gray-200"
                                                        )}
                                                    >
                                                        <button
                                                            onClick={() => toggleExpandAnalysis(analysis.fingerprint)}
                                                            className={cn(
                                                                "w-full flex items-start gap-4 p-5 text-left border-b hover:bg-black/5 hover:dark:bg-white/5 transition-colors",
                                                                isDarkMode ? "border-[#2e2e2e]" : "border-gray-100"
                                                            )}
                                                        >
                                                            <div className={cn("mt-0.5 rounded-full p-1.5", sev.bg, sev.color)}>
                                                                <FileCode size={16} />
                                                            </div>
                                                            <div className="flex-1 min-w-0">
                                                                <div className="flex items-center gap-2 mb-1">
                                                                    <span className={cn("px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider rounded border", sev.badge, sev.border)}>
                                                                        {analysis.severity_hint}
                                                                    </span>
                                                                    <div className={cn("h-1 w-1 rounded-full", isDarkMode ? "bg-gray-700" : "bg-gray-300")} />
                                                                    <span className={cn("text-xs font-mono truncate", isDarkMode ? "text-gray-400" : "text-gray-500")}>
                                                                        {getShortFile(analysis.origin_file)}:{analysis.origin_line}
                                                                    </span>
                                                                </div>
                                                                <p className={cn("text-sm font-medium leading-relaxed", isDarkMode ? "text-gray-200" : "text-gray-800")}>
                                                                    {analysis.error_message}
                                                                </p>
                                                            </div>
                                                            <div className={cn("p-1.5 rounded-md", isDarkMode ? "text-gray-500" : "text-gray-400")}>
                                                                {isExpanded ? <ChevronDown size={16} /> : <ChevronRight size={16} />}
                                                            </div>
                                                        </button>

                                                        <AnimatePresence>
                                                            {isExpanded && (
                                                                <motion.div
                                                                    initial={{ height: 0, opacity: 0 }}
                                                                    animate={{ height: 'auto', opacity: 1 }}
                                                                    exit={{ height: 0, opacity: 0 }}
                                                                    className="overflow-hidden bg-gray-50/30 dark:bg-black/20"
                                                                >
                                                                    <div className="p-6 md:p-8 space-y-6">
                                                                        <div className="prose prose-sm md:prose-base max-w-none text-gray-700 dark:text-gray-300 dark:prose-headings:text-gray-100 dark:prose-strong:text-gray-100 dark:prose-code:text-gray-200 dark:prose-pre:bg-gray-900/50 dark:prose-pre:border dark:prose-pre:border-gray-800">
                                                                            <ReactMarkdown>{analysis.root_cause_analysis}</ReactMarkdown>
                                                                        </div>

                                                                        {analysis.code_files_referenced?.length > 0 && (
                                                                            <div className={cn("pt-4 border-t", isDarkMode ? "border-[#2e2e2e]" : "border-gray-200")}>
                                                                                <p className={cn("text-[10px] font-semibold uppercase tracking-wider mb-2", isDarkMode ? "text-gray-500" : "text-gray-400")}>
                                                                                    Retrieved Vector DB Context
                                                                                </p>
                                                                                <div className="flex flex-wrap gap-2">
                                                                                    {analysis.code_files_referenced.map((f, i) => (
                                                                                        <span key={i} className={cn(
                                                                                            "px-2.5 py-1 text-[11px] font-mono border rounded-md",
                                                                                            isDarkMode ? "bg-gray-800 border-gray-700 text-gray-300" : "bg-white border-gray-200 text-gray-600 shadow-sm"
                                                                                        )}>
                                                                                            {getShortFile(f)}
                                                                                        </span>
                                                                                    ))}
                                                                                </div>
                                                                            </div>
                                                                        )}
                                                                    </div>
                                                                </motion.div>
                                                            )}
                                                        </AnimatePresence>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </>
                                )}
                            </div>
                        )}
                    </motion.div>
                )}
            </div>
        </div >
    );
}

export default LogAnalyzer;
