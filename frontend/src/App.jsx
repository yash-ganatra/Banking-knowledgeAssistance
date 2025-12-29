import { useState } from 'react';
import { Send, Menu, Bot, User, Shield, Code, ChevronLeft, Database, FileText } from 'lucide-react';
import ReactMarkdown from 'react-markdown';
import { RotatingCube } from './components/RotatingCube';
import { BackgroundEffects } from './components/BackgroundEffects';
import { MermaidDiagram } from './components/MermaidDiagram';

import { cn } from './lib/utils';
import { motion, AnimatePresence } from 'framer-motion';

function App() {
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);
  const [selectedContext, setSelectedContext] = useState('business'); // business, php, js
  const [messages, setMessages] = useState([
    {
      id: 1,
      role: 'bot',
      content: 'Hello! I am your advanced banking assistant. Please select a knowledge base context and ask your question.'
    }
  ]);
  const [inputValue, setInputValue] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  // Context Options
  const contexts = [
    { id: 'business', label: 'Business Docs', icon: <FileText size={18} /> },
    { id: 'php', label: 'PHP Knowledge', icon: <Database size={18} /> },
    { id: 'js', label: 'JS Knowledge', icon: <Code size={18} /> },
  ];

  const handleSendMessage = async (e) => {
    e.preventDefault();
    if (!inputValue.trim() || isLoading) return;

    const newMessage = {
      id: Date.now(),
      role: 'user',
      content: inputValue
    };

    setMessages(prev => [...prev, newMessage]);
    setInputValue('');
    setIsLoading(true);

    try {
      const response = await fetch(`http://localhost:8000/inference/${selectedContext}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ query: newMessage.content, top_k: 5, rerank: true })
      });

      if (!response.ok) throw new Error('Network response was not ok');
      const data = await response.json();

      const botResponse = {
        id: Date.now() + 1,
        role: 'bot',
        content: data.llm_response || "I found some relevant information but couldn't generate a summary. Please check the context chunks if available.",
        context_used: data.context_used
      };

      setMessages(prev => [...prev, botResponse]);
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
    <div className="relative min-h-screen bg-white text-gray-800 font-sans overflow-hidden">
      {/* Background Cube and Effects */}
      <BackgroundEffects />
      <div className="fixed inset-0 z-0 flex items-center justify-center pointer-events-none">
        <RotatingCube />
      </div>

      <div className="relative z-10 flex h-screen">
        {/* Sidebar */}
        <AnimatePresence mode="wait">
          {isSidebarOpen && (
            <motion.aside
              initial={{ width: 0, opacity: 0 }}
              animate={{ width: 280, opacity: 1 }}
              exit={{ width: 0, opacity: 0 }}
              className="h-full bg-white/80 backdrop-blur-md border-r border-gray-200 shadow-xl flex flex-col"
            >
              <div className="p-6 border-b border-gray-100 flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <div className="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center text-white">
                    <Shield size={18} />
                  </div>
                  <span className="font-bold text-gray-800 tracking-tight">Cube AI</span>
                </div>
                <button onClick={() => setIsSidebarOpen(false)} className="lg:hidden p-1 text-gray-500 hover:bg-gray-100 rounded">
                  <ChevronLeft size={20} />
                </button>
              </div>

              <div className="flex-1 overflow-y-auto p-4 space-y-6">

                {/* Context Selector */}
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
                            ? "bg-primary-100 text-primary-700 ring-1 ring-primary-200"
                            : "text-gray-700 hover:bg-gray-50"
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

                <div className="space-y-1">
                  <h3 className="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Capabilities</h3>
                  <div className="group flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-primary-50 hover:text-primary-700 cursor-pointer transition-colors">
                    <Code size={18} />
                    <span>Code Analysis</span>
                  </div>
                  <div className="group flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-primary-50 hover:text-primary-700 cursor-pointer transition-colors">
                    <Shield size={18} />
                    <span>Security Audit</span>
                  </div>
                </div>

                <div className="space-y-1 pt-4 border-t border-gray-100">
                  <h3 className="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Recent Chats</h3>
                  <div className="px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg cursor-pointer truncate">
                    API Integration Help
                  </div>
                </div>
              </div>

              <div className="p-4 border-t border-gray-100">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-600">
                    <User size={20} />
                  </div>
                  <div>
                    <p className="text-sm font-medium text-gray-900">Developer</p>
                    <p className="text-xs text-gray-500">Pro Account</p>
                  </div>
                </div>
              </div>
            </motion.aside>
          )}
        </AnimatePresence>

        {/* Main Chat Area */}
        <main className="flex-1 flex flex-col h-full relative">
          <header className="h-16 px-6 flex items-center justify-between bg-white/50 backdrop-blur-sm border-b border-gray-100/50">
            <div className="flex items-center gap-3">
              {!isSidebarOpen && (
                <button onClick={() => setIsSidebarOpen(true)} className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                  <Menu size={20} />
                </button>
              )}
              <h1 className="font-semibold text-gray-800">Banking Assistant</h1>
            </div>
          </header>

          <div className="flex-1 overflow-y-auto p-4 lg:p-8 space-y-6">
            {messages.map((message) => (
              <motion.div
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                key={message.id}
                className={cn(
                  "flex items-start gap-4 max-w-3xl",
                  message.role === 'user' ? "ml-auto flex-row-reverse" : "mr-auto"
                )}
              >
                <div className={cn(
                  "w-10 h-10 rounded-full flex items-center justify-center shrink-0 shadow-sm",
                  message.role === 'user' ? "bg-primary-600 text-white" : "bg-white text-primary-600 border border-gray-100"
                )}>
                  {message.role === 'user' ? <User size={20} /> : <Bot size={20} />}
                </div>

                <div className={cn(
                  "p-4 rounded-2xl shadow-sm text-sm leading-relaxed overflow-hidden",
                  message.role === 'user'
                    ? "bg-primary-600 text-white rounded-tr-none"
                    : "bg-white/80 backdrop-blur-sm border border-gray-100 rounded-tl-none text-gray-700"
                )}>
                  {message.role === 'user' ? (
                    <div className="whitespace-pre-wrap">{message.content}</div>
                  ) : (
                    <div className="prose prose-sm max-w-none prose-p:my-1 prose-headings:my-2 prose-ul:my-1 prose-li:my-0.5 text-gray-700">
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
                        {message.content}
                      </ReactMarkdown>
                    </div>
                  )}

                  {message.context_used && (
                    <div className="mt-4 pt-4 border-t border-gray-200/50">
                      <details className="text-xs text-gray-500 cursor-pointer">
                        <summary className="hover:text-primary-600 font-medium">View Source Context</summary>
                        <div className="mt-2 p-2 bg-gray-50 rounded border border-gray-100 font-mono text-[10px] overflow-x-auto max-h-60">
                          <div className="prose prose-xs max-w-none">
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
                </div>
              </motion.div>
            ))}

            {isLoading && (
              <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="flex items-start gap-4 max-w-3xl mr-auto">
                <div className="w-10 h-10 rounded-full bg-white text-primary-600 border border-gray-100 flex items-center justify-center shrink-0 shadow-sm">
                  <Bot size={20} />
                </div>
                <div className="bg-white/80 backdrop-blur-sm border border-gray-100 p-4 rounded-2xl rounded-tl-none">
                  <div className="flex gap-1">
                    <span className="w-2 h-2 bg-primary-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                    <span className="w-2 h-2 bg-primary-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                    <span className="w-2 h-2 bg-primary-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
                  </div>
                </div>
              </motion.div>
            )}

            <div className="h-4" />
          </div>

          <div className="p-4 lg:p-8 bg-gradient-to-t from-white via-white/80 to-transparent">
            <div className="max-w-4xl mx-auto relative group">
              <div className="absolute -inset-1 bg-gradient-to-r from-primary-400 to-primary-600 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-1000 group-hover:duration-200" />
              <form onSubmit={handleSendMessage} className="relative flex items-center gap-2 bg-white rounded-xl shadow-lg border border-gray-100 p-2">
                <input
                  type="text"
                  value={inputValue}
                  onChange={(e) => setInputValue(e.target.value)}
                  placeholder={`Ask about ${contexts.find(c => c.id === selectedContext)?.label}...`}
                  className="flex-1 px-4 py-2 bg-transparent text-gray-800 placeholder-gray-400 focus:outline-none"
                />
                <button
                  type="submit"
                  disabled={isLoading}
                  className="p-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors shadow-md transform hover:scale-105 active:scale-95 disabled:opacity-50 disabled:transform-none"
                >
                  <Send size={20} />
                </button>
              </form>
              <div className="text-center mt-2 text-xs text-gray-400">
                AI-generated responses may require verification.
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}

export default App;
