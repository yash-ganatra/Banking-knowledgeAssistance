#!/usr/bin/env python3
"""
Description Generation Prompts

Centralized LLM prompts extracted from the existing enhancement scripts:
- utils/enhance_blade_chunks.py (Blade descriptions)
- utils/enhance_descriptions_with_groq.py (JS descriptions)
- PHP descriptions (from Colab notebooks, same pattern as JS)

These are the EXACT prompts used in the original bulk pipelines.
The ingestion pipeline reuses them for consistency.
"""


# =============================================================================
# BLADE DESCRIPTION PROMPTS (from utils/enhance_blade_chunks.py)
# Model: Groq | Temp: 0.2 | Max tokens: 400
# =============================================================================

BLADE_DESCRIPTION_SYSTEM = (
    "You are an expert Laravel developer. Generate concise, searchable "
    "descriptions for UI views. Focus on FUNCTIONALITY and USER INTENT."
)


def get_blade_description_prompt(code_snippet: str, filename: str, section: str) -> str:
    """
    Create prompt for Blade template description generation.
    Matches exactly: utils/enhance_blade_chunks.py -> create_enhancement_prompt()
    """
    # Truncate code to 2000 chars, same as original
    truncated_code = code_snippet[:2000]

    return f"""Analyze this Laravel Blade/HTML view code and provide a concise functional description (50-80 words).

File: {filename}
Section: {section}

Code:
```html
{truncated_code}
```

Write ONE focused paragraph covering:
• UI PURPOSE - What does this view display? (e.g., User Login Form, Dashboard Stats Widget)
• KEY ELEMENTS - Main interaction points (forms, buttons, tables)
• DATA DISPLAYED - What dynamic data is shown ({{{{ $variable }}}})
• USER ACTION - What can the user do here?

Format as a searchable description. e.g., "User authentication form allowing email and password entry with CSRF protection. Displays validation errors and includes a 'Forgot Password' link."
"""


# =============================================================================
# JS DESCRIPTION PROMPTS (from utils/enhance_descriptions_with_groq.py)
# Model: Groq llama-3.3-70b-versatile | Temp: 0.2 | Max tokens: 300
# =============================================================================

JS_DESCRIPTION_SYSTEM = (
    "You are a senior software engineer specializing in JavaScript and banking "
    "applications. Provide CONCISE, technical code descriptions focusing on specific "
    "implementation details. Write ONE focused paragraph (80-120 words). Use exact "
    "DOM selectors, parameter names, and condition checks. Avoid generic phrases - "
    "be specific and technical."
)


def get_js_function_description_prompt(
    function_name: str,
    parameters: list,
    code_snippet: str,
    dom_selectors: list = None,
    ajax_calls: list = None,
) -> str:
    """
    Create prompt for JS function description generation.
    Matches exactly: utils/enhance_descriptions_with_groq.py -> create_enhancement_prompt()
    for chunk_type == "js_function"
    """
    params_str = ", ".join(parameters) if parameters else "None"
    dom_str = ", ".join((dom_selectors or [])[:10]) if dom_selectors else "None"
    ajax_str = ", ".join(ajax_calls) if ajax_calls else "None"

    return f"""Analyze this JavaScript function and provide a concise technical description (80-120 words):

Function Name: {function_name}
Parameters: {params_str}

Code:
```javascript
{code_snippet}
```

DOM Selectors Used: {dom_str}
Related AJAX Calls: {ajax_str}

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


def get_js_ajax_description_prompt(
    endpoint_url: str,
    http_method: str,
    file_name: str,
    code_snippet: str,
) -> str:
    """
    Create prompt for JS AJAX endpoint description.
    Matches: utils/enhance_descriptions_with_groq.py for chunk_type == "js_ajax_endpoint"
    """
    return f"""Analyze this AJAX endpoint and provide a concise technical description (60-90 words):

Endpoint: {http_method} {endpoint_url}
File: {file_name}

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


def get_js_file_description_prompt(file_name: str, code_snippet: str) -> str:
    """
    Create prompt for JS file-level description.
    Matches: utils/enhance_descriptions_with_groq.py for chunk_type == "js_file"
    """
    return f"""Analyze this JavaScript file overview and provide a comprehensive technical summary (100-150 words):

File: {file_name}

Code Preview:
```javascript
{code_snippet}
```

Provide a comprehensive technical description covering purpose, implementation, data flow, and integration points."""


# =============================================================================
# PHP DESCRIPTION PROMPTS
# Model: Groq llama-3.3-70b-versatile | Temp: 0.2 | Max tokens: 400
# =============================================================================

PHP_DESCRIPTION_SYSTEM = (
    "You are a senior software engineer specializing in PHP Laravel and banking "
    "applications. Provide CONCISE, technical code descriptions. Write ONE focused "
    "paragraph. Use exact class names, method names, and parameter details. "
    "Avoid generic phrases - be specific and technical."
)


def get_php_class_description_prompt(
    class_name: str,
    file_path: str,
    method_names: list,
    dependencies: list,
    code_snippet: str = None,
) -> str:
    """
    Create prompt for PHP class description generation.
    """
    methods_str = ", ".join(method_names) if method_names else "None"
    deps_str = ", ".join(dependencies) if dependencies else "None"
    code_section = f"\nCode:\n```php\n{code_snippet[:3000]}\n```" if code_snippet else ""

    return f"""Analyze this PHP class and provide a concise technical description (80-120 words):

Class: {class_name}
File: {file_path}
Methods: {methods_str}
Dependencies: {deps_str}
{code_section}

Write ONE focused paragraph covering:
• CLASS PURPOSE - Primary responsibility in the Laravel application
• KEY METHODS - What the main methods do
• DEPENDENCIES - How it interacts with other classes/services
• BUSINESS DOMAIN - Which banking feature this supports

Be specific about class responsibilities, method purposes, and data flow."""


def get_php_method_description_prompt(
    class_name: str,
    method_name: str,
    parameters: str,
    return_type: str,
    file_path: str,
    code_snippet: str = None,
) -> str:
    """
    Create prompt for PHP method description generation.
    """
    code_section = f"\nCode:\n```php\n{code_snippet[:3000]}\n```" if code_snippet else ""

    return f"""Analyze this PHP method and provide a concise technical description (60-100 words):

Method: {class_name}::{method_name}
Parameters: {parameters if parameters else 'None'}
Returns: {return_type if return_type else 'void'}
File: {file_path}
{code_section}

Write ONE focused paragraph covering:
• METHOD PURPOSE - What it does in the application workflow
• PARAMETERS - What each parameter is used for
• RETURN VALUE - What it returns and when
• BUSINESS LOGIC - Key validation, processing, or data transformation steps
• SIDE EFFECTS - Database writes, cache updates, event dispatches

Be specific about parameter handling, conditions checked, and data flow."""
