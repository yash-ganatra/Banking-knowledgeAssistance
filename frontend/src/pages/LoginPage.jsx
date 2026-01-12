import { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { Lock, Mail, AlertCircle, Loader2, Shield } from 'lucide-react';
import { motion } from 'framer-motion';
import { RotatingCube } from '../components/RotatingCube';

export function LoginPage({ onSwitchToSignup }) {
  const { login } = useAuth();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await login(username, password);
      // Redirect happens automatically via AuthContext
    } catch (err) {
      setError(err.message || 'Login failed. Please check your credentials.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex min-h-screen">
      {/* Left Panel - Solid Blue */}
      <div className="hidden lg:flex w-1/2 bg-blue-600 items-center justify-center p-12 relative overflow-hidden">
        <div className="text-center z-10 w-full max-w-lg">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
          >
            <h1 className="text-5xl font-bold text-white mb-6">Welcome Back</h1>
            <p className="text-blue-100 text-xl leading-relaxed">
              Access your intelligent coding companion. Navigate complex banking architectures, understand legacy code, and accelerate development with AI-powered insights.
            </p>
          </motion.div>
        </div>

        {/* Subtle decorative circles in background */}
        <div className="absolute top-0 -left-20 w-80 h-80 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob" />
        <div className="absolute -bottom-20 right-0 w-80 h-80 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000" />
      </div>

      {/* Right Panel - White with Cube and Form */}
      <div className="w-full lg:w-1/2 bg-white flex flex-col items-center justify-center p-8 relative">
        <div className="w-full max-w-md space-y-8">

          {/* Cube Display */}
          <div className="flex justify-center mb-8 h-48 relative">
            <div className="scale-75 origin-center">
              <RotatingCube size={180} textColor="text-blue-600" />
            </div>
          </div>

          <div className="text-center">
            <h2 className="text-3xl font-bold text-gray-900 tracking-tight">Sign in to Account</h2>
            <p className="mt-2 text-sm text-gray-600">
              Please enter your credentials to proceed
            </p>
          </div>

          {/* Error Message */}
          {error && (
            <motion.div
              initial={{ opacity: 0, x: -20 }}
              animate={{ opacity: 1, x: 0 }}
              className="p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3"
            >
              <AlertCircle className="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" />
              <p className="text-sm text-red-700">{error}</p>
            </motion.div>
          )}

          {/* Login Form */}
          <form onSubmit={handleSubmit} className="space-y-6">
            <div>
              <label htmlFor="username" className="block text-sm font-medium text-gray-700 mb-2">
                Username
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Mail className="h-5 w-5 text-gray-400" />
                </div>
                <input
                  id="username"
                  type="text"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  required
                  className="block w-full pl-10 pr-3 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                  placeholder="Enter your username"
                />
              </div>
            </div>

            <div>
              <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                Password
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Lock className="h-5 w-5 text-gray-400" />
                </div>
                <input
                  id="password"
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                  className="block w-full pl-10 pr-3 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                  placeholder="Enter your password"
                />
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:scale-[1.02] shadow-md shadow-blue-200"
            >
              {loading ? (
                <>
                  <Loader2 className="w-5 h-5 animate-spin" />
                  <span>Signing in...</span>
                </>
              ) : (
                <>
                  <Shield className="w-5 h-5" />
                  <span>Sign In</span>
                </>
              )}
            </button>
          </form>

          <div className="text-center mt-6">
            <p className="text-sm text-gray-600">
              Don't have an account?{' '}
              <button
                onClick={onSwitchToSignup}
                className="text-blue-600 hover:text-blue-700 font-semibold transition-colors"
              >
                Sign up
              </button>
            </p>
          </div>


        </div>
      </div>
    </div>
  );
}
