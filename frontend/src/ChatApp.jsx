import { useState, useRef, useEffect } from 'react';
import { Send, Menu, Bot, User, Shield, Code, ChevronLeft, Database, FileText, FileCode, Plus, Trash2, MessageSquare, PanelLeftClose, PanelLeft, Moon, Sun, LogOut, Activity, RefreshCw, Search } from 'lucide-react';
import ReactMarkdown from 'react-markdown';
import { RotatingCube } from './components/RotatingCube';
import { BackgroundEffects } from './components/BackgroundEffects';
import { MermaidDiagram } from './components/MermaidDiagram';
import CodeReview from './components/CodeReview';
import InferenceLogs from './components/InferenceLogs';
import SyncKnowledgeBase from './components/SyncKnowledgeBase';
import SearchModal from './components/SearchModal';
import { useAuth } from './contexts/AuthContext';

import { cn } from './lib/utils';
import { motion, AnimatePresence } from 'framer-motion';

function ChatApp() {
  const { user, logout, loading: authLoading } = useAuth();
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);
  const [activeView, setActiveView] = useState('chat'); // 'chat', 'code-review', or 'inference-logs'
  const [showSyncModal, setShowSyncModal] = useState(false);
  const [isSearchModalOpen, setIsSearchModalOpen] = useState(false);
  const [isDarkMode, setIsDarkMode] = useState(() => {
    if (typeof window !== 'undefined') {
      const saved = localStorage.getItem('theme');
      if (saved) return saved === 'dark';
      return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    return false;
  });

  // Helper function to get auth headers
  const getAuthHeaders = () => {
    const token = user ? localStorage.getItem('token') : null;
    if (!token) {
      console.warn('No authentication token available');
      return {
        'Content-Type': 'application/json'
      };
    }
    console.log('Token from localStorage:', token ? `${token.substring(0, 20)}...` : 'null');
    return {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    };
  };

  useEffect(() => {
    if (isDarkMode) {
      document.documentElement.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem('theme', 'light');
    }
  }, [isDarkMode]);

  const [selectedContext, setSelectedContext] = useState('smart'); // smart, business, php, js, blade
  const [messages, setMessages] = useState([
    {
      id: 1,
      role: 'bot',
      content: 'Hello! I am your advanced banking assistant with smart routing. Ask your question and I will automatically select the best knowledge base for you.'
    }
  ]);
  const [inputValue, setInputValue] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [hasUserInteracted, setHasUserInteracted] = useState(false);
  const messagesEndRef = useRef(null);

  // Chat history state
  const [conversations, setConversations] = useState([]);
  const [currentConversationId, setCurrentConversationId] = useState(null);
  const [loadingConversations, setLoadingConversations] = useState(true);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages, isLoading]);

  // Load conversations on mount
  useEffect(() => {
    if (user && !authLoading) {
      loadConversations();
    }
  }, [user, authLoading]);

  // Load conversations from API
  const loadConversations = async (search = '') => {
    const token = localStorage.getItem('token');
    if (!token || !user) {
      console.log('No token or user found, skipping conversation load');
      setLoadingConversations(false);
      return;
    }

    try {
      setLoadingConversations(true);
      const headers = getAuthHeaders();
      console.log('Request headers:', headers);

      const url = new URL('http://localhost:8000/api/chat/conversations');
      if (search) {
        url.searchParams.append('search', search);
      }

      const response = await fetch(url, {
        headers: headers
      });

      console.log('Response status:', response.status);

      if (response.status === 401) {
        console.error('Authentication failed - token may be expired');
        logout();
        setLoadingConversations(false);
        return;
      }

      if (response.ok) {
        const data = await response.json();
        setConversations(data);
      }
    } catch (error) {
      console.error('Error loading conversations:', error);
    } finally {
      setLoadingConversations(false);
    }
  };

  // Create a new conversation
  const createNewConversation = async () => {
    // Don't create empty conversations - just reset the UI
    // A conversation will be created automatically when the user sends the first message
    setCurrentConversationId(null);
    setMessages([{
      id: Date.now(),
      role: 'bot',
      content: `Hello! I am your advanced banking assistant. This is a new ${selectedContext} conversation. How can I help you?`
    }]);
    setHasUserInteracted(false);
  };

  // Load a specific conversation
  const loadConversation = async (conversationId) => {
    if (!user) {
      console.warn('Cannot load conversation: user not authenticated');
      return;
    }

    try {
      const response = await fetch(`http://localhost:8000/api/chat/conversations/${conversationId}`, {
        headers: getAuthHeaders()
      });

      if (response.status === 401) {
        console.error('Authentication failed when loading conversation');
        logout();
        return;
      }

      if (response.ok) {
        const data = await response.json();
        setCurrentConversationId(conversationId);
        setSelectedContext(data.context_type);

        // Convert messages to frontend format
        const loadedMessages = data.messages.map(msg => ({
          id: msg.id,
          role: msg.role,
          content: msg.content,
          context_used: msg.context_used
        }));

        if (loadedMessages.length === 0) {
          // If no messages, add welcome message
          setMessages([{
            id: Date.now(),
            role: 'bot',
            content: `Hello! I am your advanced banking assistant. This is a ${data.context_type} conversation. How can I help you?`
          }]);
        } else {
          setMessages(loadedMessages);
        }

        setHasUserInteracted(loadedMessages.length > 0);
      }
    } catch (error) {
      console.error('Error loading conversation:', error);
    }
  };

  // Delete a conversation
  const deleteConversation = async (conversationId, event) => {
    event.stopPropagation(); // Prevent triggering loadConversation

    if (!confirm('Are you sure you want to delete this conversation?')) {
      return;
    }

    try {
      const response = await fetch(`http://localhost:8000/api/chat/conversations/${conversationId}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      });

      if (response.ok) {
        setConversations(prev => prev.filter(c => c.id !== conversationId));

        // If we deleted the current conversation, reset
        if (conversationId === currentConversationId) {
          setCurrentConversationId(null);
          setMessages([{
            id: 1,
            role: 'bot',
            content: 'Hello! I am your advanced banking assistant. Please select a knowledge base context and ask your question.'
          }]);
          setHasUserInteracted(false);
        }
      }
    } catch (error) {
      console.error('Error deleting conversation:', error);
    }
  };

  // Context Options
  const contexts = [
    { id: 'smart', label: 'Smart Routing', icon: <Shield size={18} />, description: 'AI auto-selects best source' },
    { id: 'business', label: 'Business Docs', icon: <FileText size={18} /> },
    { id: 'php', label: 'PHP Knowledge', icon: <Database size={18} /> },
    { id: 'js', label: 'JS Knowledge', icon: <Code size={18} /> },
    { id: 'blade', label: 'Blade Templates', icon: <FileCode size={18} /> },
  ];

  const handleSendMessage = async (e) => {
    e.preventDefault();
    if (!inputValue.trim() || isLoading) return;

    const newMessage = {
      id: Date.now(),
      role: 'user',
      content: inputValue
    };

    if (!hasUserInteracted) {
      setHasUserInteracted(true);
    }

    setMessages(prev => [...prev, newMessage]);
    setInputValue('');
    setIsLoading(true);

    try {
      // Create conversation if it doesn't exist
      let convId = currentConversationId;
      if (!convId) {
        const convResponse = await fetch('http://localhost:8000/api/chat/conversations', {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify({
            title: inputValue.length > 60 ? inputValue.substring(0, 60) + '...' : inputValue,  // Use first message as title
            context_type: selectedContext
          })
        });

        if (convResponse.ok) {
          const newConv = await convResponse.json();
          convId = newConv.id;
          setCurrentConversationId(convId);
          setConversations(prev => [newConv, ...prev]);
        }
      } else {
        // If conversation exists but title is still "New Conversation", update it
        const currentConv = conversations.find(c => c.id === convId);
        if (currentConv && currentConv.title === 'New Conversation') {
          try {
            await fetch(`http://localhost:8000/api/chat/conversations/${convId}`, {
              method: 'PATCH',
              headers: getAuthHeaders(),
              body: JSON.stringify({
                title: inputValue.length > 60 ? inputValue.substring(0, 60) + '...' : inputValue
              })
            });
          } catch (error) {
            console.error('Error updating conversation title:', error);
          }
        }
      }

      const response = await fetch(`http://localhost:8000/inference/${selectedContext}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          query: newMessage.content,
          top_k: 5,
          rerank: true,
          conversation_id: convId  // Include conversation ID
        })
      });

      if (!response.ok) throw new Error('Network response was not ok');
      const data = await response.json();

      const botResponse = {
        id: Date.now() + 1,
        role: 'bot',
        content: data.llm_response || "I found some relevant information but couldn't generate a summary. Please check the context chunks if available.",
        context_used: data.context_used,
        routing_info: data.routing_info  // Add routing info for smart routing
      };

      setMessages(prev => [...prev, botResponse]);

      // Refresh conversations list to update message count
      loadConversations();

    } catch (error) {
      console.error("Error:", error);
      setMessages(prev => [...prev, {
        id: Date.now() + 1,
        role: 'bot',
        content: "Sorry, I encountered an error connecting to the knowledge base. Please ensure the backend server is running."
      }]);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="relative min-h-screen bg-white dark:bg-gray-950 text-gray-800 dark:text-gray-100 font-sans overflow-hidden">
      {/* Background Cube and Effects */}
      <BackgroundEffects isDarkMode={isDarkMode} />
      <AnimatePresence>
        {!hasUserInteracted && activeView !== 'code-review' && activeView !== 'inference-logs' && (
          <div className="fixed inset-0 z-0 flex items-center justify-center pointer-events-none">
            <RotatingCube layoutId="cube-main" size={180} textColor="text-blue-600 dark:text-blue-400" />
          </div>
        )}
      </AnimatePresence>

      <div className="relative z-10 flex h-screen">
        {/* Sidebar */}
        <AnimatePresence mode="wait">
          {isSidebarOpen && (
            <motion.aside
              initial={{ width: 0, opacity: 0 }}
              animate={{ width: 280, opacity: 1 }}
              exit={{ width: 0, opacity: 0 }}
              className="h-full bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-r border-gray-200 dark:border-gray-800 shadow-xl flex flex-col"
            >
              <div className="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 flex items-center justify-center overflow-visible">
                    {hasUserInteracted ? (
                      <RotatingCube layoutId="cube-main" size={38} textColor="text-blue-600 dark:text-blue-400" disableInteractive />
                    ) : (
                      <RotatingCube layoutId="cube-sidebar" size={38} textColor="text-blue-600 dark:text-blue-400" disableInteractive />
                    )}
                  </div>
                  <span className="font-bold text-xl text-gray-800 dark:text-white tracking-tight">Cube AI</span>
                </div>
                <button onClick={() => setIsSidebarOpen(false)} className="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded" title="Close Sidebar">
                  <PanelLeftClose size={20} />
                </button>
              </div>

              <div className="flex-1 overflow-y-auto scrollbar-hide p-4 space-y-6">

                {/* Context Selector - only show in chat view */}
                {activeView === 'chat' && (
                  <div className="space-y-2">
                    <h3 className="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Context Source</h3>
                    <div className="space-y-1">
                      {contexts.map((ctx) => (
                        <div
                          key={ctx.id}
                          onClick={() => setSelectedContext(ctx.id)}
                          className={cn(
                            "flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg cursor-pointer transition-colors",
                            selectedContext === ctx.id
                              ? "bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-1 ring-primary-200 dark:ring-primary-800"
                              : "text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800"
                          )}
                        >
                          <span className={selectedContext === ctx.id ? "text-primary-600" : "text-gray-500"}>
                            {ctx.icon}
                          </span>
                          <span>{ctx.label}</span>
                          {selectedContext === ctx.id && (
                            <motion.div layoutId="active-dot" className="ml-auto w-1.5 h-1.5 rounded-full bg-primary-500" />
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                <div className="space-y-1">
                  <h3 className="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Capabilities</h3>
                  <div
                    onClick={() => setActiveView('code-review')}
                    className={cn(
                      "group flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg cursor-pointer transition-colors",
                      activeView === 'code-review'
                        ? "bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-1 ring-primary-200 dark:ring-primary-800"
                        : "text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:text-primary-700 dark:hover:text-primary-300"
                    )}
                  >
                    <Code size={18} />
                    <span>Code Review</span>
                    {activeView === 'code-review' && (
                      <motion.div layoutId="active-capability" className="ml-auto w-1.5 h-1.5 rounded-full bg-primary-500" />
                    )}
                  </div>
                  <div
                    onClick={() => setActiveView('chat')}
                    className={cn(
                      "group flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg cursor-pointer transition-colors",
                      activeView === 'chat'
                        ? "bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-1 ring-primary-200 dark:ring-primary-800"
                        : "text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:text-primary-700 dark:hover:text-primary-300"
                    )}
                  >
                    <MessageSquare size={18} />
                    <span>Knowledge Chat</span>
                    {activeView === 'chat' && (
                      <motion.div layoutId="active-capability" className="ml-auto w-1.5 h-1.5 rounded-full bg-primary-500" />
                    )}
                  </div>
                  <div
                    onClick={() => setActiveView('inference-logs')}
                    className={cn(
                      "group flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg cursor-pointer transition-colors",
                      activeView === 'inference-logs'
                        ? "bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 ring-1 ring-primary-200 dark:ring-primary-800"
                        : "text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:text-primary-700 dark:hover:text-primary-300"
                    )}
                  >
                    <Activity size={18} />
                    <span>Inference Logs</span>
                    {activeView === 'inference-logs' && (
                      <motion.div layoutId="active-capability" className="ml-auto w-1.5 h-1.5 rounded-full bg-primary-500" />
                    )}
                  </div>
                  <div
                    onClick={() => setShowSyncModal(true)}
                    className={cn(
                      "group flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg cursor-pointer transition-colors",
                      "text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:text-primary-700 dark:hover:text-primary-300"
                    )}
                  >
                    <RefreshCw size={18} />
                    <span>Sync Knowledge</span>
                  </div>
                </div>

                {/* Conversations - only show in chat view */}
                {activeView === 'chat' && (
                  <div className="space-y-1 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <div className="flex items-center justify-between px-2 mb-2">
                      <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wider">Conversations</h3>
                      <div className="flex items-center gap-1">
                        <button
                          onClick={() => setIsSearchModalOpen(true)}
                          className="p-1 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded transition-colors"
                          title="Search Chats"
                        >
                          <Search size={16} />
                        </button>
                        <button
                          onClick={createNewConversation}
                          className="p-1 text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded transition-colors"
                          title="New Conversation"
                        >
                          <Plus size={16} />
                        </button>
                      </div>
                    </div>

                    {loadingConversations || authLoading ? (
                      <div className="px-3 py-2 text-sm text-gray-400">Loading...</div>
                    ) : conversations.length === 0 ? (
                      <div className="px-3 py-2 text-xs text-gray-400">
                        No conversations yet. Start chatting!
                      </div>
                    ) : (
                      <div className="space-y-1 max-h-64 overflow-y-auto scrollbar-hide">
                        {conversations.slice(0, 5).map((conv) => (
                          <div
                            key={conv.id}
                            onClick={() => !authLoading && loadConversation(conv.id)}
                            className={cn(
                              "group flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-colors relative",
                              authLoading ? "opacity-50 cursor-not-allowed" : "cursor-pointer",
                              currentConversationId === conv.id
                                ? "bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300"
                                : "text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800"
                            )}
                            title={conv.title} // Show full title on hover
                          >
                            <MessageSquare size={14} className="shrink-0" />
                            <div className="flex-1 min-w-0">
                              <div className="truncate font-medium">{conv.title}</div>
                              <div className="text-xs text-gray-400 mt-0.5">
                                {new Date(conv.updated_at).toLocaleDateString()} • {conv.context_type}
                              </div>
                            </div>
                            <span className="text-xs text-gray-400 shrink-0">{conv.message_count || 0}</span>
                            <button
                              onClick={(e) => deleteConversation(conv.id, e)}
                              className="opacity-0 group-hover:opacity-100 p-1 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-600 dark:hover:text-red-400 rounded transition-all shrink-0"
                              title="Delete conversation"
                            >
                              <Trash2 size={12} />
                            </button>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                )}
              </div>

              <div className="p-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
                <button
                  onClick={() => setIsDarkMode(!isDarkMode)}
                  className="flex items-center gap-3 w-full px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                >
                  {isDarkMode ? <Sun size={18} /> : <Moon size={18} />}
                  <span>{isDarkMode ? 'Light Mode' : 'Dark Mode'}</span>
                </button>
                <button
                  onClick={logout}
                  className="flex items-center gap-3 w-full px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                >
                  <LogOut size={18} />
                  <span>Logout</span>
                </button>
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400">
                    <User size={20} />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-gray-900 dark:text-white truncate">{user?.username || 'Developer'}</p>
                    <p className="text-xs text-gray-500 dark:text-gray-400 capitalize">{user?.role?.replace('_', ' ') || 'User'}</p>
                  </div>
                </div>
              </div>
            </motion.aside>
          )}
        </AnimatePresence>

        {/* Main Chat Area */}
        <main className="flex-1 flex flex-col h-full relative">
          <header className="h-16 px-6 flex items-center justify-between bg-white/50 dark:bg-gray-950/50 backdrop-blur-sm border-b border-gray-100/50 dark:border-gray-800/50">
            <div className="flex items-center gap-3">
              {!isSidebarOpen && (
                <button onClick={() => setIsSidebarOpen(true)} className="p-2 text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-lg transition-colors" title="Open Sidebar">
                  <PanelLeft size={20} />
                </button>
              )}
              <h1 className="font-semibold text-gray-800 dark:text-white">
                {activeView === 'code-review' ? 'Code Review Assistant' : activeView === 'inference-logs' ? 'Inference Logs' : 'Banking Assistant'}
              </h1>
            </div>
          </header>

          {/* Conditional content based on active view */}
          {activeView === 'code-review' ? (
            <CodeReview isDarkMode={isDarkMode} />
          ) : activeView === 'inference-logs' ? (
            <div className="flex-1 overflow-y-auto h-full relative">
              <InferenceLogs />
            </div>
          ) : (
            <>
              <div className="flex-1 overflow-y-auto p-4 lg:p-8 space-y-6">
                {messages.map((message) => (
                  <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    key={message.id}
                    className={cn(
                      "flex items-start gap-4 max-w-4xl",
                      message.role === 'user' ? "ml-auto flex-row-reverse" : "mr-auto"
                    )}
                  >


                    <div className={cn(
                      "text-base leading-relaxed overflow-hidden max-w-full",
                      message.role === 'user'
                        ? "bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 px-5 py-3 rounded-3xl rounded-tr-sm"
                        : "bg-transparent text-gray-800 dark:text-gray-100 px-5 py-1"
                    )}>
                      {message.role === 'user' ? (
                        <div className="whitespace-pre-wrap">{message.content}</div>
                      ) : (
                        <div className="prose prose-sm max-w-none prose-p:my-1 prose-headings:my-2 prose-ul:my-1 prose-li:my-0.5 text-gray-700 dark:text-gray-300 dark:prose-headings:text-gray-100 dark:prose-strong:text-gray-100 dark:prose-code:text-gray-100 dark:prose-pre:bg-gray-800 dark:prose-pre:border-gray-700">
                          <ReactMarkdown
                            components={{
                              code(props) {
                                const { children, className, node, ...rest } = props;
                                const match = /language-(\w+)/.exec(className || '');
                                if (match && match[1] === 'mermaid') {
                                  return <MermaidDiagram code={String(children).replace(/\n$/, '')} isDarkMode={isDarkMode} />;
                                }
                                return (
                                  <code {...rest} className={className}>
                                    {children}
                                  </code>
                                );
                              }
                            }}
                          >
                            {message.content}
                          </ReactMarkdown>
                        </div>
                      )}

                      {message.context_used && (
                        <div className="mt-4 pt-4 border-t border-gray-200/50 dark:border-gray-600/50">
                          <details className="text-xs text-gray-600 dark:text-gray-300 cursor-pointer">
                            <summary className="hover:text-primary-600 dark:hover:text-primary-400 font-medium">View Source Context</summary>
                            <div className="mt-2 p-3 bg-gray-50 dark:bg-gray-700/80 rounded-lg border border-gray-200 dark:border-gray-600 font-mono text-[11px] overflow-x-auto max-h-72 text-gray-700 dark:text-gray-100">
                              <div className="prose prose-xs max-w-none dark:prose-invert">
                                <ReactMarkdown
                                  components={{
                                    code(props) {
                                      const { children, className, node, ...rest } = props;
                                      const match = /language-(\w+)/.exec(className || '');
                                      if (match && match[1] === 'mermaid') {
                                        return <MermaidDiagram code={String(children).replace(/\n$/, '')} />;
                                      }
                                      return (
                                        <code {...rest} className={className}>
                                          {children}
                                        </code>
                                      );
                                    }
                                  }}
                                >
                                  {message.context_used}
                                </ReactMarkdown>
                              </div>
                            </div>
                          </details>
                        </div>
                      )}

                      {message.routing_info && (
                        <div className="mt-3 p-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-lg text-xs">
                          <div className="font-semibold text-blue-700 dark:text-blue-300 mb-1">🧠 Smart Routing</div>
                          <div className="text-blue-600 dark:text-blue-400 space-y-1">
                            <div><span className="font-medium">Primary:</span> {message.routing_info.primary_source}</div>
                            {message.routing_info.secondary_sources?.length > 0 && (
                              <div><span className="font-medium">Also searched:</span> {message.routing_info.secondary_sources.join(', ')}</div>
                            )}
                            <div><span className="font-medium">Confidence:</span> {(message.routing_info.confidence * 100).toFixed(0)}%</div>
                            <div><span className="font-medium">Reason:</span> {message.routing_info.reasoning}</div>
                          </div>
                        </div>
                      )}
                    </div>
                  </motion.div>
                ))}

                {isLoading && (
                  <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="flex items-start gap-4 max-w-4xl mr-auto px-5">
                    <div className="bg-transparent px-0 py-1">
                      <div className="flex gap-1">
                        <span className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                        <span className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                        <span className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
                      </div>
                    </div>
                  </motion.div>
                )}

                <div className="h-4" />
                <div ref={messagesEndRef} />
              </div>

              <div className="p-4 lg:p-8 bg-gradient-to-t from-white via-white/80 to-transparent dark:from-gray-950 dark:via-gray-950/80">
                <div className="max-w-4xl mx-auto relative group">
                  <div className="absolute -inset-1 bg-gradient-to-r from-primary-400 to-primary-600 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-1000 group-hover:duration-200" />
                  <form onSubmit={handleSendMessage} className="relative flex items-center gap-2 bg-gray-100 dark:bg-gray-800 rounded-[26px] p-2 pl-5 transition-shadow focus-within:ring-2 focus-within:ring-primary-100 dark:focus-within:ring-primary-900/30">
                    <input
                      type="text"
                      value={inputValue}
                      onChange={(e) => setInputValue(e.target.value)}
                      placeholder={`Message Cube AI...`}
                      className="flex-1 bg-transparent text-gray-800 dark:text-gray-100 placeholder-gray-500 focus:outline-none text-base py-2"
                    />
                    <button
                      type="submit"
                      disabled={isLoading || !inputValue.trim()}
                      className="p-2 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition-all disabled:opacity-30 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95"
                    >
                      <Send size={18} />
                    </button>
                  </form>
                  <div className="text-center mt-2 text-xs text-gray-400">
                    AI-generated responses may require verification.
                  </div>
                </div>
              </div>
            </>
          )}
        </main>
      </div>

      {/* Sync Knowledge Base Modal */}
      <SyncKnowledgeBase
        isOpen={showSyncModal}
        onClose={() => setShowSyncModal(false)}
        isDarkMode={isDarkMode}
      />

      {/* Search Modal */}
      <SearchModal
        isOpen={isSearchModalOpen}
        onClose={() => setIsSearchModalOpen(false)}
        isDarkMode={isDarkMode}
        loadConversation={loadConversation}
        createNewConversation={createNewConversation}
      />
    </div>
  );
}

export default ChatApp;
