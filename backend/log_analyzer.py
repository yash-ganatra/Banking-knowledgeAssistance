"""
Log Analyzer Module for Banking Knowledge Assistant
Parses, deduplicates, and analyzes Laravel/PHP log files using the existing
RAG pipeline (vector DB + LLM) to provide root cause analysis for errors.
"""

import re
import hashlib
import logging
import asyncio
from typing import List, Dict, Optional, Any, Tuple
from datetime import datetime
from dataclasses import dataclass, field

logger = logging.getLogger(__name__)


# ============================================================
# Data Models
# ============================================================

@dataclass
class ParsedError:
    """A single error entry extracted from the log file."""
    timestamp: str
    level: str
    error_message: str
    exception_class: Optional[str]
    # The actual application-level file that caused the error (not vendor)
    origin_file: Optional[str]
    origin_line: Optional[int]
    triggering_function: Optional[str]
    app_stack_frames: List[str]
    raw_block: str
    # View-related metadata (for Blade template errors)
    view_file: Optional[str] = None
    # The raw origin from the exception (may be a vendor file)
    raw_exception_file: Optional[str] = None
    raw_exception_line: Optional[int] = None


@dataclass
class DeduplicatedError:
    """A group of identical errors collapsed into one entry."""
    fingerprint: str
    error: ParsedError
    occurrence_count: int
    first_seen: str
    last_seen: str
    timestamps: List[str] = field(default_factory=list)


@dataclass
class AnalyzedError:
    """Final output: a deduplicated error with LLM root cause analysis."""
    error: DeduplicatedError
    code_context: str
    root_cause_analysis: str
    code_files_referenced: List[str]


# ============================================================
# Log Parser
# ============================================================

class LogParser:
    """
    Regex-based parser for Laravel log format.
    
    Laravel log format:
        [YYYY-MM-DD HH:MM:SS] environment.LEVEL: message {"exception":"..."}
    
    Each error spans multiple lines (typically ~50 lines because of stack traces).
    The parser splits on timestamp patterns to form complete error blocks.
    """
    
    # Matches the start of a log entry: [2026-02-07 09:44:16] local.ERROR:
    TIMESTAMP_PATTERN = re.compile(
        r'^\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\]\s+(\w+)\.(\w+):\s*(.*)',
        re.MULTILINE
    )
    
    # Extracts the origin file and line from the "at" clause in exception message
    # e.g. "at /var/www/html/APP_DEV/app/Http/Controllers/Bank/AddAccountController.php:5790)"
    ORIGIN_PATTERN = re.compile(
        r'at\s+(/[^\s:)]+):(\d+)\)'
    )
    
    # Pattern to extract app-level file paths from the "at" clause or stack frames
    # ONLY matches files inside /app/ (not vendor/)
    APP_ORIGIN_PATTERN = re.compile(
        r'(/var/www/html/[^/]+/app/[^\s:)\"]+\.php)[:\s]+(\d+)'
    )
    
    # Extracts the exception class from the JSON block
    # e.g. ErrorException(code: 0) or ParseError(code: 0)
    EXCEPTION_CLASS_PATTERN = re.compile(
        r'\(([A-Za-z\\]+(?:Exception|Error|Throwable))\(code:'
    )
    
    # Extracts app-level stack frames with full path (skip vendor/ paths)
    APP_FRAME_PATTERN = re.compile(
        r'#\d+\s+(/var/www/html/[^/]+/app/[^\s]+)\((\d+)\):\s*(.*?)(?:\r?\n|$)'
    )
    
    # Extract view file from view-related errors
    VIEW_FILE_PATTERN = re.compile(
        r'"view":"([^"]+)"'
    )
    
    # Extract function name from stack trace (the called function at origin)
    FUNCTION_PATTERN = re.compile(
        r'\\(\w+)->(\w+)\(\)|\\(\w+)::(\w+)\(\)'
    )

    # Framework/middleware classes to skip when extracting triggering function
    FRAMEWORK_CLASSES = frozenset({
        'HandleExceptions', 'Pipeline', 'Router', 'Kernel', 'Route',
        'ControllerDispatcher', 'Controller', 'Filesystem', 'ClassLoader',
        'File', 'UploadedFile', 'ResponseFactory', 'Response',
        'CompiledEngine', 'PhpEngine', 'CompilerEngine', 'FileEngine',
        'ViewFactory', 'View', 'Factory', 'TransformsRequest',
        'JwtMiddleware', 'BinaryFileResponse', 'SubstituteBindings'
    })
    
    def parse(self, log_content: str) -> List[ParsedError]:
        """
        Parse a complete log file into structured error entries.
        
        Args:
            log_content: Raw text content of the log file
            
        Returns:
            List of ParsedError objects
        """
        blocks = self._split_into_blocks(log_content)
        errors = []
        
        for block in blocks:
            parsed = self._parse_block(block)
            if parsed:
                errors.append(parsed)
        
        logger.info(f"Parsed {len(errors)} error entries from log file")
        return errors
    
    def _split_into_blocks(self, content: str) -> List[Dict[str, str]]:
        """
        Split log content into individual error blocks using timestamp anchors.
        """
        blocks = []
        matches = list(self.TIMESTAMP_PATTERN.finditer(content))
        
        for i, match in enumerate(matches):
            start = match.start()
            end = matches[i + 1].start() if i + 1 < len(matches) else len(content)
            
            raw_block = content[start:end].strip()
            blocks.append({
                'timestamp': match.group(1),
                'environment': match.group(2),
                'level': match.group(3),
                'message_start': match.group(4),
                'raw_block': raw_block
            })
        
        return blocks
    
    def _parse_block(self, block: Dict[str, str]) -> Optional[ParsedError]:
        """Parse a single log block into a structured ParsedError."""
        level = block['level'].upper()
        
        # Only parse ERROR-level entries
        if level != 'ERROR':
            return None
        
        raw = block['raw_block']
        timestamp = block['timestamp']
        error_message = block['message_start'].strip()
        
        # Clean up the error message — take only the portion before the JSON blob
        json_start = error_message.find('{"exception"')
        if json_start > 0:
            error_message = error_message[:json_start].strip()
        # Clean trailing braces (but NOT quotes — they contain key names like "scheme_code")
        error_message = error_message.rstrip(' {}')
        
        # Extract exception class
        exception_class = None
        exc_match = self.EXCEPTION_CLASS_PATTERN.search(raw)
        if exc_match:
            exception_class = exc_match.group(1).split('\\')[-1]  # Get short name
        
        # Extract raw exception origin (may be vendor file)
        raw_exception_file = None
        raw_exception_line = None
        origin_match = self.ORIGIN_PATTERN.search(raw)
        if origin_match:
            raw_exception_file = origin_match.group(1)
            raw_exception_line = int(origin_match.group(2))
        
        # Extract app-level stack frames (skip vendor/ lines)
        app_frames = self._extract_app_frames(raw)
        
        # INTELLIGENCE: Find the REAL app-level origin (not vendor)
        # Prioritize: app-level "at" reference > first app stack frame
        origin_file, origin_line = self._extract_app_origin(raw, app_frames)
        
        # Extract triggering function from app-level context
        triggering_function = self._extract_function(raw)
        
        # Extract view file if present
        view_file = None
        view_match = self.VIEW_FILE_PATTERN.search(raw)
        if view_match:
            view_file = view_match.group(1)
        
        # INTELLIGENCE: For ViewException errors, the REAL origin is the blade template
        # The view_file IS the actual file that caused the error
        if exception_class in ('ViewException',) and view_file:
            # Extract the blade file name and try to find its line from the error
            blade_line_match = re.search(r'\.blade\.php:(\d+)', raw)
            if blade_line_match:
                origin_file = view_file
                origin_line = int(blade_line_match.group(1))
            elif view_file:
                origin_file = view_file
        
        return ParsedError(
            timestamp=timestamp,
            level=level,
            error_message=error_message,
            exception_class=exception_class,
            origin_file=origin_file,
            origin_line=origin_line,
            triggering_function=triggering_function,
            app_stack_frames=app_frames[:5],  # Keep top 5 app frames max
            raw_block=raw[:800],  # Keep first 800 chars for reference
            view_file=view_file,
            raw_exception_file=raw_exception_file,
            raw_exception_line=raw_exception_line
        )
    
    def _extract_app_origin(self, raw: str, app_frames: List[str]) -> Tuple[Optional[str], Optional[int]]:
        """
        Extract the true application-level origin of the error.
        
        Strategy:
        1. Look for app-level file paths in the "at" clause of the exception message
        2. If not found there, use the first app-level stack frame
        3. Falls back to the raw exception origin (may be vendor) as last resort
        """
        # Strategy 1: Find app-level file in the main error body (first few lines)
        app_matches = list(self.APP_ORIGIN_PATTERN.finditer(raw))
        if app_matches:
            # Return the first app-level match
            return app_matches[0].group(1), int(app_matches[0].group(2))
        
        # Strategy 2: Extract from first app stack frame
        if app_frames:
            # Parse the formatted frame: "app/Http/Controllers/Bank/AddAccountController.php:811 → ..."
            frame = app_frames[0]
            # Extract file and line from the frame format
            frame_match = re.match(r'app/(.+?):(\d+)', frame)
            if frame_match:
                # Reconstruct full path pattern for the file
                return f"/var/www/html/APP_DEV/app/{frame_match.group(1)}", int(frame_match.group(2))
        
        # Strategy 3: Fall back to raw exception origin
        origin_match = self.ORIGIN_PATTERN.search(raw)
        if origin_match:
            return origin_match.group(1), int(origin_match.group(2))
        
        return None, None
    
    def _extract_function(self, raw: str) -> Optional[str]:
        """Extract the function name that triggered the error, preferring app-level functions."""
        # Look for Controller->method() patterns in stack frames
        func_matches = self.FUNCTION_PATTERN.finditer(raw)
        for m in func_matches:
            # Instance method: Class->method()
            if m.group(1) and m.group(2):
                class_name = m.group(1)
                method_name = m.group(2)
                if class_name not in self.FRAMEWORK_CLASSES:
                    return f"{class_name}->{method_name}()"
            # Static method: Class::method()
            if m.group(3) and m.group(4):
                class_name = m.group(3)
                method_name = m.group(4)
                if class_name not in self.FRAMEWORK_CLASSES:
                    return f"{class_name}::{method_name}()"
        return None
    
    def _extract_app_frames(self, raw: str) -> List[str]:
        """Extract application-level stack frames (excluding vendor/ paths)."""
        frames = []
        for match in self.APP_FRAME_PATTERN.finditer(raw):
            file_path = match.group(1)
            line_num = match.group(2)
            call_info = match.group(3).strip()
            
            # short filename for readability
            short_path = file_path.split('/app/')[-1] if '/app/' in file_path else file_path
            frames.append(f"app/{short_path}:{line_num} → {call_info}")
        
        return frames


# ============================================================
# Log Deduplicator
# ============================================================

class LogDeduplicator:
    """
    Groups identical errors using content-based fingerprinting.
    
    Fingerprint = hash(error_message + origin_file + origin_line)
    This ensures the same root-cause error occurring multiple times
    is treated as one unique issue regardless of timestamp differences.
    """
    
    def deduplicate(self, errors: List[ParsedError]) -> List[DeduplicatedError]:
        """
        Collapse duplicate errors into grouped entries with occurrence counts.
        """
        groups: Dict[str, Dict] = {}
        
        for error in errors:
            fp = self._fingerprint(error)
            
            if fp not in groups:
                groups[fp] = {
                    'fingerprint': fp,
                    'error': error,
                    'count': 0,
                    'timestamps': []
                }
            
            groups[fp]['count'] += 1
            groups[fp]['timestamps'].append(error.timestamp)
        
        # Build deduplicated list sorted by occurrence count
        result = []
        for fp, group in groups.items():
            timestamps = sorted(group['timestamps'])
            result.append(DeduplicatedError(
                fingerprint=group['fingerprint'],
                error=group['error'],
                occurrence_count=group['count'],
                first_seen=timestamps[0],
                last_seen=timestamps[-1],
                timestamps=timestamps
            ))
        
        # Sort by count descending (most frequent errors first)
        result.sort(key=lambda x: x.occurrence_count, reverse=True)
        
        logger.info(f"Deduplicated {len(errors)} errors → {len(result)} unique errors")
        return result
    
    def _fingerprint(self, error: ParsedError) -> str:
        """Generate a content-based fingerprint for deduplication."""
        key = f"{error.error_message}|{error.origin_file}|{error.origin_line}"
        return hashlib.md5(key.encode()).hexdigest()[:12]


# ============================================================
# Log Analyzer (Orchestrator)
# ============================================================

class LogAnalyzer:
    """
    Orchestrator that ties together parsing, deduplication, and LLM analysis.
    
    Uses the existing CodeQueryEngine (PHP vector DB) to retrieve
    relevant code context for each error, then asks the existing
    LLMService to generate a root cause analysis.
    """
    
    # System prompt for error analysis LLM calls
    ERROR_ANALYSIS_PROMPT = """You are a senior Laravel/PHP developer and debugging expert. You are analyzing production error logs alongside the ACTUAL source code from the codebase that was retrieved from the project's vector database.

Your job is to:
1. Match the error to the specific code in the provided context
2. Explain WHY this code causes the error (root cause)
3. Provide a CONCRETE fix with actual code

IMPORTANT CONTEXT:
- The source code chunks below are REAL code from this project's codebase, retrieved by searching the vector database
- The error messages are from production Laravel logs
- Use the actual variable names, function names, and file paths from both the error and the code

Your response MUST follow this exact structure:

## Root Cause
[1-2 sentence explanation of WHY this error happens, referencing the specific code]

## Code Issue
[Show the exact problematic code pattern from the retrieved context and explain what's wrong]

## Suggested Fix
[Provide a CONCRETE code fix — show the actual code change needed. Use php code blocks.]

## Impact
[CRITICAL / HIGH / MEDIUM / LOW] — [Brief justification: how many users affected, what functionality breaks, etc.]

Be very specific. Reference actual variable names, function signatures, and line numbers."""
    
    def __init__(self, php_engine=None, blade_engine=None, llm_service=None):
        """
        Initialize with existing engine instances from main.py.
        
        Args:
            php_engine: CodeQueryEngine for PHP code retrieval
            blade_engine: BladeDescriptionEngine for Blade template retrieval
            llm_service: LLMService for generating root cause analysis
        """
        self.php_engine = php_engine
        self.blade_engine = blade_engine
        self.llm_service = llm_service
        self.parser = LogParser()
        self.deduplicator = LogDeduplicator()
    
    def parse_log(self, log_content: str) -> Dict[str, Any]:
        """
        Parse and deduplicate a log file (no LLM calls).
        Used for the preview/summary endpoint.
        """
        errors = self.parser.parse(log_content)
        deduped = self.deduplicator.deduplicate(errors)
        
        return {
            "total_entries": len(errors),
            "unique_errors": len(deduped),
            "errors": [self._deduped_to_dict(d) for d in deduped]
        }
    
    async def analyze_log(
        self, 
        log_content: str, 
        selected_errors: Optional[List[str]] = None,
        top_k_context: int = 5,
        fast_mode: bool = False,
        concurrency: int = 1
    ) -> Dict[str, Any]:
        """
        Full analysis pipeline: parse → deduplicate → retrieve code → LLM analysis.
        """
        errors = self.parser.parse(log_content)
        deduped = self.deduplicator.deduplicate(errors)
        
        # Filter to selected errors if specified
        if selected_errors:
            deduped = [d for d in deduped if d.fingerprint in selected_errors]
        
        # Faster mode: reduce retrieval breadth for lower latency
        effective_top_k = max(1, min(top_k_context, 3 if fast_mode else top_k_context))

        # Process errors with bounded concurrency (LLM calls are network-bound)
        sem = asyncio.Semaphore(max(1, concurrency))

        async def _run_single(dedup_error: DeduplicatedError) -> AnalyzedError:
            async with sem:
                return await self._analyze_single_error(
                    dedup_error,
                    top_k=effective_top_k,
                    fast_mode=fast_mode
                )

        tasks = [_run_single(d) for d in deduped]
        results = await asyncio.gather(*tasks)
        
        return {
            "total_entries": len(errors),
            "unique_errors": len(deduped),
            "analyzed_count": len(results),
            "analyses": [self._analysis_to_dict(a) for a in results]
        }
    
    async def _analyze_single_error(
        self, 
        dedup_error: DeduplicatedError,
        top_k: int = 5,
        fast_mode: bool = False
    ) -> AnalyzedError:
        """
        Analyze a single deduplicated error:
        1. Build MULTIPLE search queries from the error context
        2. Retrieve relevant code from the vector DB using each query
        3. Send error + code context to LLM for root cause analysis
        """
        error = dedup_error.error
        
        # Step 1: Build multiple search queries for better code retrieval
        search_queries = self._build_search_queries(error)
        if fast_mode:
            # Keep only the most specific queries to reduce vector calls
            search_queries = search_queries[:2]
        
        # Step 2: Retrieve code context from the appropriate vector DB(s)
        code_context, files_referenced = self._retrieve_code_context_multi(
            error,
            search_queries,
            top_k,
            fast_mode=fast_mode
        )
        
        # Step 3: Generate LLM root cause analysis
        root_cause = self._generate_analysis(dedup_error, code_context)
        
        return AnalyzedError(
            error=dedup_error,
            code_context=code_context,
            root_cause_analysis=root_cause,
            code_files_referenced=files_referenced
        )
    
    def _build_search_queries(self, error: ParsedError) -> List[str]:
        """
        Build MULTIPLE targeted search queries for the vector DB.
        
        Strategy: create separate queries focused on different aspects of the error
        to maximize the chance of retrieving the exact code that caused it.
        """
        queries = []
        
        # Query 1: The app-level file name + function name (most specific)
        if error.origin_file:
            short_file = error.origin_file.split('/')[-1]  # e.g., "AddAccountController.php"
            if error.triggering_function:
                func_clean = error.triggering_function.replace('->', ' ').replace('::', ' ').replace('()', '')
                queries.append(f"{short_file} {func_clean}")
            else:
                queries.append(short_file)
        
        # Query 2: Extract the key/variable name from the error message
        # Handles both full quotes: "scheme_code" AND truncated: "scheme_code
        key_match = re.search(r'["\']([\w]+)["\']?', error.error_message)
        if key_match and error.origin_file:
            short_file = error.origin_file.split('/')[-1].replace('.php', '').replace('.blade', '')
            key_name = key_match.group(1)
            # Only add if key_name is not a generic word
            if len(key_name) > 2 and key_name.lower() not in ('the', 'and', 'for', 'not'):
                queries.append(f"{key_name} {short_file}")
        
        # Query 3: Extract variable name from "Undefined variable $xyz" errors
        var_match = re.search(r'\$(\w+)', error.error_message)
        if var_match and error.origin_file:
            short_file = error.origin_file.split('/')[-1].replace('.php', '')
            queries.append(f"${var_match.group(1)} {short_file}")
        
        # Query 4: If there are app stack frames, build a query from the first non-middleware frame
        if error.app_stack_frames:
            # The first frame is typically the most relevant
            frame = error.app_stack_frames[0]
            # Extract just the file part: "app/Http/Controllers/Bank/AddAccountController.php:811"
            frame_file_match = re.match(r'app/(.+?):\d+', frame)
            if frame_file_match:
                frame_file = frame_file_match.group(1).split('/')[-1].replace('.php', '')
                if error.triggering_function:
                    func_name = error.triggering_function.split('->')[-1].split('::')[-1].replace('()', '')
                    queries.append(f"{frame_file} {func_name} function")
        
        # Query 5: For view errors, search for the blade template
        if error.view_file:
            view_short = error.view_file.split('/')[-1]
            queries.append(view_short)
        
        # Deduplicate while preserving order
        seen = set()
        unique_queries = []
        for q in queries:
            q_normalized = q.strip()
            if q_normalized and q_normalized not in seen:
                seen.add(q_normalized)
                unique_queries.append(q_normalized)
        
        # Always have at least one query
        if not unique_queries:
            unique_queries.append(error.error_message[:100])
        
        logger.info(f"Built {len(unique_queries)} search queries for error: {error.error_message[:60]}...")
        for i, q in enumerate(unique_queries):
            logger.info(f"  Query {i+1}: {q}")
        
        return unique_queries
    
    def _retrieve_code_context_multi(
        self, 
        error: ParsedError, 
        search_queries: List[str],
        top_k: int = 5,
        fast_mode: bool = False
    ) -> Tuple[str, List[str]]:
        """
        Retrieve relevant code from the vector DB using MULTIPLE search queries.
        Deduplicates results across queries to avoid sending duplicate chunks to the LLM.
        """
        all_results = []
        files_referenced = []
        seen_content_hashes = set()
        
        # Determine which engine(s) to use
        is_blade = error.view_file or (error.origin_file and '.blade.php' in (error.origin_file or ''))
        
        for query in search_queries:
            try:
                # Query Blade engine for template-related errors
                if is_blade and self.blade_engine:
                    try:
                        blade_results = self.blade_engine.query(
                            query_text=query,
                            top_k=min(top_k, 3),
                            initial_candidates=10,
                            max_snippet_chars=2000,
                            use_rerank=False
                        )
                        for r in blade_results:
                            content = r.get('snippet', '')
                            content_hash = hashlib.md5(content[:200].encode()).hexdigest()
                            if content_hash not in seen_content_hashes:
                                seen_content_hashes.add(content_hash)
                                all_results.append({
                                    'content': content,
                                    'metadata': {
                                        'file_path': r.get('file_path', 'unknown'),
                                        'file_name': r.get('file_name', 'unknown')
                                    },
                                    'query_used': query
                                })
                                fp = r.get('file_path', 'unknown')
                                if fp not in files_referenced:
                                    files_referenced.append(fp)
                    except Exception as e:
                        logger.warning(f"Blade query failed for '{query}': {e}")
                
                # Always query PHP engine
                if self.php_engine:
                    try:
                        php_results = self.php_engine.query(query, top_k=min(top_k, 3))
                        for r in php_results:
                            content = r.get('content', '')
                            content_hash = hashlib.md5(content[:200].encode()).hexdigest()
                            if content_hash not in seen_content_hashes:
                                seen_content_hashes.add(content_hash)
                                all_results.append({
                                    'content': content,
                                    'metadata': r.get('metadata', {}),
                                    'query_used': query
                                })
                                fp = r.get('metadata', {}).get('file_path', 'unknown')
                                if fp not in files_referenced:
                                    files_referenced.append(fp)
                    except Exception as e:
                        logger.warning(f"PHP query failed for '{query}': {e}")
                        
            except Exception as e:
                logger.error(f"Error retrieving code context for query '{query}': {e}")
        
        # Format context string — include query attribution for the LLM
        if not all_results:
            return "No relevant code context found in the indexed codebase.", files_referenced
        
        # Take fewer results in fast mode
        max_results = 4 if fast_mode else 8
        top_results = all_results[:max_results]
        
        context_parts = []
        for r in top_results:
            file_info = r.get('metadata', {}).get('file_path') or r.get('metadata', {}).get('file_name') or 'Unknown'
            short_file = file_info.split('/')[-1] if '/' in file_info else file_info
            content_cap = 1500 if fast_mode else 3000
            content = r.get('content', '')[:content_cap]  # Cap per chunk
            context_parts.append(f"[File: {short_file} | Full path: {file_info}]\n{content}")
        
        return "\n\n---\n\n".join(context_parts), files_referenced
    
    def _generate_analysis(
        self, 
        dedup_error: DeduplicatedError,
        code_context: str
    ) -> str:
        """Generate LLM root cause analysis for an error."""
        if not self.llm_service:
            return "⚠️ LLM service not available. Cannot generate root cause analysis."
        
        error = dedup_error.error
        
        # Build the user query with full error details
        user_query = self._format_error_for_llm(dedup_error)
        
        try:
            response, _, _ = self.llm_service.generate_response(
                system_prompt=self.ERROR_ANALYSIS_PROMPT,
                user_query=user_query,
                context=code_context,
                model="llama-3.1-8b-instant"
            )
            return response
        except Exception as e:
            logger.error(f"LLM analysis failed for error {dedup_error.fingerprint}: {e}")
            return f"⚠️ Analysis failed: {str(e)}"
    
    def _format_error_for_llm(self, dedup_error: DeduplicatedError) -> str:
        """Format the error details as a structured query for the LLM."""
        error = dedup_error.error
        
        # Build a rich context for the LLM
        parts = [
            "=== ERROR FROM PRODUCTION LOGS ===",
            f"ERROR MESSAGE: {error.error_message}",
            f"EXCEPTION TYPE: {error.exception_class or 'Unknown'}",
        ]
        
        # Show app-level origin (the real source)
        if error.origin_file:
            short_file = error.origin_file.split('/')[-1]
            parts.append(f"APP FILE: {short_file} (full: {error.origin_file})")
            parts.append(f"LINE: {error.origin_line or 'Unknown'}")
        
        if error.triggering_function:
            parts.append(f"FUNCTION: {error.triggering_function}")
        
        parts.extend([
            f"OCCURRENCES: {dedup_error.occurrence_count} times between {dedup_error.first_seen} and {dedup_error.last_seen}",
        ])
        
        if error.view_file:
            view_short = error.view_file.split('/')[-1]
            parts.append(f"VIEW FILE: {view_short} (full: {error.view_file})")
        
        if error.app_stack_frames:
            parts.append("APP-LEVEL STACK TRACE (vendor frames filtered out):")
            for f in error.app_stack_frames:
                parts.append(f"  → {f}")
        
        parts.append("")
        parts.append("=== YOUR TASK ===")
        parts.append("Using the source code context provided above, explain the root cause of this error and provide a concrete fix.")
        
        return "\n".join(parts)
    
    # ---- Serialization helpers ----
    
    def _deduped_to_dict(self, d: DeduplicatedError) -> Dict[str, Any]:
        """Convert DeduplicatedError to a JSON-serializable dict."""
        error = d.error
        return {
            "fingerprint": d.fingerprint,
            "error_message": error.error_message,
            "exception_class": error.exception_class,
            "origin_file": error.origin_file,
            "origin_line": error.origin_line,
            "triggering_function": error.triggering_function,
            "view_file": error.view_file,
            "occurrence_count": d.occurrence_count,
            "first_seen": d.first_seen,
            "last_seen": d.last_seen,
            "app_stack_frames": error.app_stack_frames,
            "severity_hint": self._severity_hint(error, d.occurrence_count)
        }
    
    def _analysis_to_dict(self, a: AnalyzedError) -> Dict[str, Any]:
        """Convert AnalyzedError to a JSON-serializable dict."""
        base = self._deduped_to_dict(a.error)
        base.update({
            "root_cause_analysis": a.root_cause_analysis,
            "code_files_referenced": a.code_files_referenced,
            "code_context_preview": a.code_context[:500] + "..." if len(a.code_context) > 500 else a.code_context
        })
        return base
    
    def _severity_hint(self, error: ParsedError, count: int) -> str:
        """Provide a quick severity hint based on error type and frequency."""
        if error.exception_class in ('ParseError', 'SyntaxError'):
            return "CRITICAL"  # Syntax errors break entire controller loading
        if count >= 5:
            return "HIGH"      # Frequent errors indicate systemic issues
        if 'Undefined variable' in error.error_message:
            return "MEDIUM"
        if 'FileNotFoundException' in (error.exception_class or ''):
            return "LOW"       # Missing assets are lower priority
        return "MEDIUM"
