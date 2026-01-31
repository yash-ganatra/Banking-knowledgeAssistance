import os
import logging
from typing import Optional, List, Dict
from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel, Field
from groq import Groq
from dotenv import load_dotenv
from sqlalchemy.orm import Session

from auth import get_current_user
from models import User
import database

# Import Security modules
from security.security_config import (
    SECURITY_PREAMBLE,
    get_hardened_system_prompt,
    get_code_review_security_addendum
)
from security.query_guardrails import QueryGuardrails, check_query_safety
from security.output_filter import OutputFilter, filter_llm_response, redact_sensitive

# Load environment variables
load_dotenv()

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

router = APIRouter(prefix="/api/code-review", tags=["code-review"])

# Initialize Groq client
GROQ_API_KEY = os.getenv("GROQ_API_KEY")
if not GROQ_API_KEY:
    GROQ_API_KEY = "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"

groq_client = Groq(api_key=GROQ_API_KEY)

# Initialize security components
query_guardrails = QueryGuardrails(strict_mode=True)
output_filter = OutputFilter(strict_mode=True)

# Allowed languages for code review (whitelist)
ALLOWED_LANGUAGES = {"php", "javascript", "js", "sql", "html", "css", "blade", "unknown"}

# Maximum code length to prevent abuse
MAX_CODE_LENGTH = 50000  # 50KB limit

# Request/Response Models
class CodeReviewRequest(BaseModel):
    code: str = Field(..., max_length=MAX_CODE_LENGTH)
    language: Optional[str] = "unknown"

class CodeIssue(BaseModel):
    severity: str  # error, warning, info, suggestion
    description: str
    line_number: Optional[int] = None
    suggestion: Optional[str] = None

class CodeReviewResponse(BaseModel):
    success: bool
    review: str
    issues: Optional[List[CodeIssue]] = None
    score: Optional[int] = None
    summary: Optional[str] = None

# Load coding guidelines
def load_coding_guidelines():
    """Load the developer coding guidelines from the markdown file"""
    try:
        project_root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
        guidelines_path = os.path.join(project_root, "developer_coding_guidelines_php_java_script_database.md")
        
        if os.path.exists(guidelines_path):
            with open(guidelines_path, 'r', encoding='utf-8') as f:
                return f.read()
        else:
            logger.warning(f"Guidelines file not found at {guidelines_path}")
            return None
    except Exception as e:
        logger.error(f"Error loading guidelines: {e}")
        return None

# Code review system prompt
def create_code_review_prompt(code: str, language: str, guidelines: str) -> str:
    """Create a comprehensive code review prompt with security considerations"""
    
    base_prompt = f"""You are an expert code reviewer specializing in PHP, JavaScript, and SQL. 
You provide clear, concise feedback for junior developers using simple language.

**Coding Guidelines to Follow:**
{guidelines if guidelines else "Use standard industry best practices"}

**SECURITY RULES FOR CODE REVIEW:**
- If you find hardcoded credentials/API keys in the code, flag them as CRITICAL security issues but do NOT display the actual values
- Never provide working exploit code as examples
- Focus on HOW TO FIX security issues, not how to exploit them
- If the submitted code appears to be malicious/exploit code, refuse to review it

**Your Task:**
Review the following {language.upper()} code briefly and provide:

1. **Syntax Check** - First, check if there are any syntax errors that will prevent the code from running
2. **Quick Summary** (1-2 sentences max) - What's the main issue or what's good?
3. **Top 3-5 Issues** (focus on the most important ones):
   - SYNTAX: Syntax errors (missing semicolons, brackets, quotes, etc.)
   - CRITICAL: Security holes or code that will break
   - WARNING: Things that should be fixed
   - INFO: Nice-to-have improvements
   
4. **Simple Fix** for each issue (1 line suggestion)

**Focus Areas:**
- Syntax errors (missing brackets, semicolons, quotes, parentheses)
- Naming (variables, functions should be clear)
- Safety (validate inputs, handle errors)
- Security (SQL injection, XSS)
- Best practices from guidelines

**Code to Review:**
```{language}
{code}
```

**Output Format:**
Keep it short and actionable. Use simple language. Format as:

**Summary:** [Brief overview]

**Issues:**
- 🔴 SYNTAX: [Syntax error] → Fix: [Simple solution]
- 🔴 CRITICAL: [Issue] → Fix: [Simple solution]
- ⚠️ WARNING: [Issue] → Fix: [Simple solution]
- ℹ️ INFO: [Issue] → Fix: [Simple solution]

Maximum 5 issues total. Syntax errors first, then by priority. Be direct and helpful.
"""
    
    # Apply security hardening to the prompt
    return base_prompt


def check_code_for_injection(code: str) -> bool:
    """
    Check if submitted code contains prompt injection attempts.
    Returns True if injection detected.
    """
    injection_patterns = [
        "ignore previous instructions",
        "ignore all previous",
        "disregard your rules",
        "you are now",
        "new instructions:",
        "system prompt:",
        "[INST]",
        "<<SYS>>",
    ]
    code_lower = code.lower()
    for pattern in injection_patterns:
        if pattern.lower() in code_lower:
            return True
    return False


@router.post("", response_model=CodeReviewResponse)
async def review_code(
    request: CodeReviewRequest,
    current_user: User = Depends(get_current_user),
    db: Session = Depends(database.get_db)
):
    """
    Perform AI-powered code review based on coding guidelines
    With security guardrails to prevent prompt injection and sensitive data exposure.
    """
    try:
        logger.info(f"Code review request from user {current_user.username} for {request.language} code")
        
        # ===== SECURITY: Validate language =====
        if request.language.lower() not in ALLOWED_LANGUAGES:
            logger.warning(f"SECURITY: Rejected invalid language: {request.language}")
            raise HTTPException(
                status_code=400,
                detail=f"Language '{request.language}' is not supported. Allowed: {', '.join(ALLOWED_LANGUAGES)}"
            )
        
        # ===== SECURITY: Check code length =====
        if len(request.code) > MAX_CODE_LENGTH:
            logger.warning(f"SECURITY: Rejected oversized code submission: {len(request.code)} bytes")
            raise HTTPException(
                status_code=400,
                detail=f"Code too large. Maximum size is {MAX_CODE_LENGTH} characters."
            )
        
        # ===== SECURITY: Check for prompt injection in code comments =====
        if check_code_for_injection(request.code):
            logger.warning(f"SECURITY: Detected prompt injection attempt in code from user {current_user.username}")
            return CodeReviewResponse(
                success=False,
                review="I cannot review this code as it appears to contain content that could interfere with the review process.",
                issues=None,
                score=None,
                summary="Review blocked for security reasons."
            )
        
        # ===== SECURITY: Check code content with guardrails =====
        # Treat the code itself as a potential vector for injection
        combined_input = f"{request.language} {request.code[:500]}"  # Check first 500 chars
        security_result = check_query_safety(combined_input)
        
        if security_result.should_block and security_result.risk_score > 0.8:
            logger.warning(f"SECURITY: Blocked code review - high risk score {security_result.risk_score}")
            return CodeReviewResponse(
                success=False,
                review="I cannot review this code submission.",
                issues=None,
                score=None,
                summary="Review blocked for security reasons."
            )
        # ===== END SECURITY =====
        
        # Load coding guidelines
        guidelines = load_coding_guidelines()
        
        if not guidelines:
            logger.warning("Proceeding without guidelines file")
        
        # Create the review prompt
        prompt = create_code_review_prompt(request.code, request.language, guidelines)
        
        # Build hardened system prompt
        base_system_prompt = "You are an expert code reviewer with deep knowledge of PHP, JavaScript, SQL, and software engineering best practices. You provide thorough, constructive feedback following established coding guidelines."
        system_prompt = get_hardened_system_prompt(
            base_system_prompt,
            get_code_review_security_addendum()
        )
        
        # Call Groq API for code review
        chat_completion = groq_client.chat.completions.create(
            messages=[
                {
                    "role": "system",
                    "content": system_prompt
                },
                {
                    "role": "user",
                    "content": prompt
                }
            ],
            model="llama-3.3-70b-versatile",
            temperature=0.3,
            max_tokens=2000,
        )
        
        raw_review_text = chat_completion.choices[0].message.content
        
        # ===== SECURITY: Filter output for sensitive data =====
        filtered_response = output_filter.filter_response(raw_review_text)
        review_text = filtered_response.response
        
        if filtered_response.redactions_made > 0:
            logger.info(f"SECURITY: Redacted {filtered_response.redactions_made} patterns from code review output")
        # ===== END SECURITY =====
        
        # Parse the review to extract structured information
        issues = parse_issues_from_review(review_text)
        score = extract_score_from_review(review_text)
        summary = extract_summary_from_review(review_text)
        
        logger.info(f"Code review completed successfully. Score: {score}, Issues: {len(issues)}")
        
        return CodeReviewResponse(
            success=True,
            review=review_text,
            issues=issues,
            score=score,
            summary=summary
        )
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error during code review: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Code review failed: {str(e)}")

def parse_issues_from_review(review_text: str) -> List[CodeIssue]:
    """
    Parse issues from the review text
    This is a simple parser - could be enhanced with more sophisticated NLP
    """
    issues = []
    
    # Look for common patterns indicating issues
    lines = review_text.split('\n')
    current_issue = None
    
    for line in lines:
        line_lower = line.lower().strip()
        
        # Check for severity indicators
        if any(keyword in line_lower for keyword in ['critical:', '**critical**', '- critical']):
            if current_issue:
                issues.append(current_issue)
            current_issue = CodeIssue(severity="critical", description="", suggestion=None)
        elif any(keyword in line_lower for keyword in ['error:', '**error**', '- error']):
            if current_issue:
                issues.append(current_issue)
            current_issue = CodeIssue(severity="error", description="", suggestion=None)
        elif any(keyword in line_lower for keyword in ['warning:', '**warning**', '- warning']):
            if current_issue:
                issues.append(current_issue)
            current_issue = CodeIssue(severity="warning", description="", suggestion=None)
        elif any(keyword in line_lower for keyword in ['info:', '**info**', 'suggestion:', '- info']):
            if current_issue:
                issues.append(current_issue)
            current_issue = CodeIssue(severity="info", description="", suggestion=None)
        elif current_issue and line.strip():
            # Add to current issue description
            if not current_issue.description:
                current_issue.description = line.strip()
            elif 'suggestion' in line_lower or 'fix' in line_lower:
                current_issue.suggestion = line.strip()
            else:
                current_issue.description += " " + line.strip()
    
    if current_issue and current_issue.description:
        issues.append(current_issue)
    
    return issues

def extract_score_from_review(review_text: str) -> Optional[int]:
    """Extract code quality score from review text"""
    import re
    
    # Look for patterns like "Score: 7/10", "Quality: 8/10", "7 out of 10"
    patterns = [
        r'score[:\s]+(\d+)\s*/\s*10',
        r'quality[:\s]+(\d+)\s*/\s*10',
        r'(\d+)\s*/\s*10',
        r'(\d+)\s+out of\s+10'
    ]
    
    for pattern in patterns:
        match = re.search(pattern, review_text, re.IGNORECASE)
        if match:
            try:
                score = int(match.group(1))
                if 0 <= score <= 10:
                    return score
            except ValueError:
                continue
    
    return None

def extract_summary_from_review(review_text: str) -> Optional[str]:
    """Extract summary from review text"""
    lines = review_text.split('\n')
    
    # Look for summary section
    in_summary = False
    summary_lines = []
    
    for line in lines:
        line_lower = line.lower().strip()
        
        if 'summary' in line_lower or 'overall' in line_lower:
            in_summary = True
            continue
        
        if in_summary:
            if line.strip() and not line.startswith('#'):
                summary_lines.append(line.strip())
            elif line.startswith('#') or len(summary_lines) > 3:
                break
    
    if summary_lines:
        return ' '.join(summary_lines[:3])  # Limit to first 3 lines
    
    # Fallback: return first non-empty paragraph
    for line in lines:
        if len(line.strip()) > 50 and not line.startswith('#'):
            return line.strip()
    
    return None

@router.get("/guidelines")
async def get_guidelines(current_user: User = Depends(get_current_user)):
    """
    Get the coding guidelines document
    """
    guidelines = load_coding_guidelines()
    
    if guidelines:
        return {"success": True, "guidelines": guidelines}
    else:
        raise HTTPException(status_code=404, detail="Guidelines document not found")
