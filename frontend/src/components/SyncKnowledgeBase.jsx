import { useState, useEffect, useRef, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
    Search, FileCode, Sparkles, Database, BarChart3, GitBranch,
    CheckCircle2, XCircle, Loader2, X, RefreshCw, FilePlus, FileMinus, FileEdit,
    Zap, ArrowRight
} from 'lucide-react';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

/* ─── Pipeline step definitions (must match backend STEPS) ──────── */
const PIPELINE_STEPS = [
    { key: 'scanning', label: 'Scanning Files', icon: Search, color: '#6366f1' },
    { key: 'parsing', label: 'Parsing & Chunking', icon: FileCode, color: '#8b5cf6' },
    { key: 'descriptions', label: 'Generating Descriptions', icon: Sparkles, color: '#a855f7' },
    { key: 'embedding', label: 'Embedding to Vector DB', icon: Database, color: '#d946ef' },
    { key: 'bm25', label: 'Rebuilding BM25 Indices', icon: BarChart3, color: '#ec4899' },
    { key: 'graph', label: 'Updating Knowledge Graph', icon: GitBranch, color: '#f43f5e' },
];

/* ─── Particle burst for completion ─────────────────────────────── */
function SuccessParticles() {
    const particles = Array.from({ length: 30 }, (_, i) => ({
        id: i,
        x: Math.random() * 360 - 180,
        y: Math.random() * -300 - 50,
        rotate: Math.random() * 720 - 360,
        scale: Math.random() * 0.6 + 0.4,
        delay: Math.random() * 0.3,
        color: ['#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e', '#fbbf24', '#34d399'][
            Math.floor(Math.random() * 8)
        ],
    }));

    return (
        <div className="absolute inset-0 pointer-events-none overflow-hidden">
            {particles.map((p) => (
                <motion.div
                    key={p.id}
                    initial={{ opacity: 1, x: 0, y: 0, scale: 0, rotate: 0 }}
                    animate={{ opacity: 0, x: p.x, y: p.y, scale: p.scale, rotate: p.rotate }}
                    transition={{ duration: 1.5, delay: p.delay, ease: 'easeOut' }}
                    className="absolute left-1/2 top-1/2"
                    style={{ width: 8, height: 8, borderRadius: '50%', backgroundColor: p.color }}
                />
            ))}
        </div>
    );
}

/* ─── Animated orbital ring behind the active step icon ──────── */
function PulseRing({ color }) {
    return (
        <>
            <motion.div
                className="absolute inset-0 rounded-full"
                style={{ border: `2px solid ${color}` }}
                animate={{ scale: [1, 1.8, 1.8], opacity: [0.6, 0, 0] }}
                transition={{ duration: 1.8, repeat: Infinity, ease: 'easeOut' }}
            />
            <motion.div
                className="absolute inset-0 rounded-full"
                style={{ border: `2px solid ${color}` }}
                animate={{ scale: [1, 1.5, 1.5], opacity: [0.4, 0, 0] }}
                transition={{ duration: 1.8, repeat: Infinity, ease: 'easeOut', delay: 0.6 }}
            />
        </>
    );
}

/* ─── Single step node ──────────────────────────────────────────── */
function StepNode({ step, index, activeIndex, isComplete, isError }) {
    const Icon = step.icon;
    const isActive = index === activeIndex;
    const isPast = index < activeIndex || isComplete;
    const isFuture = index > activeIndex && !isComplete;

    return (
        <motion.div
            className="flex flex-col items-center gap-2 relative"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.08, duration: 0.4 }}
        >
            {/* Connector line (to previous) */}
            {index > 0 && (
                <div className="absolute -left-full top-6 w-full h-0.5 -translate-y-1/2 z-0">
                    <motion.div
                        className="h-full rounded-full"
                        initial={{ scaleX: 0 }}
                        animate={{ scaleX: isPast ? 1 : 0.3 }}
                        transition={{ duration: 0.5 }}
                        style={{
                            transformOrigin: 'left',
                            height: '100%',
                            borderRadius: 999,
                            background: isPast
                                ? `linear-gradient(90deg, ${PIPELINE_STEPS[index - 1].color}, ${step.color})`
                                : 'rgba(148,163,184,0.15)',
                        }}
                    />
                </div>
            )}

            {/* Icon circle */}
            <div className="relative z-10">
                {isActive && <PulseRing color={step.color} />}
                <motion.div
                    className="w-12 h-12 rounded-full flex items-center justify-center relative"
                    animate={{
                        backgroundColor: isPast
                            ? step.color
                            : isActive
                                ? `${step.color}22`
                                : 'rgba(148,163,184,0.08)',
                        boxShadow: isActive
                            ? `0 0 20px ${step.color}44, 0 0 40px ${step.color}22`
                            : isPast
                                ? `0 0 12px ${step.color}33`
                                : 'none',
                    }}
                    transition={{ duration: 0.4 }}
                >
                    {isPast && !isError ? (
                        <motion.div initial={{ scale: 0 }} animate={{ scale: 1 }} transition={{ type: 'spring', stiffness: 400 }}>
                            <CheckCircle2 size={22} className="text-white" />
                        </motion.div>
                    ) : isError && isActive ? (
                        <XCircle size={22} style={{ color: '#ef4444' }} />
                    ) : isActive ? (
                        <motion.div animate={{ rotate: 360 }} transition={{ duration: 2, repeat: Infinity, ease: 'linear' }}>
                            <Loader2 size={22} style={{ color: step.color }} />
                        </motion.div>
                    ) : (
                        <Icon size={20} style={{ color: isFuture ? 'rgba(148,163,184,0.35)' : step.color }} />
                    )}
                </motion.div>
            </div>

            {/* Label */}
            <span
                className="text-xs font-medium text-center leading-tight max-w-[80px]"
                style={{
                    color: isPast || isActive ? step.color : 'rgba(148,163,184,0.5)',
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
        setMessage('Starting sync…');
        setStepErrors(new Set());
        setLogs([]);
        addLog('Starting knowledge base sync…', 'info');

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
                            addLog('✨ Sync complete!', 'success');
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
                <motion.div
                    className="fixed inset-0 z-50 flex items-center justify-center p-4"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                >
                    {/* Backdrop */}
                    <motion.div
                        className="absolute inset-0 bg-black/60 backdrop-blur-sm"
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        onClick={syncState !== 'syncing' ? handleClose : undefined}
                    />

                    {/* Modal */}
                    <motion.div
                        className="relative w-full max-w-3xl max-h-[90vh] overflow-hidden rounded-2xl shadow-2xl"
                        style={{
                            background: isDarkMode
                                ? 'linear-gradient(145deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%)'
                                : 'linear-gradient(145deg, #ffffff 0%, #f5f3ff 50%, #ffffff 100%)',
                        }}
                        initial={{ scale: 0.9, y: 30 }}
                        animate={{ scale: 1, y: 0 }}
                        exit={{ scale: 0.9, y: 30 }}
                        transition={{ type: 'spring', damping: 25, stiffness: 300 }}
                    >
                        {/* Completion particles */}
                        {syncState === 'complete' && <SuccessParticles />}

                        {/* Decorative gradient border */}
                        <div className="absolute inset-0 rounded-2xl p-[1px] pointer-events-none">
                            <div
                                className="w-full h-full rounded-2xl"
                                style={{
                                    background: syncState === 'syncing'
                                        ? 'linear-gradient(135deg, rgba(99,102,241,0.3), rgba(168,85,247,0.3), rgba(236,72,153,0.3))'
                                        : syncState === 'complete'
                                            ? 'linear-gradient(135deg, rgba(52,211,153,0.3), rgba(59,130,246,0.3))'
                                            : 'transparent',
                                }}
                            />
                        </div>

                        {/* Header */}
                        <div className="relative flex items-center justify-between px-6 pt-6 pb-2">
                            <div className="flex items-center gap-3">
                                <div
                                    className="w-10 h-10 rounded-xl flex items-center justify-center"
                                    style={{
                                        background: 'linear-gradient(135deg, #6366f1, #a855f7)',
                                        boxShadow: '0 4px 15px rgba(99,102,241,0.3)',
                                    }}
                                >
                                    <RefreshCw size={20} className="text-white" />
                                </div>
                                <div>
                                    <h2 className={`text-lg font-bold ${isDarkMode ? 'text-white' : 'text-gray-900'}`}>
                                        Sync Knowledge Base
                                    </h2>
                                    <p className={`text-xs ${isDarkMode ? 'text-gray-400' : 'text-gray-500'}`}>
                                        {syncState === 'idle' && 'Detect changes and update the knowledge pipeline'}
                                        {syncState === 'syncing' && 'Pipeline running…'}
                                        {syncState === 'complete' && 'All changes synced successfully!'}
                                        {syncState === 'error' && 'Sync encountered an error'}
                                    </p>
                                </div>
                            </div>
                            <button
                                onClick={handleClose}
                                disabled={syncState === 'syncing'}
                                className={`p-2 rounded-lg transition-colors ${syncState === 'syncing'
                                    ? 'opacity-30 cursor-not-allowed'
                                    : isDarkMode
                                        ? 'hover:bg-white/10 text-gray-400'
                                        : 'hover:bg-gray-100 text-gray-500'
                                    }`}
                            >
                                <X size={20} />
                            </button>
                        </div>

                        {/* Content scrollable zone */}
                        <div className="relative px-6 pb-6 overflow-y-auto max-h-[calc(90vh-100px)]">

                            {/* ── Idle state: Start button ────────────────────── */}
                            {syncState === 'idle' && (
                                <motion.div
                                    className="flex flex-col items-center py-12 gap-6"
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                >
                                    <motion.div
                                        className="w-24 h-24 rounded-full flex items-center justify-center"
                                        style={{
                                            background: 'linear-gradient(135deg, rgba(99,102,241,0.1), rgba(168,85,247,0.1))',
                                            border: '2px dashed rgba(99,102,241,0.3)',
                                        }}
                                        animate={{ rotate: [0, 5, -5, 0] }}
                                        transition={{ duration: 4, repeat: Infinity, ease: 'easeInOut' }}
                                    >
                                        <RefreshCw size={40} style={{ color: '#6366f1' }} />
                                    </motion.div>

                                    <div className="text-center space-y-2">
                                        <p className={`text-sm ${isDarkMode ? 'text-gray-300' : 'text-gray-600'}`}>
                                            This will scan your codebase for changes and run the full ingestion pipeline.
                                        </p>
                                        <div className="flex items-center gap-2 justify-center flex-wrap">
                                            {PIPELINE_STEPS.map((s, i) => (
                                                <span key={s.key} className="flex items-center gap-1 text-xs" style={{ color: s.color }}>
                                                    <s.icon size={12} />
                                                    {s.label}
                                                    {i < PIPELINE_STEPS.length - 1 && (
                                                        <ArrowRight size={10} className="text-gray-400 mx-0.5" />
                                                    )}
                                                </span>
                                            ))}
                                        </div>
                                    </div>

                                    <motion.button
                                        onClick={startSync}
                                        className="px-8 py-3 rounded-xl text-white font-semibold text-sm"
                                        style={{
                                            background: 'linear-gradient(135deg, #6366f1, #a855f7)',
                                            boxShadow: '0 4px 20px rgba(99,102,241,0.4)',
                                        }}
                                        whileHover={{ scale: 1.05, boxShadow: '0 6px 25px rgba(99,102,241,0.5)' }}
                                        whileTap={{ scale: 0.97 }}
                                    >
                                        <span className="flex items-center gap-2">
                                            <Zap size={16} />
                                            Start Sync
                                        </span>
                                    </motion.button>
                                </motion.div>
                            )}

                            {/* ── Active / Complete / Error state ─────────────── */}
                            {syncState !== 'idle' && (
                                <div className="space-y-6 pt-4">

                                    {/* File change summary chips */}
                                    {(filesAdded > 0 || filesModified > 0 || filesDeleted > 0) && (
                                        <motion.div
                                            className="flex justify-center gap-3 flex-wrap"
                                            initial={{ opacity: 0, y: -10 }}
                                            animate={{ opacity: 1, y: 0 }}
                                        >
                                            {filesAdded > 0 && (
                                                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-500 ring-1 ring-emerald-500/20">
                                                    <FilePlus size={12} /> {filesAdded} added
                                                </span>
                                            )}
                                            {filesModified > 0 && (
                                                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-500 ring-1 ring-amber-500/20">
                                                    <FileEdit size={12} /> {filesModified} modified
                                                </span>
                                            )}
                                            {filesDeleted > 0 && (
                                                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-500 ring-1 ring-red-500/20">
                                                    <FileMinus size={12} /> {filesDeleted} deleted
                                                </span>
                                            )}
                                        </motion.div>
                                    )}

                                    {/* ── Pipeline stepper ────────────────────────── */}
                                    <div className="relative">
                                        <div className="flex items-start justify-between gap-2 px-2">
                                            {PIPELINE_STEPS.map((step, i) => (
                                                <StepNode
                                                    key={step.key}
                                                    step={step}
                                                    index={i}
                                                    activeIndex={activeStepIndex}
                                                    isComplete={syncState === 'complete'}
                                                    isError={stepErrors.has(step.key)}
                                                />
                                            ))}
                                        </div>
                                    </div>

                                    {/* ── Overall progress bar ────────────────────── */}
                                    <div className="space-y-2">
                                        <div className="flex justify-between items-center text-xs">
                                            <span className={isDarkMode ? 'text-gray-400' : 'text-gray-500'}>
                                                {totalFiles > 0
                                                    ? `Processing file ${Math.min(fileIndex, totalFiles)} of ${totalFiles}`
                                                    : `Step ${Math.min(activeStepIndex + 1, PIPELINE_STEPS.length)} of ${PIPELINE_STEPS.length}`
                                                }
                                            </span>
                                            <span className="font-mono font-bold" style={{ color: '#a855f7' }}>
                                                {Math.round(syncState === 'complete' ? 100 : progress)}%
                                            </span>
                                        </div>
                                        <div
                                            className="h-2 rounded-full overflow-hidden"
                                            style={{ background: isDarkMode ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' }}
                                        >
                                            <motion.div
                                                className="h-full rounded-full"
                                                style={{
                                                    background: syncState === 'complete'
                                                        ? 'linear-gradient(90deg, #34d399, #6366f1)'
                                                        : syncState === 'error'
                                                            ? 'linear-gradient(90deg, #ef4444, #f97316)'
                                                            : 'linear-gradient(90deg, #6366f1, #a855f7, #ec4899)',
                                                }}
                                                animate={{ width: `${syncState === 'complete' ? 100 : progress}%` }}
                                                transition={{ duration: 0.5, ease: 'easeOut' }}
                                            />
                                        </div>
                                    </div>

                                    {/* ── Current file indicator ──────────────────── */}
                                    {currentFile && syncState === 'syncing' && (
                                        <motion.div
                                            key={currentFile}
                                            initial={{ opacity: 0, x: -10 }}
                                            animate={{ opacity: 1, x: 0 }}
                                            className={`flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-mono ${isDarkMode ? 'bg-white/5 text-violet-300' : 'bg-violet-50 text-violet-700'
                                                }`}
                                        >
                                            <FileCode size={14} />
                                            <span className="truncate">{currentFile}</span>
                                        </motion.div>
                                    )}

                                    {/* ── Status message ──────────────────────────── */}
                                    <motion.div
                                        key={message}
                                        initial={{ opacity: 0 }}
                                        animate={{ opacity: 1 }}
                                        className={`text-center text-sm font-medium ${syncState === 'complete'
                                            ? 'text-emerald-500'
                                            : syncState === 'error'
                                                ? 'text-red-400'
                                                : isDarkMode ? 'text-gray-300' : 'text-gray-600'
                                            }`}
                                    >
                                        {message}
                                    </motion.div>

                                    {/* ── Live log feed ───────────────────────────── */}
                                    <div
                                        className={`rounded-xl border overflow-hidden ${isDarkMode
                                            ? 'bg-gray-950/60 border-gray-800'
                                            : 'bg-gray-50 border-gray-200'
                                            }`}
                                    >
                                        <div className={`flex items-center gap-2 px-3 py-1.5 text-xs font-semibold border-b ${isDarkMode ? 'border-gray-800 text-gray-400' : 'border-gray-200 text-gray-500'
                                            }`}>
                                            <div className="flex gap-1">
                                                <div className="w-2 h-2 rounded-full bg-red-400" />
                                                <div className="w-2 h-2 rounded-full bg-yellow-400" />
                                                <div className="w-2 h-2 rounded-full bg-green-400" />
                                            </div>
                                            Pipeline Logs
                                        </div>
                                        <div className="h-32 overflow-y-auto p-3 font-mono text-[11px] leading-relaxed space-y-0.5 scrollbar-hide">
                                            {logs.map((l, i) => (
                                                <div key={i} className="flex gap-2">
                                                    <span className={isDarkMode ? 'text-gray-600' : 'text-gray-400'}>{l.time}</span>
                                                    <span className={
                                                        l.type === 'error' ? 'text-red-400'
                                                            : l.type === 'success' ? 'text-emerald-400'
                                                                : isDarkMode ? 'text-gray-300' : 'text-gray-600'
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
                                            initial={{ opacity: 0, y: 10 }}
                                            animate={{ opacity: 1, y: 0 }}
                                        >
                                            {syncState === 'error' && (
                                                <motion.button
                                                    onClick={startSync}
                                                    className="px-5 py-2 rounded-lg text-sm font-medium text-white"
                                                    style={{ background: 'linear-gradient(135deg, #6366f1, #a855f7)' }}
                                                    whileHover={{ scale: 1.03 }}
                                                    whileTap={{ scale: 0.97 }}
                                                >
                                                    <span className="flex items-center gap-2">
                                                        <RefreshCw size={14} /> Retry
                                                    </span>
                                                </motion.button>
                                            )}
                                            <motion.button
                                                onClick={handleClose}
                                                className={`px-5 py-2 rounded-lg text-sm font-medium ${isDarkMode
                                                    ? 'bg-white/10 text-gray-200 hover:bg-white/20'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                                    }`}
                                                whileHover={{ scale: 1.03 }}
                                                whileTap={{ scale: 0.97 }}
                                            >
                                                Close
                                            </motion.button>
                                        </motion.div>
                                    )}
                                </div>
                            )}
                        </div>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
