# Code Review Feature

## Overview
The Code Review feature is an AI-powered capability that analyzes developer code against established coding guidelines and provides constructive feedback.

## How It Works

### Frontend Component
- **Location**: `/frontend/src/components/CodeReview.jsx`
- **Features**:
  - Large textarea input for pasting code
  - Auto-resize textarea
  - Language detection (PHP, JavaScript, SQL)
  - Real-time code review analysis
  - Structured display of issues by severity
  - Code quality score display
  - Copy and clear functionality

### Backend API
- **Location**: `/backend/routers/code_review_routes.py`
- **Endpoint**: `POST /api/code-review`
- **Authentication**: Required (Bearer token)

### Review Criteria
The code review is based on the guidelines in:
`developer_coding_guidelines_php_java_script_database.md`

#### Review Focus Areas:
1. **Variable Naming Conventions**
   - PHP: camelCase
   - JavaScript: camelCase
   - Database: snake_case

2. **Function Naming**
   - Start with verbs
   - One function = one responsibility
   - Boolean functions: is/has/can/should prefix

3. **API Best Practices**
   - Input validation
   - Error handling (try-catch)
   - Structured API responses
   - Proper HTTP status codes

4. **Database Data Types**
   - Appropriate type selection
   - Avoiding common mistakes (VARCHAR for numbers, etc.)

5. **Code Quality**
   - Readability and maintainability
   - Defensive coding practices
   - Comment quality
   - Function length (< 30-40 lines preferred)

## Usage

### For Developers:
1. Login to the application
2. Click on **"Code Review"** in the Capabilities section
3. Paste your PHP, JavaScript, or SQL code
4. Click **"Review Code"**
5. Review the analysis:
   - Overall summary
   - Issues categorized by severity (Critical, Error, Warning, Info)
   - Specific suggestions for each issue
   - Code quality score (0-10)

### Issue Severity Levels:
- **CRITICAL**: Security vulnerabilities, major bugs
- **ERROR**: Code that won't work correctly
- **WARNING**: Potential problems, bad practices
- **INFO**: Style improvements, optimizations

## Integration

### In ChatApp.jsx:
```javascript
// State for view switching
const [activeView, setActiveView] = useState('chat'); // 'chat' or 'code-review'

// Conditional rendering
{activeView === 'code-review' ? (
  <CodeReview isDarkMode={isDarkMode} />
) : (
  // Chat interface
)}
```

### API Request Format:
```json
POST /api/code-review
Headers:
  Authorization: Bearer <token>
  Content-Type: application/json

Body:
{
  "code": "function example() { ... }",
  "language": "javascript"
}
```

### API Response Format:
```json
{
  "success": true,
  "review": "Detailed markdown review text",
  "issues": [
    {
      "severity": "warning",
      "description": "Variable name should use camelCase",
      "line_number": 5,
      "suggestion": "Rename 'user_name' to 'userName'"
    }
  ],
  "score": 7,
  "summary": "Overall the code is well-structured but has some naming convention issues."
}
```

## AI Model
- Uses **Groq API** with `llama-3.3-70b-versatile` model
- Temperature: 0.3 (for consistent, focused feedback)
- Max tokens: 2000

## Features

### Frontend Features:
- ✅ Syntax-highlighted code input
- ✅ Auto-expanding textarea
- ✅ Language detection
- ✅ Copy code functionality
- ✅ Clear code button
- ✅ Loading animation during analysis
- ✅ Structured issue display with severity icons
- ✅ Dark mode support
- ✅ Responsive design

### Backend Features:
- ✅ Loads coding guidelines from markdown file
- ✅ AI-powered code analysis
- ✅ Issue parsing and categorization
- ✅ Score extraction
- ✅ Summary generation
- ✅ User authentication
- ✅ Error handling and logging

## Future Enhancements (Optional)
- [ ] Save code review history
- [ ] Export review as PDF/markdown
- [ ] Line-by-line annotations
- [ ] Code diff suggestions
- [ ] Multi-file review
- [ ] Custom rule configuration
- [ ] Integration with Git repositories
- [ ] Automated PR reviews

## Testing

To test the feature:
1. Ensure backend is running: `uvicorn backend.main:app --reload`
2. Ensure frontend is running: `npm run dev`
3. Login to the application
4. Navigate to Code Review
5. Test with sample code:

```php
// Test PHP code
function calculateTotal($items) {
    $total = 0;
    foreach($items as $item) {
        $total += $item['price'];
    }
    return $total;
}
```

Expected feedback might include:
- Missing input validation
- No error handling
- Good naming conventions
- Consider using array functions

## Troubleshooting

### Issue: "Code review failed"
- Check if backend is running
- Verify GROQ_API_KEY is set
- Check authentication token

### Issue: No guidelines loaded
- Verify `developer_coding_guidelines_php_java_script_database.md` exists in project root
- Check file path in `code_review_routes.py`

### Issue: Frontend not displaying component
- Check browser console for errors
- Verify CodeReview component is imported in ChatApp.jsx
- Check that activeView state is working

## Security Considerations
- All requests require authentication
- Code is not stored by default
- API key is kept server-side
- Input validation on backend
- XSS protection via React's built-in sanitization
