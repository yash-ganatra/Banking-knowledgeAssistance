# Groq API Setup Guide

## 1. Get Your Groq API Key

1. Visit: https://console.groq.com/
2. Sign up or log in
3. Navigate to API Keys section
4. Create a new API key
5. Copy the key (it will look like: `gsk_...`)

## 2. Install Required Package

```bash
pip install groq
```

Or install from requirements file:

```bash
pip install -r requirements_groq.txt
```

## 3. Set Environment Variable

### macOS/Linux (Current Terminal Session):
```bash
export GROQ_API_KEY='your-api-key-here'
```

### macOS/Linux (Permanent - add to ~/.zshrc or ~/.bashrc):
```bash
echo 'export GROQ_API_KEY="your-api-key-here"' >> ~/.zshrc
source ~/.zshrc
```

### Verify It's Set:
```bash
echo $GROQ_API_KEY
```

## 4. Run the Enhancement Script

```bash
cd /Users/newusername/Desktop/RAG_bankingV.2/Banking-knowledgeAssistance/utils
python enhance_descriptions_with_groq.py
```

## 5. Script Features

### What It Does:
- ✅ Identifies chunks with minimal/generic descriptions
- ✅ Analyzes code snippets using Groq API
- ✅ Generates detailed technical descriptions (150-200 words)
- ✅ Focuses on: implementation, data flow, DOM interactions, API calls, error handling
- ✅ Preserves original chunks that are already detailed
- ✅ Rate limiting to respect API limits

### Output:
- **File**: `js_file_chunks_enhanced_descriptions.json`
- **Metadata Added**: 
  - `description_enhanced: true`
  - `description_enhanced_date: "2025-12-22"`

### Estimated Costs (Groq Pricing):
Groq offers **FREE tier** with:
- 30 requests/minute
- 6,000 requests/day
- Very generous limits for this use case!

### Processing Time:
- ~50-100 chunks to enhance
- ~2 requests/second (rate limited)
- **Estimated**: 5-10 minutes total

## 6. Enhanced Description Quality

### BEFORE (Generic):
```
"The function saveUserDetailsCallBackFunction in admin.js accepts parameters: response, object."
```

### AFTER (Technical & Detailed):
```
"The saveUserDetailsCallBackFunction serves as a callback handler for the user details save operation. 

TECHNICAL IMPLEMENTATION: It processes the AJAX response by checking the response status field. On success, it displays a success growl notification, removes the 'disabled' class from the #saveAmendData button to re-enable user interaction, and redirects to /bank/amendtracking using the redirectUrl helper function with form state data. 

DATA FLOW: Accepts response (AJAX response object) and object (form context) parameters → validates response.status → triggers UI updates → performs conditional redirect.

DOM INTERACTIONS: Manipulates #saveAmendData element to toggle disabled state, uses jQuery's .removeClass() for UI state management.

ERROR HANDLING: For non-success responses, displays a warning-level growl notification with the response message and returns false to prevent further processing.

BUSINESS LOGIC: This function is critical in the banking amendment workflow, ensuring users receive feedback after save operations and are navigated to the appropriate tracking page upon successful data persistence."
```

## 7. Customization Options

Edit the script to adjust:

### Model Selection (Line 13):
```python
GROQ_MODEL = "mixtral-8x7b-32768"  # Fast, good quality
# OR
GROQ_MODEL = "llama2-70b-4096"     # Higher quality, slower
```

### Description Length (Line 14):
```python
MAX_TOKENS = 500  # Increase for longer descriptions
```

### Rate Limiting (Line 19):
```python
REQUESTS_PER_MINUTE = 30  # Adjust based on your plan
```

### Temperature (Line 15):
```python
TEMPERATURE = 0.3  # 0.1-0.5: more focused, 0.6-1.0: more creative
```

## 8. Troubleshooting

### "GROQ_API_KEY environment variable not set"
```bash
export GROQ_API_KEY='your-actual-key'
```

### "Rate limit exceeded"
- Script already has rate limiting built-in
- If issue persists, reduce REQUESTS_PER_MINUTE in script

### "ModuleNotFoundError: No module named 'groq'"
```bash
pip install groq
```

### API Errors
- Check API key is valid
- Verify you're within free tier limits
- Check Groq status: https://status.groq.com/

## 9. Next Steps After Enhancement

```bash
# Verify enhanced descriptions
python verify_enhanced_chunks.py

# Embed to ChromaDB with enhanced descriptions
python embed_enhanced_chunks_to_chromadb.py
```

## 10. Benefits of Enhanced Descriptions

### For RAG Retrieval:
✅ More semantic similarity matches
✅ Better context understanding
✅ Improved relevance scoring
✅ Reduced false positives

### For Developers:
✅ Better code documentation
✅ Faster onboarding
✅ Clearer technical understanding
✅ Maintenance efficiency

---

**Questions?** Check Groq documentation: https://console.groq.com/docs
