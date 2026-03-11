#!/usr/bin/env python3
"""
Enhance Blade Chunks with Logic Descriptions
Uses Groq LLM to generate functional descriptions for Blade templates.
"""

import json
import os
import time
from pathlib import Path
from groq import Groq
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Configuration
INPUT_FILE = "../chunks/blade_views_raw.json"
OUTPUT_FILE = "../chunks/blade_views_enhanced.json"
GROQ_API_KEY = os.environ.get("GROQ_API_KEY", "")

GROQ_MODEL = "openai/gpt-oss-120b"
MAX_TOKENS = 400
TEMPERATURE = 0.2

# Rate limiting
REQUESTS_PER_MINUTE = 30
DELAY_BETWEEN_REQUESTS = 60 / REQUESTS_PER_MINUTE

def create_enhancement_prompt(chunk):
    """Create prompt for Blade template analysis"""
    
    code_snippet = chunk.get("content", "")
    filename = chunk.get("file_name", "")
    section = chunk.get("section_name", "")
    
    prompt = f"""Analyze this Laravel Blade/HTML view code and provide a concise functional description (50-80 words).

File: {filename}
Section: {section}

Code:
```html
{code_snippet[:2000]}  # Truncate if too long to fit context
```

Write ONE focused paragraph covering:
• UI PURPOSE - What does this view display? (e.g., User Login Form, Dashboard Stats Widget)
• KEY ELEMENTS - Main interaction points (forms, buttons, tables)
• DATA DISPLAYED - What dynamic data is shown ({{ $variable }})
• USER ACTION - What can the user do here?

Format as a searchable description. e.g., "User authentication form allowing email and password entry with CSRF protection. Displays validation errors and includes a 'Forgot Password' link."
"""
    return prompt

def enhance_description_with_groq(chunk, client):
    try:
        prompt = create_enhancement_prompt(chunk)
        
        chat_completion = client.chat.completions.create(
            messages=[
                {
                    "role": "system",
                    "content": "You are an expert Laravel developer. Generate concise, searchable descriptions for UI views. Focus on FUNCTIONALITY and USER INTENT."
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
        
        return chat_completion.choices[0].message.content.strip()
        
    except Exception as e:
        print(f"  ⚠️  Error enhancing: {str(e)}")
        return None

def process_chunks():
    if not GROQ_API_KEY:
        print("❌ Error: GROQ_API_KEY not found")
        return

    client = Groq(api_key=GROQ_API_KEY)
    
    input_path = Path(__file__).parent / INPUT_FILE
    print(f"📂 Loading chunks from: {input_path}")
    
    with open(input_path, 'r', encoding='utf-8') as f:
        chunks = json.load(f)
    
    print(f"✅ Loaded {len(chunks)} chunks")
    
    # Filter for chunks that have content
    chunks_to_enhance = [i for i, c in enumerate(chunks) if c.get('content') and len(c['content']) > 20]
    
    print(f"🔍 Found {len(chunks_to_enhance)} chunks needing enhancement")
    print(f"⚡ Auto-proceeding with enhancement (Hybrid Strategy)...")
    
    enhanced_count = 0
    
    for idx, chunk_idx in enumerate(chunks_to_enhance, 1):
        chunk = chunks[chunk_idx]
        print(f"[{idx}/{len(chunks_to_enhance)}] Enhancing: {chunk['file_name']}")
        
        description = enhance_description_with_groq(chunk, client)
        
        if description:
            chunk['description'] = description
            chunk['description_enhanced'] = True
            enhanced_count += 1
            print(f"  ✅ Enhanced: {description[:60]}...")
        
        # Rate limit
        if idx < len(chunks_to_enhance):
            time.sleep(DELAY_BETWEEN_REQUESTS)
            
    # Save
    output_path = Path(__file__).parent / OUTPUT_FILE
    output_path.parent.mkdir(parents=True, exist_ok=True)
    
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(chunks, f, indent=2)
        
    print(f"\n💾 Saved {enhanced_count} enhanced chunks to: {output_path}")

if __name__ == "__main__":
    process_chunks()
