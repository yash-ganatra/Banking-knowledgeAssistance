# Token Authentication Debugging Guide

## Common Issues and Solutions

### Issue: "Token decode failed - invalid token" (401 Unauthorized)

This error occurs when:
1. **Token is expired** - Tokens expire after 30 minutes
2. **Token is malformed** - Token is corrupted, 'undefined', or 'null'
3. **Token not sent** - Authorization header missing or incorrect
4. **SECRET_KEY mismatch** - Backend SECRET_KEY changed after token was issued

### Solutions Applied

#### 1. Frontend Token Validation
- Added validation in `AuthContext.jsx` to check for malformed tokens
- Clear invalid tokens ('undefined', 'null') immediately on load
- Wait for auth to complete before making API calls
- Added `authLoading` state to prevent premature API calls

#### 2. Better Error Handling
- Catch 401 errors and automatically logout user
- Prevent clicking conversations while auth is loading
- Show clear loading states

#### 3. Backend Token Validation
- Added better logging for token decode errors
- Validate token format before attempting decode
- Provide specific error messages for debugging

### Testing Your Fix

1. **Clear existing token:**
   ```javascript
   // In browser console
   localStorage.removeItem('token');
   ```

2. **Login again** to get a fresh token

3. **Test loading conversations:**
   - Wait for page to fully load (check for "Loading..." text)
   - Click on an old conversation
   - Check browser console for any errors

4. **Check backend logs:**
   ```bash
   # Look for these messages:
   # - "Token decode failed - invalid token"
   # - "Authentication successful for user..."
   ```

### Browser Console Debugging

```javascript
// Check current token
console.log('Token:', localStorage.getItem('token'));

// Check token format
const token = localStorage.getItem('token');
console.log('Token valid?', token && token !== 'undefined' && token !== 'null');

// Decode token (requires jwt-decode library)
// import jwtDecode from 'jwt-decode';
// console.log('Decoded:', jwtDecode(token));
```

### Token Expiration

Tokens expire after 30 minutes. If you see 401 errors after being logged in for a while:
1. This is expected behavior
2. The app will automatically log you out
3. Simply login again to continue

### Future Improvements

Consider implementing:
- Token refresh mechanism
- Automatic token renewal before expiration
- Better UX for token expiration (modal instead of logout)
