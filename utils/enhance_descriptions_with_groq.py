#!/usr/bin/env python3
"""
Enhanced Description Generator using Groq API
Analyzes code snippets to generate detailed, technical descriptions for JS chunks
"""

import json
import os
import time
from pathlib import Path
from groq import Groq
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()

# Configuration
INPUT_FILE = "../description/js_file_chunks_cleaned.json"
OUTPUT_FILE = "../description/js_file_chunks_enhanced_descriptions.json"
GROQ_API_KEY = os.environ.get("GROQ_API_KEY")  # Loaded from .env file

# Groq configuration
GROQ_MODEL = "llama-3.3-70b-versatile"  # Fast and good quality. Alternatives: "llama-3.1-70b-versatile", "llama-3.1-8b-instant"
MAX_TOKENS = 300  # Reduced for more concise descriptions
TEMPERATURE = 0.2  # Lower = more focused, higher = more creative

# Rate limiting
REQUESTS_PER_MINUTE = 30
DELAY_BETWEEN_REQUESTS = 60 / REQUESTS_PER_MINUTE  # seconds

def create_enhancement_prompt(chunk):
    """Create a detailed prompt for Groq API based on chunk type"""
    
    chunk_type = chunk.get("chunk_type", "")
    code_snippet = chunk.get("code_snippet", "")
    function_name = chunk.get("function_name", chunk.get("file_name", ""))
    parameters = chunk.get("parameters", [])
    dom_selectors = chunk.get("parent_file_dom_selectors", [])
    ajax_calls = chunk.get("related_ajax_calls", [])
    
    if chunk_type == "js_function":
        prompt = f"""Analyze this JavaScript function and provide a concise technical description (80-120 words):

Function Name: {function_name}
Parameters: {', '.join(parameters) if parameters else 'None'}

Code:
```javascript
{code_snippet}
```

DOM Selectors Used: {', '.join(dom_selectors[:10]) if dom_selectors else 'None'}
Related AJAX Calls: {', '.join(ajax_calls) if ajax_calls else 'None'}

Write ONE focused paragraph covering:
• PRIMARY PURPOSE - What it does and why
• KEY OPERATIONS - Main logic steps (conditionals, loops, data transforms)
• DOM/DATA - Specific elements accessed/modified and data flow
• AJAX/CALLBACKS - Endpoints called and response handling
• RETURN/SIDE EFFECTS - What it returns and any state changes

Be SPECIFIC about:
- Actual DOM selector names (e.g., #userId, .status-btn)
- Exact conditions checked (e.g., if response.status === 'success')
- Data transformations (e.g., JSON.stringify, split, map)
- Error paths (else branches, try/catch)

Keep it concise but technical - avoid generic phrases like "performs operations" or "handles data"."""

    elif chunk_type == "js_ajax_endpoint":
        endpoint_url = chunk.get("endpoint_url", "")
        http_method = chunk.get("http_method", "")
        
        prompt = f"""Analyze this AJAX endpoint and provide a concise technical description (60-90 words):

Endpoint: {http_method} {endpoint_url}
File: {chunk.get('file_name', '')}

Context:
```javascript
{code_snippet if code_snippet else 'No code snippet available'}
```

Write ONE paragraph covering:
• BUSINESS PURPOSE - What operation this performs in the banking workflow
• REQUEST DATA - Specific parameters sent (formId, userId, status, etc.)
• RESPONSE HANDLING - Success/error flows and callback functions
• UI IMPACT - Which DOM elements get updated after response

Be specific about parameter names and response status handling."""

    elif chunk_type == "js_file":
        prompt = f"""Analyze this JavaScript file overview and provide a comprehensive technical summary (100-150 words):

File: {function_name}

Code Preview:
```javascript
{code_snippet}and provide a concise technical summary (70-100 words):

File: {function_name}

Code Preview:
```javascript
{code_snippet}
```

Functions: {len(chunk.get('functions', []))}
DOM Selectors: {', '.join(dom_selectors[:10]) if dom_selectors else 'None'}

Write ONE paragraph covering:
• FILE PURPOSE - Main responsibility in the application
• KEY FUNCTIONS - Primary operations (data fetching, form handling, validation)
• UI SCOPE - Main DOM elements/components managed
• API INTERACTIONS - Backend endpoints called
• BANKING DOMAIN - Which business features this supports (user mgmt, amendments, approvals)

Focus on high-level purpose and key
{code_snippet}
```

Provide a comprehensive technical description covering purpose, implementation, data flow, and integration points."""

    return prompt

def should_enhance_chunk(chunk):
    """Determine if a chunk needs description enhancement"""
    
    description = chunk.get("description", "")
    code_snippet = chunk.get("code_snippet", "")
    
    # Skip if no code snippet available (can't analyze)
    if not code_snippet or len(code_snippet.strip()) < 20:
        return False
    
    # Enhance if description is too short
    if len(description) < 100:
        return True
    
    # Enhance if description is generic/minimal
    generic_patterns = [
        "accepts parameters:",
        "The function",
        "processes the response",
        "performs",
        "retrieves",
        "Updates",
        "Fetches",
        "Saves"
    ]
    
    # Check if description starts with generic patterns and is short
    for pattern in generic_patterns:
        if pattern in description and len(description) < 200:
            return True
    
    return False

def enhance_description_with_groq(chunk, client):
    """Use Groq API to generate enhanced description"""
    
    try:
        prompt = create_enhancement_prompt(chunk)
        
        chat_completion = client.chat.completions.create(
            messages=[
                {
                    "role": "system",
                    "content": "You are a senior software engineer specializing in JavaScript and banking applications. Provide CONCISE, technical code descriptions focusing on specific implementation details. Write ONE focused paragraph (80-120 words). Use exact DOM selectors, parameter names, and condition checks. Avoid generic phrases - be specific and technical."
                },
                {
                    "role": "user",
                    "content": prompt
                }
            ],
            model=GROQ_MODEL,
            temperature=TEMPERATURE,
            max_tokens=MAX_TOKENS,
        )
        
        enhanced_description = chat_completion.choices[0].message.content.strip()
        return enhanced_description
        
    except Exception as e:
        print(f"  ⚠️  Error enhancing description: {str(e)}")
        return None

def process_chunks():
    """Main processing function"""
    
    # Check for API key
    if not GROQ_API_KEY:
        print("❌ Error: GROQ_API_KEY not found!")
        print("\nMake sure you have a .env file in the project root with:")
        print("  GROQ_API_KEY=your-api-key-here")
        print("\nOr set it as environment variable:")
        print("  export GROQ_API_KEY='your-api-key-here'")
        print("\nGet your API key from: https://console.groq.com/")
        return
    
    # Initialize Groq client
    client = Groq(api_key=GROQ_API_KEY)
    
    # Load chunks
    input_path = Path(__file__).parent / INPUT_FILE
    print(f"📂 Loading chunks from: {input_path}")
    
    with open(input_path, 'r', encoding='utf-8') as f:
        chunks = json.load(f)
    
    print(f"✅ Loaded {len(chunks)} chunks")
    
    # Identify chunks needing enhancement
    chunks_to_enhance = []
    for i, chunk in enumerate(chunks):
        if should_enhance_chunk(chunk):
            chunks_to_enhance.append(i)
    
    print(f"\n🔍 Found {len(chunks_to_enhance)} chunks needing enhancement")
    
    if len(chunks_to_enhance) == 0:
        print("✨ All descriptions are already detailed!")
        return
    
    # Confirmation
    print(f"\n⚠️  This will make ~{len(chunks_to_enhance)} API calls to Groq")
    print(f"⏱️  Estimated time: {len(chunks_to_enhance) * DELAY_BETWEEN_REQUESTS / 60:.1f} minutes")
    
    response = input("\n🤔 Continue? (y/n): ")
    if response.lower() != 'y':
        print("❌ Aborted by user")
        return
    
    # Process chunks
    print(f"\n🚀 Starting enhancement process...\n")
    
    enhanced_count = 0
    failed_count = 0
    
    for idx, chunk_idx in enumerate(chunks_to_enhance, 1):
        chunk = chunks[chunk_idx]
        
        chunk_type = chunk.get("chunk_type", "")
        chunk_name = chunk.get("function_name", chunk.get("file_name", "unknown"))
        
        print(f"[{idx}/{len(chunks_to_enhance)}] Enhancing: {chunk_type} - {chunk_name}")
        
        # Generate enhanced description
        enhanced_desc = enhance_description_with_groq(chunk, client)
        
        if enhanced_desc:
            chunk["description"] = enhanced_desc
            chunk["description_enhanced"] = True
            chunk["description_enhanced_date"] = time.strftime("%Y-%m-%d")
            enhanced_count += 1
            print(f"  ✅ Enhanced ({len(enhanced_desc)} chars)")
        else:
            failed_count += 1
            print(f"  ❌ Failed to enhance")
        
        # Rate limiting
        if idx < len(chunks_to_enhance):
            time.sleep(DELAY_BETWEEN_REQUESTS)
    
    # Save enhanced chunks
    output_path = Path(__file__).parent / OUTPUT_FILE
    print(f"\n💾 Saving enhanced chunks to: {output_path}")
    
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(chunks, f, indent=2, ensure_ascii=False)
    
    # Summary
    print(f"\n{'='*60}")
    print(f"✨ ENHANCEMENT COMPLETE")
    print(f"{'='*60}")
    print(f"Total chunks: {len(chunks)}")
    print(f"Enhanced: {enhanced_count}")
    print(f"Failed: {failed_count}")
    print(f"Unchanged: {len(chunks) - len(chunks_to_enhance)}")
    print(f"\n📄 Output file: {output_path}")
    print(f"{'='*60}")

def main():
    """Entry point"""
    print("="*60)
    print("🤖 JavaScript Description Enhancement with Groq API")
    print("="*60)
    
    try:
        process_chunks()
    except KeyboardInterrupt:
        print("\n\n⚠️  Process interrupted by user")
    except Exception as e:
        print(f"\n❌ Error: {str(e)}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    main()
