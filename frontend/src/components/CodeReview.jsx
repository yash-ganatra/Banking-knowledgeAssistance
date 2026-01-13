import { useState, useRef, useEffect } from 'react';
import { Send, Code, CheckCircle, AlertTriangle, Info, Copy, Check } from 'lucide-react';
import ReactMarkdown from 'react-markdown';
import { cn } from '../lib/utils';
import { motion } from 'framer-motion';

function CodeReview({ isDarkMode }) {
  const [codeInput, setCodeInput] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [review, setReview] = useState(null);
  const [copied, setCopied] = useState(false);
  const textareaRef = useRef(null);

  // Auto-resize textarea
  useEffect(() => {
    if (textareaRef.current) {
      textareaRef.current.style.height = 'auto';
      textareaRef.current.style.height = textareaRef.current.scrollHeight + 'px';
    }
  }, [codeInput]);

  const handleSubmitCode = async (e) => {
    e.preventDefault();
    if (!codeInput.trim() || isLoading) return;

    setIsLoading(true);
    setReview(null);

    try {
      const response = await fetch('http://localhost:8000/api/code-review', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
          code: codeInput,
          language: detectLanguage(codeInput)
        })
      });

      if (!response.ok) throw new Error('Failed to get code review');
      
      const data = await response.json();
      setReview(data);
    } catch (error) {
      console.error("Error:", error);
      setReview({
        success: false,
        message: "Sorry, I encountered an error analyzing your code. Please try again."
      });
    } finally {
      setIsLoading(false);
    }
  };

  const detectLanguage = (code) => {
    // Simple language detection based on code patterns
    if (code.includes('<?php') || code.includes('function') && code.includes('$')) return 'php';
    if (code.includes('const') || code.includes('let') || code.includes('=>')) return 'javascript';
    if (code.includes('CREATE TABLE') || code.includes('SELECT')) return 'sql';
    return 'unknown';
  };

  const handleCopyCode = () => {
    navigator.clipboard.writeText(codeInput);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const handleClearCode = () => {
    setCodeInput('');
    setReview(null);
  };

  const getSeverityColor = (severity) => {
    switch (severity?.toLowerCase()) {
      case 'error':
      case 'critical':
        return 'text-red-600 dark:text-red-400';
      case 'warning':
        return 'text-yellow-600 dark:text-yellow-400';
      case 'info':
      case 'suggestion':
        return 'text-blue-600 dark:text-blue-400';
      default:
        return 'text-gray-600 dark:text-gray-400';
    }
  };

  const getSeverityIcon = (severity) => {
    switch (severity?.toLowerCase()) {
      case 'error':
      case 'critical':
        return <AlertTriangle size={16} />;
      case 'warning':
        return <AlertTriangle size={16} />;
      case 'info':
      case 'suggestion':
        return <Info size={16} />;
      default:
        return <CheckCircle size={16} />;
    }
  };

  return (
    <div className="flex flex-col h-full">
      {/* Header */}
      <div className="p-6 border-b border-gray-200 dark:border-gray-800">
        <div className="flex items-center gap-3 mb-2">
          <div className="w-10 h-10 rounded-lg bg-primary-600 flex items-center justify-center text-white">
            <Code size={20} />
          </div>
          <div>
            <h2 className="text-xl font-bold text-gray-800 dark:text-white">Code Review Assistant</h2>
            <p className="text-sm text-gray-500 dark:text-gray-400">Get instant feedback on your code quality</p>
          </div>
        </div>
      </div>

      {/* Content Area */}
      <div className="flex-1 overflow-y-auto p-6 space-y-6">
        
        {/* Code Input Section */}
        <div className="space-y-3">
          <div className="flex items-center justify-between">
            <label className="text-sm font-semibold text-gray-700 dark:text-gray-300">
              Paste Your Code
            </label>
            <div className="flex gap-2">
              {codeInput && (
                <>
                  <button
                    onClick={handleCopyCode}
                    className="text-xs px-2 py-1 text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded transition-colors flex items-center gap-1"
                  >
                    {copied ? <Check size={12} /> : <Copy size={12} />}
                    {copied ? 'Copied!' : 'Copy'}
                  </button>
                  <button
                    onClick={handleClearCode}
                    className="text-xs px-2 py-1 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition-colors"
                  >
                    Clear
                  </button>
                </>
              )}
            </div>
          </div>
          
          <form onSubmit={handleSubmitCode} className="space-y-3">
            <div className="relative">
              <textarea
                ref={textareaRef}
                value={codeInput}
                onChange={(e) => setCodeInput(e.target.value)}
                placeholder="// Paste your PHP, JavaScript, or SQL code here...
function calculateTotal($items) {
    $total = 0;
    foreach($items as $item) {
        $total += $item['price'];
    }
    return $total;
}"
                className="w-full min-h-[200px] max-h-[400px] px-4 py-3 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 border border-gray-200 dark:border-gray-700 rounded-lg font-mono text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 resize-none"
                style={{ overflow: 'auto' }}
              />
            </div>

            <button
              type="submit"
              disabled={isLoading || !codeInput.trim()}
              className="w-full py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors shadow-md transform hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:transform-none flex items-center justify-center gap-2 font-medium"
            >
              {isLoading ? (
                <>
                  <div className="flex gap-1">
                    <span className="w-2 h-2 bg-white rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                    <span className="w-2 h-2 bg-white rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                    <span className="w-2 h-2 bg-white rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
                  </div>
                  <span>Analyzing Code...</span>
                </>
              ) : (
                <>
                  <Send size={18} />
                  <span>Review Code</span>
                </>
              )}
            </button>
          </form>
        </div>

        {/* Review Results */}
        {review && (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="space-y-4"
          >
            <div className="border-t border-gray-200 dark:border-gray-800 pt-6">
              <h3 className="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <CheckCircle className="text-primary-600" size={20} />
                Review Results
              </h3>

              {review.success === false ? (
                <div className="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300">
                  {review.message}
                </div>
              ) : (
                <div className="space-y-4">
                  {/* Full Review Response */}
                  {review.review && (
                    <div className="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg">
                      <h4 className="font-semibold text-gray-800 dark:text-white mb-3">Detailed Analysis</h4>
                      <div className="prose prose-sm max-w-none dark:prose-invert text-gray-700 dark:text-gray-300">
                        <ReactMarkdown>{review.review}</ReactMarkdown>
                      </div>
                    </div>
                  )}
                </div>
              )}
            </div>
          </motion.div>
        )}

        {/* Guidelines Reference */}
        <div className="mt-6 p-4 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg">
          <h4 className="font-semibold text-gray-800 dark:text-white mb-2 text-sm">Review Criteria</h4>
          <ul className="text-xs text-gray-600 dark:text-gray-400 space-y-1">
            <li>• Variable and function naming conventions</li>
            <li>• Input validation and error handling</li>
            <li>• API best practices and security</li>
            <li>• Database data type selection</li>
            <li>• Code readability and maintainability</li>
            <li>• Defensive coding practices</li>
          </ul>
        </div>
      </div>
    </div>
  );
}

export default CodeReview;
