import { useState, useEffect, useRef, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
    Search, FileCode, Sparkles, Database, BarChart3, GitBranch,
    CheckCircle2, XCircle, Loader2, X, RefreshCw, FilePlus, FileMinus, FileEdit,
    ArrowRight
} from 'lucide-react';
import { cn } from '../lib/utils';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

/* ─── Pipeline step definitions (must match backend STEPS) ──────── */
const PIPELINE_STEPS = [
    { key: 'scanning', label: 'Scanning Files', icon: Search },
    { key: 'parsing', label: 'Parsing & Chunking', icon: FileCode },
    { key: 'descriptions', label: 'Generating Descriptions', icon: Sparkles },
    { key: 'embedding', label: 'Embedding to Vector DB', icon: Database },
    { key: 'bm25', label: 'Rebuilding BM25 Indices', icon: BarChart3 },
    { key: 'graph', label: 'Updating Knowledge Graph', icon: GitBranch },
];

/* ─── Single step node ──────────────────────────────────────────── */
function StepNode({ step, index, activeIndex, isComplete, isError, isDarkMode }) {
    const Icon = step.icon;
    const isActive = index === activeIndex;
    const isPast = index < activeIndex || isComplete;
    const isFuture = index > activeIndex && !isComplete;

    // Sleek monochrome/blue accent styling
    const activeColor = isDarkMode ? '#e5e7eb' : '#111827'; // text-gray-200 / text-gray-900
    const pastColor = isDarkMode ? '#9ca3af' : '#6b7280'; // text-gray-400 / text-gray-500
    const futureColor = isDarkMode ? '#374151' : '#d1d5db'; // text-gray-700 / text-gray-300

    // Line connector styling
    const lineBg = isDarkMode ? '#374151' : '#e5e7eb';
    const lineActiveBg = isDarkMode ? '#9ca3af' : '#4b5563';

    return (
        <motion.div
            className="flex flex-col items-center gap-3 relative flex-1"
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.05, duration: 0.3 }}
        >
            {/* Connector line (to previous) */}
            {index > 0 && (
                <div className="absolute top-4 -translate-y-1/2 w-[calc(100%-2rem)] right-[calc(50%+1rem)] h-[2px] z-0">
                    <div className="w-full h-full relative" style={{ backgroundColor: lineBg }}>
                        <motion.div
                            className="absolute top-0 left-0 h-full"
                            style={{ backgroundColor: lineActiveBg }}
                            initial={{ width: '0%' }}
                            animate={{ width: isPast ? '100%' : '0%' }}
                            transition={{ duration: 0.4 }}
                        />
                    </div>
                </div>
            )}

            {/* Icon Node */}
            <div className="relative z-10 bg-inherit">
                <motion.div
                    className={cn(
                        "w-8 h-8 rounded-full flex items-center justify-center transition-colors border-2",
                        isActive ? (isDarkMode ? "border-gray-200 bg-gray-800" : "border-gray-900 bg-white") :
                            isPast && !isError ? (isDarkMode ? "border-gray-500 bg-gray-800" : "border-gray-400 bg-gray-50") :
                                isError && isActive ? "border-red-500 bg-red-50" :
                                    (isDarkMode ? "border-gray-700 bg-transparent text-gray-600" : "border-gray-200 bg-transparent text-gray-400")
                    )}
                >
                    {isPast && !isError ? (
                        <CheckCircle2 size={16} className={isDarkMode ? "text-gray-400" : "text-gray-500"} />
                    ) : isError && isActive ? (
                        <XCircle size={16} className="text-red-500" />
                    ) : isActive ? (
                        <Loader2 size={16} className={cn("animate-spin", isDarkMode ? "text-gray-200" : "text-gray-900")} />
                    ) : (
                        <Icon size={14} />
                    )}
                </motion.div>
            </div>

            {/* Label */}
            <span
                className="text-[10px] font-semibold text-center leading-tight max-w-[70px] uppercase tracking-wide"
                style={{
                    color: isActive ? activeColor : isPast ? pastColor : futureColor,
                }}
            >
                {step.label}
            </span>
        </motion.div>
    );
}

/* ═══════════════════════════════════════════════════════════════════
   Main Component
   ═══════════════════════════════════════════════════════════════════ */
export default function SyncKnowledgeBase({ isOpen, onClose, isDarkMode }) {
    const [syncState, setSyncState] = useState('idle'); // idle | syncing | complete | error
    const [activeStepIndex, setActiveStepIndex] = useState(-1);
    const [currentFile, setCurrentFile] = useState('');
    const [fileIndex, setFileIndex] = useState(0);
    const [totalFiles, setTotalFiles] = useState(0);
    const [filesAdded, setFilesAdded] = useState(0);
    const [filesModified, setFilesModified] = useState(0);
    const [filesDeleted, setFilesDeleted] = useState(0);
    const [message, setMessage] = useState('');
    const [stepErrors, setStepErrors] = useState(new Set());
    const [logs, setLogs] = useState([]);
    const eventSourceRef = useRef(null);
    const logsEndRef = useRef(null);

    // Auto-scroll logs
    useEffect(() => {
        logsEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [logs]);

    const addLog = useCallback((msg, type = 'info') => {
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        setLogs((prev) => [...prev.slice(-80), { time, msg, type }]);
    }, []);

    const startSync = useCallback(async () => {
        setSyncState('syncing');
        setActiveStepIndex(0);
        setCurrentFile('');
        setFileIndex(0);
        setTotalFiles(0);
        setFilesAdded(0);
        setFilesModified(0);
        setFilesDeleted(0);
        setMessage('Starting sync...');
        setStepErrors(new Set());
        setLogs([]);
        addLog('Starting knowledge base sync...', 'info');

        try {
            const response = await fetch(`${API_BASE_URL}/api/sync/start`, { method: 'POST' });
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop() || '';

                for (const line of lines) {
                    if (!line.startsWith('data: ')) continue;
                    try {
                        const data = JSON.parse(line.slice(6));

                        // Update step
                        if (typeof data.step_index === 'number') {
                            setActiveStepIndex(data.step_index);
                        }

                        // Update file info
                        if (data.current_file) setCurrentFile(data.current_file);
                        if (typeof data.file_index === 'number') setFileIndex(data.file_index);
                        if (typeof data.total_files === 'number') setTotalFiles(data.total_files);
                        if (typeof data.files_added === 'number') setFilesAdded(data.files_added);
                        if (typeof data.files_modified === 'number') setFilesModified(data.files_modified);
                        if (typeof data.files_deleted === 'number') setFilesDeleted(data.files_deleted);

                        // Message
                        if (data.message) {
                            setMessage(data.message);
                            addLog(data.message, data.status === 'error' ? 'error' : 'info');
                        }

                        // Track step errors
                        if (data.status === 'error' && data.step) {
                            setStepErrors((prev) => new Set(prev).add(data.step));
                        }

                        // Terminal states
                        if (data.step === 'complete') {
                            setSyncState('complete');
                            setActiveStepIndex(PIPELINE_STEPS.length);
                            addLog('Sync complete.', 'success');
                        } else if (data.step === 'error' && !data.step_index && data.step_index !== 0) {
                            setSyncState('error');
                            addLog(`Error: ${data.message}`, 'error');
                        }
                    } catch {
                        // ignore parse errors
                    }
                }
            }
        } catch (err) {
            setSyncState('error');
            setMessage(`Connection error: ${err.message}`);
            addLog(`Connection error: ${err.message}`, 'error');
        }
    }, [addLog]);

    const handleClose = () => {
        if (eventSourceRef.current) {
            eventSourceRef.current.close();
            eventSourceRef.current = null;
        }
        onClose();
        // Reset after animation
        setTimeout(() => {
            setSyncState('idle');
            setActiveStepIndex(-1);
            setLogs([]);
        }, 300);
    };

    if (!isOpen) return null;

    const progress = totalFiles > 0
        ? Math.min(((fileIndex) / totalFiles) * 100, 100)
        : activeStepIndex >= 0
            ? ((activeStepIndex + 1) / PIPELINE_STEPS.length) * 100
            : 0;

    return (
        <AnimatePresence>
            {isOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    {/* Backdrop */}
                    <motion.div
                        className="absolute inset-0 bg-black/60 backdrop-blur-sm"
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.2 }}
                        onClick={syncState !== 'syncing' ? handleClose : undefined}
                    />

                    {/* Modal Content */}
                    <motion.div
                        className={cn(
                            "relative w-full max-w-3xl max-h-[90vh] overflow-hidden rounded-2xl shadow-2xl border",
                            isDarkMode ? "bg-[#1e1e1e] border-[#2e2e2e]" : "bg-white border-gray-200"
                        )}
                        initial={{ scale: 0.95, opacity: 0, y: 20 }}
                        animate={{ scale: 1, opacity: 1, y: 0 }}
                        exit={{ scale: 0.95, opacity: 0, y: 20 }}
                        transition={{ duration: 0.2, ease: "easeOut" }}
                    >
                        {/* Header */}
                        <div className={cn(
                            "relative flex items-center justify-between px-6 py-4 border-b",
                            isDarkMode ? "border-[#2e2e2e]" : "border-gray-100"
                        )}>
                            <div className="flex items-center gap-3">
                                <div className={cn(
                                    "p-2 rounded-lg",
                                    isDarkMode ? "bg-gray-800 text-gray-300" : "bg-gray-100 text-gray-700"
                                )}>
                                    <Database size={18} />
                                </div>
                                <div>
                                    <h2 className={cn("text-base font-semibold", isDarkMode ? "text-gray-200" : "text-gray-900")}>
                                        Knowledge Base Sync
                                    </h2>
                                    <p className={cn("text-xs font-medium", isDarkMode ? "text-gray-500" : "text-gray-500")}>
                                        {syncState === 'idle' && 'Update your pipeline with the latest data.'}
                                        {syncState === 'syncing' && 'Pipeline running...'}
                                        {syncState === 'complete' && 'Sync completed successfully.'}
                                        {syncState === 'error' && 'Sync failed.'}
                                    </p>
                                </div>
                            </div>
                            <button
                                onClick={handleClose}
                                disabled={syncState === 'syncing'}
                                className={cn(
                                    "p-2 rounded-md transition-colors",
                                    syncState === 'syncing' ? "opacity-30 cursor-not-allowed" :
                                        isDarkMode ? "text-gray-500 hover:text-gray-300 hover:bg-[#2e2e2e]" : "text-gray-400 hover:text-gray-700 hover:bg-gray-100"
                                )}
                            >
                                <X size={18} />
                            </button>
                        </div>

                        {/* Content Scrollable Zone */}
                        <div className="relative px-8 pb-8 pt-4 overflow-y-auto max-h-[calc(90vh-80px)] scrollbar-hide">

                            {/* ── Idle state: Start button ────────────────────── */}
                            {syncState === 'idle' && (
                                <motion.div
                                    className="flex flex-col items-center py-12 gap-8"
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                >
                                    <div className="text-center space-y-3 max-w-sm">
                                        <p className={cn("text-sm", isDarkMode ? "text-gray-400" : "text-gray-500")}>
                                            Running a sync will scan your configured directories for modified files, generate embeddings, and update the Neo4j knowledge graph.
                                        </p>
                                    </div>

                                    <div className={cn(
                                        "flex flex-col p-4 rounded-xl border w-full max-w-lg",
                                        isDarkMode ? "bg-gray-900/50 border-gray-800" : "bg-gray-50 border-gray-200"
                                    )}>
                                        <h4 className={cn("text-xs font-semibold mb-3 uppercase tracking-wider", isDarkMode ? "text-gray-500" : "text-gray-500")}>
                                            Pipeline Steps
                                        </h4>
                                        <div className="space-y-4">
                                            {PIPELINE_STEPS.map((s, i) => (
                                                <div key={s.key} className="flex items-center gap-3">
                                                    <div className={cn("p-1.5 rounded-md", isDarkMode ? "bg-[#1e1e1e] text-gray-400" : "bg-white border text-gray-500")}>
                                                        <s.icon size={14} />
                                                    </div>
                                                    <span className={cn("text-sm", isDarkMode ? "text-gray-300" : "text-gray-700")}>{s.label}</span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    <button
                                        onClick={startSync}
                                        className={cn(
                                            "px-6 py-2.5 rounded-lg font-medium text-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 w-full max-w-lg",
                                            isDarkMode
                                                ? "bg-white text-black hover:bg-gray-100 focus:ring-gray-300 focus:ring-offset-[#1e1e1e]"
                                                : "bg-[#111827] text-white hover:bg-black focus:ring-gray-900"
                                        )}
                                    >
                                        Start Sync
                                    </button>
                                </motion.div>
                            )}

                            {/* ── Active / Complete / Error state ─────────────── */}
                            {syncState !== 'idle' && (
                                <div className="space-y-8 pt-4">

                                    {/* ── Pipeline stepper ────────────────────────── */}
                                    <div className="relative w-full overflow-hidden py-4">
                                        <div className="flex items-start justify-between relative">
                                            {PIPELINE_STEPS.map((step, i) => (
                                                <StepNode
                                                    key={step.key}
                                                    step={step}
                                                    index={i}
                                                    activeIndex={activeStepIndex}
                                                    isComplete={syncState === 'complete'}
                                                    isError={stepErrors.has(step.key)}
                                                    isDarkMode={isDarkMode}
                                                />
                                            ))}
                                        </div>
                                    </div>

                                    {/* File change summary chips */}
                                    {(filesAdded > 0 || filesModified > 0 || filesDeleted > 0) && (
                                        <motion.div
                                            className="flex justify-center gap-3 flex-wrap"
                                            initial={{ opacity: 0, y: -5 }}
                                            animate={{ opacity: 1, y: 0 }}
                                        >
                                            {filesAdded > 0 && (
                                                <span className={cn(
                                                    "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border",
                                                    isDarkMode ? "bg-emerald-500/10 text-emerald-400 border-emerald-500/20" : "bg-emerald-50 text-emerald-700 border-emerald-200"
                                                )}>
                                                    <FilePlus size={12} /> {filesAdded} added
                                                </span>
                                            )}
                                            {filesModified > 0 && (
                                                <span className={cn(
                                                    "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border",
                                                    isDarkMode ? "bg-blue-500/10 text-blue-400 border-blue-500/20" : "bg-blue-50 text-blue-700 border-blue-200"
                                                )}>
                                                    <FileEdit size={12} /> {filesModified} modified
                                                </span>
                                            )}
                                            {filesDeleted > 0 && (
                                                <span className={cn(
                                                    "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border",
                                                    isDarkMode ? "bg-red-500/10 text-red-400 border-red-500/20" : "bg-red-50 text-red-700 border-red-200"
                                                )}>
                                                    <FileMinus size={12} /> {filesDeleted} deleted
                                                </span>
                                            )}
                                        </motion.div>
                                    )}

                                    {/* ── Overall progress bar ────────────────────── */}
                                    <div className="space-y-2">
                                        <div className="flex justify-between items-center text-xs">
                                            <span className={cn("font-medium", isDarkMode ? 'text-gray-400' : 'text-gray-500')}>
                                                {totalFiles > 0
                                                    ? `Processing file ${fileIndex} of ${totalFiles}`
                                                    : syncState === 'complete'
                                                        ? 'Complete'
                                                        : `Step ${Math.min(activeStepIndex + 1, PIPELINE_STEPS.length)} of ${PIPELINE_STEPS.length}`
                                                }
                                            </span>
                                            <span className={cn("font-mono font-bold", isDarkMode ? "text-gray-200" : "text-gray-900")}>
                                                {Math.round(syncState === 'complete' ? 100 : progress)}%
                                            </span>
                                        </div>
                                        <div
                                            className={cn(
                                                "h-1.5 rounded-full overflow-hidden",
                                                isDarkMode ? 'bg-gray-800' : 'bg-gray-200'
                                            )}
                                        >
                                            <motion.div
                                                className={cn(
                                                    "h-full rounded-full transition-colors",
                                                    syncState === 'error' ? "bg-red-500" :
                                                        (isDarkMode ? "bg-gray-200" : "bg-gray-900")
                                                )}
                                                animate={{ width: `${syncState === 'complete' ? 100 : progress}%` }}
                                                transition={{ duration: 0.3, ease: 'easeOut' }}
                                            />
                                        </div>
                                    </div>

                                    {/* ── Current file indicator ──────────────────── */}
                                    {currentFile && syncState === 'syncing' && (
                                        <div className="flex items-center justify-center">
                                            <motion.div
                                                key={currentFile}
                                                initial={{ opacity: 0 }}
                                                animate={{ opacity: 1 }}
                                                className={cn(
                                                    "flex items-center gap-2 px-3 py-1.5 rounded-md text-[11px] font-mono max-w-full overflow-hidden border",
                                                    isDarkMode ? 'bg-gray-900 border-gray-800 text-gray-400' : 'bg-gray-50 border-gray-200 text-gray-600'
                                                )}
                                            >
                                                <Search size={12} className="shrink-0" />
                                                <span className="truncate">{currentFile}</span>
                                            </motion.div>
                                        </div>
                                    )}

                                    {/* ── Status message ──────────────────────────── */}
                                    <motion.div
                                        key={message}
                                        initial={{ opacity: 0 }}
                                        animate={{ opacity: 1 }}
                                        className={cn(
                                            "text-center text-sm font-medium",
                                            syncState === 'complete' ? (isDarkMode ? 'text-gray-200' : 'text-gray-900') :
                                                syncState === 'error' ? 'text-red-500' :
                                                    (isDarkMode ? 'text-gray-400' : 'text-gray-600')
                                        )}
                                    >
                                        {message}
                                    </motion.div>

                                    {/* ── Live log feed ───────────────────────────── */}
                                    <div
                                        className={cn(
                                            "rounded-xl border overflow-hidden flex flex-col items-center",
                                            isDarkMode ? 'bg-[#141414] border-[#2e2e2e]' : 'bg-gray-50 border-gray-200'
                                        )}
                                    >
                                        <div className="w-full h-40 overflow-y-auto p-4 font-mono text-[11px] leading-relaxed space-y-1 scrollbar-hide">
                                            {logs.map((l, i) => (
                                                <div key={i} className="flex gap-3">
                                                    <span className={cn("shrink-0", isDarkMode ? 'text-gray-600' : 'text-gray-400')}>{l.time}</span>
                                                    <span className={
                                                        l.type === 'error' ? 'text-red-500' :
                                                            l.type === 'success' ? (isDarkMode ? 'text-emerald-400' : 'text-emerald-600') :
                                                                (isDarkMode ? 'text-gray-300' : 'text-gray-700')
                                                    }>
                                                        {l.msg}
                                                    </span>
                                                </div>
                                            ))}
                                            <div ref={logsEndRef} />
                                        </div>
                                    </div>

                                    {/* ── Actions ─────────────────────────────────── */}
                                    {(syncState === 'complete' || syncState === 'error') && (
                                        <motion.div
                                            className="flex justify-center gap-3 pt-2"
                                            initial={{ opacity: 0, y: 5 }}
                                            animate={{ opacity: 1, y: 0 }}
                                        >
                                            {syncState === 'error' && (
                                                <button
                                                    onClick={startSync}
                                                    className={cn(
                                                        "px-5 py-2 rounded-lg text-sm font-medium transition-colors border",
                                                        isDarkMode
                                                            ? "bg-gray-800 border-gray-700 text-white hover:bg-gray-700"
                                                            : "bg-white border-gray-300 text-gray-900 hover:bg-gray-50"
                                                    )}
                                                >
                                                    <span className="flex items-center gap-2">
                                                        <RefreshCw size={14} /> Retry
                                                    </span>
                                                </button>
                                            )}
                                            <button
                                                onClick={handleClose}
                                                className={cn(
                                                    "px-6 py-2 rounded-lg text-sm font-medium transition-colors",
                                                    isDarkMode
                                                        ? "bg-white text-black hover:bg-gray-200"
                                                        : "bg-gray-900 text-white hover:bg-black"
                                                )}
                                            >
                                                Done
                                            </button>
                                        </motion.div>
                                    )}
                                </div>
                            )}
                        </div>
                    </motion.div>
                </div>
            )}
        </AnimatePresence>
    );
}
