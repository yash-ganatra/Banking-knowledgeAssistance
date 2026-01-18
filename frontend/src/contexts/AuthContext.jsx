import { createContext, useContext, useState, useEffect } from 'react';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(() => localStorage.getItem('token'));
  const [loading, setLoading] = useState(true);

  // Load user data on mount if token exists
  useEffect(() => {
    const loadUser = async () => {
      const storedToken = localStorage.getItem('token');
      if (storedToken) {
        // Validate the token format first
        if (!storedToken || storedToken === 'undefined' || storedToken === 'null') {
          console.log('Invalid token format, clearing...');
          localStorage.removeItem('token');
          setToken(null);
          setUser(null);
          setLoading(false);
          return;
        }

        try {
          const response = await fetch('http://localhost:8000/api/auth/me', {
            headers: {
              'Authorization': `Bearer ${storedToken}`
            }
          });

          if (response.ok) {
            const userData = await response.json();
            setUser(userData);
            setToken(storedToken);
          } else if (response.status === 401) {
            // Token is invalid or expired - clear it silently
            console.log('Token expired or invalid, clearing...');
            localStorage.removeItem('token');
            setToken(null);
            setUser(null);
          } else {
            // Other error
            console.error('Error validating token:', response.status);
            localStorage.removeItem('token');
            setToken(null);
            setUser(null);
          }
        } catch (error) {
          console.error('Network error fetching user:', error);
          // Don't clear token on network errors - might be temporary
        }
      }
      setLoading(false);
    };

    loadUser();
  }, []);

  const login = async (username, password) => {
    const response = await fetch('http://localhost:8000/api/auth/login-json', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ username, password }),
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.detail || 'Login failed');
    }

    const data = await response.json();
    
    // Store token first
    localStorage.setItem('token', data.access_token);
    
    // Then update state
    const newUser = {
      id: data.user_id,
      username: data.username,
      email: data.email,
      role: data.role
    };
    
    setUser(newUser);
    setToken(data.access_token);
    
    return data;
  };

  const signup = async (username, email, password, role = 'team_member') => {
    const response = await fetch('http://localhost:8000/api/auth/signup', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ username, email, password, role }),
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.detail || 'Signup failed');
    }

    const data = await response.json();
    
    // Store token first
    localStorage.setItem('token', data.access_token);
    
    // Then update state
    const newUser = {
      id: data.user_id,
      username: data.username,
      email: data.email,
      role: data.role
    };
    
    setUser(newUser);
    setToken(data.access_token);
    
    return data;
  };

  const logout = () => {
    setToken(null);
    setUser(null);
    localStorage.removeItem('token');
  };

  const value = {
    user,
    token,
    loading,
    login,
    signup,
    logout,
    isAuthenticated: !!(user && token)
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
}
