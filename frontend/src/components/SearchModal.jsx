import React, { useState, useEffect, useRef } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Search, X, MessageSquare, Edit } from 'lucide-react';
import { cn } from '../lib/utils';
import { useAuth } from '../contexts/AuthContext';

function SearchModal({
    isOpen,
    onClose,
    isDarkMode,
    loadConversation,
    createNewConversation,
}) {
    const [searchQuery, setSearchQuery] = useState('');
    const [conversations, setConversations] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const { user } = useAuth();
    const inputRef = useRef(null);

    // Focus input when modal opens
    useEffect(() => {
        if (isOpen) {
            setTimeout(() => {
                inputRef.current?.focus();
            }, 100);
        } else {
            setSearchQuery('');
            setConversations([]);
        }
    }, [isOpen]);

    // Handle keyboard shortcuts (Escape to close)
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (e.key === 'Escape' && isOpen) {
                onClose();
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [isOpen, onClose]);

    // Handle debounced search
    useEffect(() => {
        if (!isOpen) return;

        const fetchResults = async () => {
            const token = localStorage.getItem('token');
            if (!token || !user) return;

            setIsLoading(true);
            try {
                const url = new URL('http://localhost:8000/api/chat/conversations');
                if (searchQuery.trim()) {
                    url.searchParams.append('search', searchQuery.trim());
                }

                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    setConversations(data.slice(0, 5));
                }
            } catch (error) {
                console.error('Error searching conversations:', error);
            } finally {
                setIsLoading(false);
            }
        };

        const timer = setTimeout(() => {
            fetchResults();
        }, 300);

        return () => clearTimeout(timer);
    }, [searchQuery, isOpen, user]);

    // Group conversations by time
    const groupedConversations = React.useMemo(() => {
        if (!conversations.length) return {};

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        const previous7Days = new Date(today);
        previous7Days.setDate(previous7Days.getDate() - 7);

        // If there's an active search query, don't group by time
        if (searchQuery) {
            return {
                'Search Results': conversations
            };
        }

        const groups = {
            Today: [],
            Yesterday: [],
            'Previous 7 Days': [],
            Older: []
        };

        conversations.forEach(conv => {
            const convDate = new Date(conv.updated_at);
            if (convDate >= today) {
                groups.Today.push(conv);
            } else if (convDate >= yesterday) {
                groups.Yesterday.push(conv);
            } else if (convDate >= previous7Days) {
                groups['Previous 7 Days'].push(conv);
            } else {
                groups.Older.push(conv);
            }
        });

        // Remove empty groups
        return Object.fromEntries(
            Object.entries(groups).filter(([_, arr]) => arr.length > 0)
        );
    }, [conversations, searchQuery]);

    if (!isOpen) return null;

    return (
        <AnimatePresence>
            <div className="fixed inset-0 z-50 flex items-start justify-center pt-[10vh]">
                {/* Backdrop */}
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.2 }}
                    className="absolute inset-0 bg-black/60 backdrop-blur-sm"
                    onClick={onClose}
                />

                {/* Modal Window */}
                <motion.div
                    initial={{ opacity: 0, scale: 0.95, y: -20 }}
                    animate={{ opacity: 1, scale: 1, y: 0 }}
                    exit={{ opacity: 0, scale: 0.95, y: -20 }}
                    transition={{ duration: 0.2, ease: "easeOut" }}
                    className={cn(
                        "relative w-full max-w-[600px] border shadow-2xl rounded-2xl overflow-hidden mx-4 pb-2",
                        isDarkMode
                            ? "bg-[#1e1e1e] border-[#3e3e3e]"
                            : "bg-white border-gray-100"
                    )}
                >
                    {/* Search Header */}
                    <div className={cn(
                        "flex items-center px-4 py-3 border-b",
                        isDarkMode ? "border-[#2e2e2e]" : "border-gray-100"
                    )}>
                        <Search className={cn(
                            "w-5 h-5 shrink-0",
                            isDarkMode ? "text-gray-400" : "text-gray-500"
                        )} />
                        <input
                            ref={inputRef}
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder="Search chats..."
                            className={cn(
                                "flex-1 bg-transparent border-none outline-none mx-3 text-base placeholder-gray-500",
                                isDarkMode ? "text-gray-200" : "text-gray-800"
                            )}
                        />
                        <button
                            onClick={onClose}
                            className={cn(
                                "p-1 rounded-md transition-colors",
                                isDarkMode
                                    ? "text-gray-500 hover:text-gray-300 hover:bg-[#2e2e2e]"
                                    : "text-gray-400 hover:text-gray-600 hover:bg-gray-100"
                            )}
                        >
                            <X className="w-5 h-5" />
                        </button>
                    </div>

                    {/* Results Area */}
                    <div className="max-h-[60vh] overflow-y-auto px-2 py-3 scrollbar-hide">

                        {/* New Chat Button */}
                        {!searchQuery && (
                            <div
                                className={cn(
                                    "flex items-center gap-3 px-3 py-3 mx-2 mb-4 cursor-pointer font-medium rounded-xl border transition-colors",
                                    isDarkMode
                                        ? "text-gray-200 border-[#3e3e3e] hover:bg-[#2e2e2e]"
                                        : "text-gray-700 border-gray-100 hover:bg-gray-50"
                                )}
                                onClick={() => {
                                    createNewConversation();
                                    onClose();
                                }}
                            >
                                <Edit className="w-4 h-4 text-gray-400" />
                                <span>New chat</span>
                            </div>
                        )}

                        {/* Empty State / Loading */}
                        {isLoading ? (
                            <div className="px-6 py-6 text-sm text-gray-500 text-center animate-pulse">
                                Searching knowledge base...
                            </div>
                        ) : conversations.length === 0 ? (
                            searchQuery ? (
                                <div className="px-6 py-6 text-sm text-gray-500 text-center">
                                    No results found for "{searchQuery}"
                                </div>
                            ) : (
                                <div className="px-6 py-6 text-sm text-gray-500 text-center">
                                    You have no chat history.
                                </div>
                            )
                        ) : (
                            /* Grouped Results list */
                            <div className="space-y-6">
                                {Object.entries(groupedConversations).map(([groupName, groupConvs]) => (
                                    <div key={groupName} className="px-2">
                                        {!searchQuery && (
                                            <h3 className="px-2 pb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                {groupName}
                                            </h3>
                                        )}
                                        <div className="space-y-[2px]">
                                            {groupConvs.map((conv) => (
                                                <div
                                                    key={conv.id}
                                                    className={cn(
                                                        "flex items-center gap-3 px-3 py-3 rounded-xl cursor-pointer transition-colors group",
                                                        isDarkMode ? "hover:bg-[#2a2a2a]" : "hover:bg-gray-50"
                                                    )}
                                                    onClick={() => {
                                                        loadConversation(conv.id);
                                                        onClose();
                                                    }}
                                                >
                                                    <MessageSquare className={cn(
                                                        "w-4 h-4 shrink-0 transition-colors",
                                                        isDarkMode
                                                            ? "text-gray-500 group-hover:text-gray-300"
                                                            : "text-gray-400 group-hover:text-gray-600"
                                                    )} />
                                                    <div className={cn(
                                                        "flex-1 min-w-0 text-[15px] truncate",
                                                        isDarkMode ? "text-gray-200" : "text-gray-800"
                                                    )}>
                                                        {conv.title || "Untitled Conversation"}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </motion.div>
            </div>
        </AnimatePresence>
    );
}

export default SearchModal;
