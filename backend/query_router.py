"""
Centralized Query Router with LLM-based Intent Classification
Uses function calling for structured routing decisions and RRF for result fusion
Supports Hybrid Search (Dense + BM25 Sparse) for improved retrieval accuracy
"""

import logging
import asyncio
import time
from typing import List, Dict, Any, Optional, Tuple
from enum import Enum
import json
import sys
import os
from pathlib import Path
from groq import Groq

# Add parent directory to path for imports
sys.path.append(str(Path(__file__).parent.parent))

from utils.groq_rate_limiter import GroqRateLimiter
from utils.bm25_index import BM25IndexManager
from utils.hybrid_search import HybridSearchManager, HybridSearchConfig, SearchMethod, QueryIntent, QueryExpansionInfo

# Security imports
from security.security_config import (
    SECURITY_PREAMBLE, 
    get_hardened_system_prompt,
    get_banking_security_addendum
)
from security.query_guardrails import QueryGuardrails, check_query_safety
from security.output_filter import OutputFilter, filter_llm_response, redact_sensitive

logger = logging.getLogger(__name__)


class KnowledgeSource(str, Enum):
    """Available knowledge sources/vector databases"""
    BUSINESS_DOCS = "business_docs"
    PHP_CODE = "php_code"
    JS_CODE = "js_code"
    BLADE_TEMPLATES = "blade_templates"


class QueryType(str, Enum):
    """Types of queries for better routing"""
    DOCUMENTATION = "documentation"
    IMPLEMENTATION = "implementation"
    DEBUGGING = "debugging"
    ARCHITECTURE = "architecture"
    MIXED = "mixed"


class IntentClassificationResult:
    """Structured result from intent classification"""
    def __init__(self, 
                 primary_source: KnowledgeSource,
                 secondary_sources: List[KnowledgeSource],
                 confidence: float,
                 reasoning: str,
                 query_type: QueryType,
                 requires_code: bool):
        self.primary_source = primary_source
        self.secondary_sources = secondary_sources
        self.confidence = confidence
        self.reasoning = reasoning
        self.query_type = query_type
        self.requires_code = requires_code
        
    def get_all_sources(self) -> List[KnowledgeSource]:
        """Get all sources to query (primary + secondary)"""
        sources = [self.primary_source]
        sources.extend(self.secondary_sources)
        return list(set(sources))  # Remove duplicates
    
    def to_dict(self) -> Dict:
        return {
            "primary_source": self.primary_source,
            "secondary_sources": self.secondary_sources,
            "confidence": self.confidence,
            "reasoning": self.reasoning,
            "query_type": self.query_type,
            "requires_code": self.requires_code
        }


class IntentClassifier:
    """
    LLM-based intent classifier using Groq function calling
    Determines which vector DB(s) to query based on user intent
    """
    
    def __init__(self, groq_api_key: str, model: str = "llama-3.1-8b-instant", rate_limiter: Optional[GroqRateLimiter] = None):
        """
        Initialize with cheaper/faster model for classification
        
        Args:
            groq_api_key: Groq API key
            model: Model to use (8b-instant for speed and cost efficiency)
            rate_limiter: Optional rate limiter instance
        """
        self.client = Groq(api_key=groq_api_key)
        self.model = model
        self.rate_limiter = rate_limiter or GroqRateLimiter(
            max_retries=3,
            base_delay=1.5,
            daily_token_limit=100000,
            enable_cache=True,
            cache_ttl=3600  # 1 hour cache for intent classification
        )
        self.model = model
        
        # Define the function schema for routing
        self.routing_function = {
            "name": "route_query",
            "description": "Route a user query to the appropriate knowledge sources in a banking application",
            "parameters": {
                "type": "object",
                "properties": {
                    "primary_source": {
                        "type": "string",
                        "enum": ["business_docs", "php_code", "js_code", "blade_templates"],
                        "description": "The main knowledge source to query. business_docs: Banking domain documentation, processes, workflows. php_code: Laravel PHP backend controllers, models, services. js_code: Frontend JavaScript/React components. blade_templates: Laravel Blade view templates and forms."
                    },
                    "secondary_sources": {
                        "type": "array",
                        "items": {
                            "type": "string",
                            "enum": ["business_docs", "php_code", "js_code", "blade_templates"]
                        },
                        "description": "Additional knowledge sources if query spans multiple domains. Empty array if single source sufficient."
                    },
                    "confidence": {
                        "type": "number",
                        "minimum": 0,
                        "maximum": 1,
                        "description": "Confidence score for routing decision (0.0 to 1.0)"
                    },
                    "reasoning": {
                        "type": "string",
                        "description": "Brief explanation of why these sources were selected"
                    },
                    "query_type": {
                        "type": "string",
                        "enum": ["documentation", "implementation", "debugging", "architecture", "mixed"],
                        "description": "Type of query: documentation (conceptual), implementation (how it's built), debugging (fixing issues), architecture (system design), mixed (combination)"
                    },
                    "requires_code": {
                        "type": "boolean",
                        "description": "Whether the answer should include code examples"
                    }
                },
                "required": ["primary_source", "secondary_sources", "confidence", "reasoning", "query_type", "requires_code"]
            }
        }
    
    def classify(self, query: str) -> IntentClassificationResult:
        """
        Classify user query using function calling
        
        Args:
            query: User's natural language query
            
        Returns:
            IntentClassificationResult with routing decision
        """
        try:
            system_prompt = """You are an expert routing system for a banking application knowledge base.

Available Knowledge Sources:
1. business_docs: Banking domain documentation (loan processes, account types, business rules, workflows, policies, KYC requirements, risk assessment, compliance)
2. php_code: Laravel PHP backend code (controllers, models, services, API endpoints, business logic, helpers, commands, database operations)
3. js_code: Frontend JavaScript/React code (components, state management, UI logic, API calls, form validation, client-side processing)
4. blade_templates: Laravel Blade templates (HTML views, forms, layouts, frontend rendering, form inputs, user interface)

Routing Guidelines with Examples:

SINGLE SOURCE queries (DO NOT add business_docs unless explicitly about business concepts):
- "What is a term deposit?" → business_docs ONLY (pure business concept)
- "Explain loan approval process" → business_docs ONLY (business workflow)
- "What are KYC requirements?" → business_docs ONLY (regulatory/policy)
- "Show me UserController code" → php_code ONLY (specific code file)
- "How does AuthService work?" → php_code ONLY (backend logic)
- "What does checkKYC command do?" → php_code ONLY (backend command)
- "React component for dashboard" → js_code ONLY (frontend component)
- "JavaScript validation logic" → js_code ONLY (client-side code)
- "login.blade.php structure" → blade_templates ONLY (specific template)
- "Form fields in account opening" → blade_templates ONLY (form structure)
- "How is data validated in UserController?" → php_code ONLY (code-specific)
- "Debug DSA create account function" → php_code ONLY (implementation debugging)

MULTI-SOURCE queries (ONLY when query explicitly needs multiple domains):
- "How does account opening form work end to end?" → blade_templates (primary: form UI) + php_code (backend processing) + business_docs (business rules)
- "Complete KYC verification from UI to backend with business rules" → blade_templates + js_code + php_code + business_docs
- "What are loan approval rules and how are they implemented?" → business_docs (primary: rules) + php_code (implementation)
- "Full authentication flow with security policies" → php_code (primary: auth logic) + blade_templates (login form) + business_docs (security policy)

CRITICAL: business_docs should ONLY be included when:
1. Query explicitly asks about "business rules", "policies", "requirements", "process", "workflow"
2. Query asks "what is" (conceptual question)
3. Query needs context about WHY something exists
4. Query asks about "complete flow" or "end to end" spanning business and technical

DO NOT include business_docs when:
1. Query is about code implementation details
2. Query asks "how does [code/function] work"
3. Query is debugging or technical troubleshooting
4. Query mentions specific files, classes, or functions
5. Query is about UI/form structure without business context

Key Decision Rules:
1. If query asks "What is..." or "Explain concept" → business_docs
2. If query mentions specific file/class/function → php_code or js_code or blade_templates
3. If query asks "How does [feature] work" → likely multi-source (UI + backend + business)
4. If query mentions "form" → blade_templates + php_code
5. If query mentions "validation" → js_code (client) + php_code (server)
6. If query asks "complete flow" or "end to end" → multi-source (all relevant)
7. If query mentions "business rules implemented" → business_docs + php_code

Confidence Guidelines:
- High confidence (0.8-1.0): Query clearly matches one domain or explicitly multi-domain
- Medium confidence (0.5-0.8): Query could span multiple domains
- Low confidence (0.3-0.5): Ambiguous query, default to primary source only

Be precise and confident in your routing decisions. Secondary sources should only be included if they add significant value."""

            @self.rate_limiter.with_retry
            def _make_classification(client, model, messages, tools, tool_choice, temperature, max_tokens):
                return client.chat.completions.create(
                    model=model,
                    messages=messages,
                    tools=tools,
                    tool_choice=tool_choice,
                    temperature=temperature,
                    max_tokens=max_tokens
                )

            # Try function calling first
            try:
                response = _make_classification(
                    client=self.client,
                    model=self.model,
                    messages=[
                        {"role": "system", "content": system_prompt},
                        {"role": "user", "content": f"Route this query: {query}"}
                    ],
                    tools=[{"type": "function", "function": self.routing_function}],
                    tool_choice={"type": "function", "function": {"name": "route_query"}},
                    temperature=0.1,  # Low temperature for consistent routing
                    max_tokens=200
                )
                
                # Extract function call result
                tool_call = response.choices[0].message.tool_calls[0]
                routing_args = json.loads(tool_call.function.arguments)
            except Exception as func_error:
                # Function calling failed, try JSON mode fallback
                logger.warning(f"Function calling failed, trying JSON fallback: {func_error}")
                routing_args = self._classify_with_json_fallback(query, system_prompt)
            
            # Parse and validate
            primary_source = KnowledgeSource(routing_args["primary_source"])
            secondary_sources = [KnowledgeSource(s) for s in routing_args.get("secondary_sources", [])]
            # Remove primary from secondary if present
            secondary_sources = [s for s in secondary_sources if s != primary_source]
            
            result = IntentClassificationResult(
                primary_source=primary_source,
                secondary_sources=secondary_sources,
                confidence=routing_args.get("confidence", 0.7),
                reasoning=routing_args.get("reasoning", "Routed based on query content"),
                query_type=QueryType(routing_args.get("query_type", "mixed")),
                requires_code=routing_args.get("requires_code", False)
            )
            
            logger.info(f"Intent Classification: {result.to_dict()}")
            return result
            
        except Exception as e:
            logger.error(f"Intent classification failed: {e}")
            # Fallback to business docs (safest default)
            return IntentClassificationResult(
                primary_source=KnowledgeSource.BUSINESS_DOCS,
                secondary_sources=[],
                confidence=0.3,
                reasoning=f"Fallback due to classification error: {str(e)}",
                query_type=QueryType.MIXED,
                requires_code=False
            )
    
    def _classify_with_json_fallback(self, query: str, system_prompt: str) -> dict:
        """
        Fallback classification using direct JSON output when function calling fails.
        This is more reliable with smaller models that sometimes fail tool use.
        """
        json_prompt = f"""{system_prompt}

IMPORTANT: You must respond with ONLY a valid JSON object, no other text.
The JSON must have this exact structure:
{{
    "primary_source": "one of: business_docs, php_code, js_code, blade_templates",
    "secondary_sources": ["array of additional sources or empty array"],
    "confidence": 0.8,
    "reasoning": "brief explanation",
    "query_type": "one of: documentation, implementation, debugging, architecture, mixed",
    "requires_code": true or false
}}

Route this query: {query}

Respond with ONLY the JSON object:"""

        response = self.client.chat.completions.create(
            model=self.model,
            messages=[{"role": "user", "content": json_prompt}],
            temperature=0.1,
            max_tokens=300
        )
        
        response_text = response.choices[0].message.content.strip()
        
        # Try to extract JSON from the response
        # Handle cases where model adds markdown code blocks
        if "```json" in response_text:
            response_text = response_text.split("```json")[1].split("```")[0].strip()
        elif "```" in response_text:
            response_text = response_text.split("```")[1].split("```")[0].strip()
        
        # Try to parse the JSON
        try:
            routing_args = json.loads(response_text)
        except json.JSONDecodeError:
            # Try to find JSON object in the response
            import re
            json_match = re.search(r'\{[^{}]*\}', response_text, re.DOTALL)
            if json_match:
                routing_args = json.loads(json_match.group())
            else:
                # Last resort: keyword-based routing
                logger.warning("JSON parsing failed, using keyword-based routing")
                routing_args = self._keyword_based_routing(query)
        
        # Validate and ensure required fields
        valid_sources = ["business_docs", "php_code", "js_code", "blade_templates"]
        if routing_args.get("primary_source") not in valid_sources:
            routing_args["primary_source"] = "business_docs"
        
        routing_args["secondary_sources"] = [
            s for s in routing_args.get("secondary_sources", []) 
            if s in valid_sources and s != routing_args["primary_source"]
        ]
        
        return routing_args
    
    def _keyword_based_routing(self, query: str) -> dict:
        """
        Simple keyword-based routing as last fallback.
        """
        query_lower = query.lower()
        
        # PHP/Backend keywords
        php_keywords = ["controller", "model", "service", "php", "laravel", "backend", 
                       "api", "endpoint", "database", "query", "eloquent", "migration",
                       "artisan", "command", "helper", "trait", "middleware"]
        
        # JS/Frontend keywords
        js_keywords = ["javascript", "react", "component", "frontend", "state", 
                      "hook", "redux", "axios", "fetch", "client", "browser", "dom"]
        
        # Blade keywords
        blade_keywords = ["blade", "view", "template", "form", "html", "input", 
                        "layout", "partial", ".blade.php", "ui", "interface"]
        
        # Business keywords
        business_keywords = ["process", "workflow", "policy", "rule", "requirement",
                           "kyc", "loan", "account type", "compliance", "regulation",
                           "what is", "explain", "documentation", "business"]
        
        # Count matches
        php_score = sum(1 for kw in php_keywords if kw in query_lower)
        js_score = sum(1 for kw in js_keywords if kw in query_lower)
        blade_score = sum(1 for kw in blade_keywords if kw in query_lower)
        business_score = sum(1 for kw in business_keywords if kw in query_lower)
        
        scores = {
            "php_code": php_score,
            "js_code": js_score,
            "blade_templates": blade_score,
            "business_docs": business_score
        }
        
        primary = max(scores, key=scores.get)
        if scores[primary] == 0:
            primary = "business_docs"  # Default
        
        return {
            "primary_source": primary,
            "secondary_sources": [],
            "confidence": 0.5,
            "reasoning": "Keyword-based fallback routing",
            "query_type": "mixed",
            "requires_code": primary in ["php_code", "js_code", "blade_templates"]
        }


class ResultFusion:
    """
    Fuses results from multiple vector databases using Reciprocal Rank Fusion (RRF)
    with optional cross-encoder reranking for higher accuracy
    """
    
    def __init__(self, k: int = 60, use_cross_encoder: bool = True):
        """
        Initialize RRF with constant k
        
        Args:
            k: RRF constant (typically 60, larger = less emphasis on rank)
            use_cross_encoder: Whether to use cross-encoder for final reranking
        """
        self.k = k
        self.use_cross_encoder = use_cross_encoder
        self.cross_encoder = None
        
        # Load cross-encoder for reranking if enabled
        if self.use_cross_encoder:
            try:
                from sentence_transformers import CrossEncoder
                self.cross_encoder = CrossEncoder('cross-encoder/ms-marco-MiniLM-L-6-v2')
                logger.info("Cross-encoder loaded for result reranking")
            except Exception as e:
                logger.warning(f"Failed to load cross-encoder: {e}. Continuing without reranking.")
                self.use_cross_encoder = False
    
    def reciprocal_rank_fusion(self, 
                               results_by_source: Dict[str, List[Dict]],
                               top_k: int = 5,
                               source_quality_threshold: float = 0.35) -> List[Dict]:
        """
        Apply Reciprocal Rank Fusion to merge results from multiple sources
        with quality filtering per source
        
        Formula: RRF_score = sum(1 / (k + rank_i)) for each source
        
        Args:
            results_by_source: Dict mapping source name to list of results
            top_k: Number of final results to return
            source_quality_threshold: Minimum distance quality per source (filters weak sources)
            
        Returns:
            Merged and ranked list of results with RRF scores
        """
        # Build RRF scores for all unique results
        rrf_scores = {}  # Map unique_id -> RRF score
        result_data = {}  # Map unique_id -> result dict
        source_stats = {}  # Track source quality
        
        for source_name, results in results_by_source.items():
            if len(results) == 0:
                continue
            
            # Check source quality - if first result has bad distance, source might be irrelevant
            first_distance = results[0].get('distance', 0)
            source_stats[source_name] = {
                'count': len(results),
                'best_distance': first_distance
            }
            
            # If source has consistently bad distances, it might be irrelevant
            if first_distance > source_quality_threshold:
                logger.warning(f"Source {source_name} has poor relevance (best distance: {first_distance:.3f}), limiting contribution")
            
            for rank, result in enumerate(results, start=1):
                # Create unique ID combining source and result ID
                result_id = result.get('id', f"{source_name}_{rank}")
                unique_id = f"{source_name}::{result_id}"
                
                # Calculate RRF contribution from this source
                rrf_contribution = 1.0 / (self.k + rank)
                
                # Apply quality penalty if source seems irrelevant
                distance = result.get('distance', 0)
                if distance > source_quality_threshold:
                    rrf_contribution *= 0.5  # Reduce contribution from poor matches
                
                # Accumulate RRF score
                if unique_id not in rrf_scores:
                    rrf_scores[unique_id] = 0.0
                    result_data[unique_id] = {
                        **result,
                        'source': source_name,
                        'original_rank': rank,
                        'original_distance': result.get('distance', None)
                    }
                
                rrf_scores[unique_id] += rrf_contribution
        
        # Sort by RRF score (descending)
        ranked_ids = sorted(rrf_scores.keys(), key=lambda x: rrf_scores[x], reverse=True)
        
        # Build final result list
        merged_results = []
        for unique_id in ranked_ids[:top_k * 2]:  # Get more for potential filtering
            result = result_data[unique_id]
            result['rrf_score'] = rrf_scores[unique_id]
            merged_results.append(result)
        
        # Log source statistics
        for source, stats in source_stats.items():
            logger.info(f"Source {source}: {stats['count']} results, best distance: {stats['best_distance']:.3f}")
        
        logger.info(f"RRF merged {len(results_by_source)} sources into {len(merged_results)} results")
        return merged_results
    
    def rerank_with_cross_encoder(self, 
                                  query: str, 
                                  results: List[Dict], 
                                  top_k: int = 5,
                                  min_score: float = 1.0) -> List[Dict]:
        """
        Rerank results using cross-encoder with relevance filtering
        
        Args:
            query: Original user query
            results: Results to rerank
            top_k: Number of results to return after reranking
            min_score: Minimum cross-encoder score to include (filters irrelevant results)
            
        Returns:
            Reranked and filtered results with cross-encoder scores
        """
        if not self.use_cross_encoder or not self.cross_encoder or len(results) == 0:
            return results[:top_k]
        
        try:
            # Prepare query-document pairs
            pairs = [[query, result.get('content', '')] for result in results]
            
            # Get cross-encoder scores
            scores = self.cross_encoder.predict(pairs)
            
            # Add scores to results
            for result, score in zip(results, scores):
                result['cross_encoder_score'] = float(score)
            
            # Filter out low-relevance results (critical for removing irrelevant chunks)
            filtered = [r for r in results if r.get('cross_encoder_score', -999) >= min_score]
            
            if len(filtered) < top_k:
                logger.warning(f"Only {len(filtered)} results passed relevance threshold {min_score}, returning all")
                filtered = results  # Fall back to unfiltered if too aggressive
            
            # Sort by cross-encoder score (descending)
            reranked = sorted(filtered, key=lambda x: x.get('cross_encoder_score', -999), reverse=True)
            
            # Log filtering stats
            removed = len(results) - len(filtered)
            if removed > 0:
                logger.info(f"Cross-encoder filtered out {removed} irrelevant results (score < {min_score})")
            
            logger.info(f"Cross-encoder reranked {len(results)} → {len(reranked[:top_k])} results")
            return reranked[:top_k]
            
        except Exception as e:
            logger.error(f"Cross-encoder reranking failed: {e}")
            return results[:top_k]
    
    def deduplicate_results(self, results: List[Dict], similarity_threshold: float = 0.9) -> List[Dict]:
        """
        Remove near-duplicate results based on content similarity
        
        Args:
            results: List of results to deduplicate
            similarity_threshold: Jaccard similarity threshold for deduplication
            
        Returns:
            Deduplicated list of results
        """
        if len(results) <= 1:
            return results
        
        deduplicated = []
        seen_contents = []
        
        for result in results:
            content = result.get('content', '')
            # Simple word-based Jaccard similarity
            content_words = set(content.lower().split())
            
            is_duplicate = False
            for seen_content in seen_contents:
                seen_words = set(seen_content.lower().split())
                if len(content_words) == 0 or len(seen_words) == 0:
                    continue
                    
                intersection = len(content_words & seen_words)
                union = len(content_words | seen_words)
                jaccard = intersection / union if union > 0 else 0
                
                if jaccard >= similarity_threshold:
                    is_duplicate = True
                    break
            
            if not is_duplicate:
                deduplicated.append(result)
                seen_contents.append(content)
        
        logger.info(f"Deduplication: {len(results)} → {len(deduplicated)} results")
        return deduplicated


class QueryRouter:
    """
    Routes queries to appropriate vector database engines in parallel
    with cross-encoder reranking for improved accuracy.
    Supports Hybrid Search (Dense + BM25) for better exact term matching.
    """
    
    def __init__(self, 
                 business_engine,
                 php_engine,
                 js_engine,
                 blade_engine,
                 use_cross_encoder: bool = True,
                 use_hybrid_search: bool = True,
                 bm25_index_dir: str = None):
        """
        Initialize with all available query engines
        
        Args:
            business_engine: BusinessQueryEngine instance
            php_engine: CodeQueryEngine for PHP
            js_engine: CodeQueryEngine for JavaScript
            blade_engine: BladeDescriptionEngine instance
            use_cross_encoder: Whether to use cross-encoder reranking
            use_hybrid_search: Whether to use hybrid (dense + BM25) search
            bm25_index_dir: Directory containing BM25 indices
        """
        self.engines = {
            KnowledgeSource.BUSINESS_DOCS: business_engine,
            KnowledgeSource.PHP_CODE: php_engine,
            KnowledgeSource.JS_CODE: js_engine,
            KnowledgeSource.BLADE_TEMPLATES: blade_engine
        }
        self.result_fusion = ResultFusion(k=60, use_cross_encoder=use_cross_encoder)
        
        # Initialize Hybrid Search
        self.use_hybrid_search = use_hybrid_search
        self.hybrid_manager = None
        
        if use_hybrid_search:
            try:
                # Determine BM25 index directory
                if bm25_index_dir is None:
                    project_root = Path(__file__).parent.parent
                    bm25_index_dir = str(project_root / "bm25_indices")
                
                # Initialize BM25 manager and load indices
                bm25_manager = BM25IndexManager(index_dir=bm25_index_dir)
                load_results = bm25_manager.load_all_indices()
                
                # Configure hybrid search with weights favoring semantic for your use case
                hybrid_config = HybridSearchConfig(
                    dense_weight=0.6,   # Semantic understanding
                    sparse_weight=0.4,  # Exact term matching (function names, etc.)
                    rrf_k=60,
                    min_bm25_score=0.5
                )
                
                self.hybrid_manager = HybridSearchManager(bm25_manager, hybrid_config)
                
                # Log which indices loaded successfully
                loaded = [k for k, v in load_results.items() if v]
                if loaded:
                    logger.info(f"Hybrid search enabled with BM25 indices: {loaded}")
                else:
                    logger.warning("No BM25 indices found. Run build_bm25_indices.py first.")
                    self.use_hybrid_search = False
                    
            except Exception as e:
                logger.warning(f"Failed to initialize hybrid search: {e}. Falling back to dense only.")
                self.use_hybrid_search = False
    
    async def query_engine_async(self, 
                                 source: KnowledgeSource, 
                                 query: str, 
                                 top_k: int) -> Tuple[str, List[Dict]]:
        """
        Query a single engine asynchronously
        
        Args:
            source: Which knowledge source to query
            query: User query text
            top_k: Number of results to retrieve
            
        Returns:
            Tuple of (source_name, results)
        """
        engine = self.engines.get(source)
        if not engine:
            logger.warning(f"Engine for {source} not available")
            return (source.value, [])
        
        try:
            # Run in thread pool since engines are synchronous
            loop = asyncio.get_event_loop()
            
            # Blade engine has different signature
            if source == KnowledgeSource.BLADE_TEMPLATES:
                results = await loop.run_in_executor(
                    None,
                    lambda: engine.query(
                        query_text=query,
                        top_k=top_k,
                        initial_candidates=20,
                        max_snippet_chars=2000,
                        use_rerank=True
                    )
                )
                # Convert blade results to standard format
                results = [{
                    'id': r['id'],
                    'content': r['snippet'],
                    'metadata': {
                        'file_name': r['file_name'],
                        'file_path': r['file_path'],
                        'section': r['section'],
                        'description': r['description'],
                    },
                    'distance': r.get('distance')
                } for r in results]
            else:
                results = await loop.run_in_executor(
                    None,
                    lambda: engine.query(query, top_k)
                )
            
            logger.info(f"Retrieved {len(results)} results from {source}")
            return (source.value, results)
            
        except Exception as e:
            logger.error(f"Error querying {source}: {e}")
            return (source.value, [])
    
    async def query_parallel(self, 
                            sources: List[KnowledgeSource],
                            query: str,
                            top_k: int = 5,
                            query_intent: Optional[QueryIntent] = None,
                            inference_logger = None) -> Tuple[Dict[str, List[Dict]], Optional[QueryExpansionInfo]]:
        """
        Query multiple engines in parallel
        
        Args:
            sources: List of knowledge sources to query
            query: User query text
            top_k: Number of results per source
            query_intent: Optional intent info for controlling query expansion
            inference_logger: Optional logger for tracking expansion
            
        Returns:
            Tuple of (Dict mapping source name to results, expansion info)
        """
        # Create parallel query tasks
        tasks = [
            self.query_engine_async(source, query, top_k)
            for source in sources
        ]
        
        # Execute in parallel
        results = await asyncio.gather(*tasks)
        
        # Convert to dict
        results_by_source = {source_name: source_results 
                            for source_name, source_results in results}
        
        # Apply hybrid search (combine with BM25) if enabled
        expansion_info = None
        if self.use_hybrid_search and self.hybrid_manager:
            results_by_source, expansion_info = self.hybrid_manager.search_multi_source(
                results_by_source=results_by_source,
                query=query,
                top_k=top_k,
                query_intent=query_intent  # Pass intent for smart expansion
            )
            logger.info(f"Hybrid search applied to {len(results_by_source)} sources")
            
            # Log expansion info if logger provided
            if inference_logger and expansion_info:
                inference_logger.log_query_expansion(
                    original_query=expansion_info.original_query,
                    expanded_query=expansion_info.expanded_query,
                    was_expanded=expansion_info.was_expanded,
                    query_type=expansion_info.query_type,
                    requires_code=expansion_info.requires_code
                )
        
        return results_by_source, expansion_info
    
    async def query_multi_source(self,
                          sources: List[KnowledgeSource],
                          query: str,
                          top_k: int = 5,
                          final_top_k: Optional[int] = None,
                          query_intent: Optional[QueryIntent] = None) -> List[Dict]:
        """
        Query multiple sources and merge results using RRF
        
        Args:
            sources: List of knowledge sources to query
            query: User query text
            top_k: Number of results to get from each source
            final_top_k: Final number of merged results (defaults to top_k)
            query_intent: Optional intent info for controlling query expansion
            
        Returns:
            Merged and ranked results using RRF
        """
        if final_top_k is None:
            final_top_k = top_k
        
        # Run parallel queries - get more candidates for reranking
        retrieve_k = top_k * 2  # Get 2x candidates for better reranking
        results_by_source, _ = await self.query_parallel(sources, query, retrieve_k, query_intent=query_intent)
        
        # Apply RRF to merge results with quality filtering
        merged_results = self.result_fusion.reciprocal_rank_fusion(
            results_by_source,
            top_k=final_top_k * 3,  # Keep more for cross-encoder filtering
            source_quality_threshold=0.35  # Filter out poor matches
        )
        
        # Apply cross-encoder reranking with aggressive relevance filtering
        reranked_results = self.result_fusion.rerank_with_cross_encoder(
            query=query,
            results=merged_results,
            top_k=final_top_k,
            min_score=2.0  # Aggressive threshold to remove irrelevant chunks
        )
        
        return reranked_results
    
    async def query_multi_source_with_filtering(self,
                          sources: List[KnowledgeSource],
                          query: str,
                          top_k: int = 5,
                          final_top_k: Optional[int] = None,
                          min_relevance_score: float = 2.0,
                          inference_logger = None,
                          query_intent: Optional[QueryIntent] = None) -> List[Dict]:
        """
        Query multiple sources with configurable relevance filtering
        
        Args:
            sources: List of knowledge sources to query
            query: User query text
            top_k: Number of results to get from each source
            final_top_k: Final number of merged results (defaults to top_k)
            min_relevance_score: Minimum cross-encoder score to include
            inference_logger: Optional logger for tracking
            query_intent: Optional intent info for controlling query expansion
            
        Returns:
            Merged, filtered and ranked results
        """
        if final_top_k is None:
            final_top_k = top_k
        
        # Run parallel queries - get more candidates for reranking
        retrieve_k = top_k * 2
        retrieval_start = time.time()
        results_by_source, _ = await self.query_parallel(
            sources, query, retrieve_k, 
            query_intent=query_intent,
            inference_logger=inference_logger  # Pass logger for expansion tracking
        )
        retrieval_time_ms = (time.time() - retrieval_start) * 1000
        
        # Log initial retrieval for each source
        if inference_logger:
            for source_name, results in results_by_source.items():
                inference_logger.log_initial_retrieval(source_name, results, retrieval_time_ms / len(results_by_source))
        
        # Pre-filter results: Remove obviously irrelevant results BEFORE RRF
        # This prevents low-quality results from polluting the fusion
        filtered_results = {}
        distance_threshold = 0.5  # More aggressive for secondary sources
        
        for source_name, results in results_by_source.items():
            if len(results) == 0:
                filtered_results[source_name] = []
                continue
            
            # Filter based on distance - keep only reasonable matches
            good_results = [r for r in results if r.get('distance', 999) <= distance_threshold]
            
            # If we filtered out too many, keep at least top 3 from each source
            if len(good_results) < 3 and len(results) >= 3:
                good_results = results[:3]
            elif len(good_results) == 0 and len(results) > 0:
                good_results = results[:1]  # Keep at least one result
            
            filtered_results[source_name] = good_results
            
            if len(good_results) < len(results):
                removed = len(results) - len(good_results)
                logger.info(f"Pre-filtered {removed} poor results from {source_name} (distance > {distance_threshold})")
        
        # Apply RRF to merge results with quality filtering
        merged_results = self.result_fusion.reciprocal_rank_fusion(
            filtered_results,  # Use pre-filtered results
            top_k=final_top_k * 3,
            source_quality_threshold=0.35
        )
        
        # Log RRF fusion
        if inference_logger:
            inference_logger.log_rrf_fusion(merged_results)
            # Also log before reranking snapshot
            inference_logger.log_before_reranking(merged_results)
        
        # Apply cross-encoder reranking with CONFIGURABLE relevance filtering
        rerank_start = time.time()
        reranked_results = self.result_fusion.rerank_with_cross_encoder(
            query=query,
            results=merged_results,
            top_k=final_top_k,
            min_score=min_relevance_score  # Use user-provided threshold
        )
        rerank_time_ms = (time.time() - rerank_start) * 1000
        
        # Log reranking
        if inference_logger:
            inference_logger.log_reranking(reranked_results, rerank_time_ms)
        
        return reranked_results
    
    async def query_single_source(self,
                           source: KnowledgeSource,
                           query: str,
                           top_k: int = 5,
                           inference_logger = None,
                           query_intent: Optional[QueryIntent] = None) -> List[Dict]:
        """
        Query a single source directly (no RRF needed)
        
        Args:
            source: Knowledge source to query
            query: User query text
            top_k: Number of results
            inference_logger: Optional logger for tracking
            query_intent: Optional intent info for controlling query expansion
            
        Returns:
            Results from the source
        """
        retrieval_start = time.time()
        results_by_source, _ = await self.query_parallel(
            [source], query, top_k, 
            query_intent=query_intent,
            inference_logger=inference_logger  # Pass logger for expansion tracking
        )
        retrieval_time_ms = (time.time() - retrieval_start) * 1000
        
        results = results_by_source.get(source.value, [])
        
        # Log retrieval
        if inference_logger:
            inference_logger.log_initial_retrieval(source.value, results, retrieval_time_ms)
            # For single source, also log as final results
            inference_logger.log_reranking(results, 0)
        
        return results


class UnifiedQueryEngine:
    """
    Main orchestrator that combines intent classification, routing, and response generation
    with query preprocessing for improved accuracy
    """
    
    def __init__(self,
                 intent_classifier: IntentClassifier,
                 query_router: QueryRouter,
                 llm_service):
        """
        Initialize unified query engine
        
        Args:
            intent_classifier: IntentClassifier instance
            query_router: QueryRouter instance
            llm_service: LLM service for response generation
        """
        self.intent_classifier = intent_classifier
        self.query_router = query_router
        self.llm_service = llm_service
    
    def preprocess_query(self, query: str) -> str:
        """
        Preprocess query for better retrieval.
        
        Note: Banking abbreviation expansion is now handled by intent-aware
        BM25 search (see utils/bm25_index.py). This method only handles
        basic normalization to avoid double expansion.
        
        Args:
            query: Raw user query
            
        Returns:
            Preprocessed query (normalized whitespace only)
        """
        # Remove extra whitespace - this is safe for all query types
        query = ' '.join(query.split())
        
        # NOTE: Abbreviation expansion has been moved to intent-aware BM25 search.
        # This ensures code queries like "validateKYC" are not expanded,
        # while conceptual queries like "what is kyc" get proper expansion.
        # See: utils/bm25_index.py - expand_query_abbreviations()
        
        return query
    
    async def smart_query(self,
                   query: str,
                   top_k: int = 5,
                   confidence_threshold: float = 0.5,
                   min_relevance_score: float = 2.0,
                   inference_logger = None) -> Dict[str, Any]:
        """
        Execute smart routing and retrieval with preprocessing
        
        Args:
            query: User query text
            top_k: Number of results to return
            confidence_threshold: Minimum confidence to include secondary sources
            min_relevance_score: Minimum cross-encoder score for relevance filtering
            inference_logger: Optional InferenceLogger instance for detailed logging
            
        Returns:
            Dict containing:
                - routing_decision: Intent classification details
                - results: Retrieved and ranked results
                - context: Formatted context for LLM
                - llm_response: Generated response
        """
        # Step 0: Preprocess query for better retrieval
        processed_query = self.preprocess_query(query)
        logger.info(f"Original query: {query}")
        if processed_query != query:
            logger.info(f"Preprocessed query: {processed_query}")
        
        # Log preprocessing
        if inference_logger:
            inference_logger.log_preprocessing(processed_query)
        
        # Step 1: Classify intent (use original query for better understanding)
        routing_start = time.time()
        intent = self.intent_classifier.classify(query)
        routing_time_ms = (time.time() - routing_start) * 1000
        
        # Step 2: Determine sources to query
        sources_to_query = [intent.primary_source]
        
        # Only add secondary sources if confidence is high enough
        # Be EXTRA strict about adding business_docs as secondary source
        if intent.confidence >= confidence_threshold:
            for secondary in intent.secondary_sources:
                # Apply higher threshold for business_docs to reduce irrelevant chunks
                if secondary == KnowledgeSource.BUSINESS_DOCS:
                    if intent.confidence >= confidence_threshold + 0.2:  # Require 20% higher confidence
                        sources_to_query.append(secondary)
                        logger.info(f"Adding business_docs as secondary (high confidence: {intent.confidence})")
                    else:
                        logger.info(f"Skipping business_docs secondary - insufficient confidence: {intent.confidence}")
                else:
                    sources_to_query.append(secondary)
        else:
            logger.info(f"Skipping secondary sources due to low confidence: {intent.confidence}")
        
        # Remove duplicates while preserving order
        sources_to_query = list(dict.fromkeys(sources_to_query))
        
        # Log routing decision
        if inference_logger:
            inference_logger.log_routing_decision(
                primary_source=intent.primary_source.value,
                secondary_sources=[s.value for s in intent.secondary_sources],
                confidence=intent.confidence,
                reasoning=intent.reasoning,
                query_type=intent.query_type.value,
                routing_time_ms=routing_time_ms
            )
            inference_logger.log_sources_queried([s.value for s in sources_to_query])
        
        # Step 3: Create QueryIntent for smart query expansion decisions
        # This passes the LLM's understanding of query type to BM25 search
        query_intent = QueryIntent.from_classification(intent)
        logger.info(f"QueryIntent: type={query_intent.query_type}, requires_code={query_intent.requires_code}")
        
        # Step 4: Query appropriate sources (use processed query for retrieval)
        retrieval_start = time.time()
        if len(sources_to_query) == 1:
            # Single source - direct query (no aggressive filtering needed)
            results = await self.query_router.query_single_source(
                sources_to_query[0],
                processed_query,
                top_k,
                inference_logger=inference_logger,
                query_intent=query_intent  # Pass intent for smart BM25 expansion
            )
        else:
            # Multi-source - use stricter filtering to remove irrelevant chunks
            results = await self.query_router.query_multi_source_with_filtering(
                sources_to_query,
                processed_query,
                top_k=top_k,
                final_top_k=top_k,
                min_relevance_score=min_relevance_score,
                inference_logger=inference_logger,
                query_intent=query_intent  # Pass intent for smart BM25 expansion
            )
        retrieval_time_ms = (time.time() - retrieval_start) * 1000
        
        # Step 4: Format context
        context = self._format_context_multi_source(results)
        
        # Step 5: Generate LLM response
        llm_response = None
        llm_start = time.time()
        if self.llm_service:
            system_prompt = self._build_system_prompt(sources_to_query, intent)
            llm_response = self.llm_service.generate_response(
                system_prompt,
                query,
                context
            )
        llm_time_ms = (time.time() - llm_start) * 1000
        
        # Log LLM time
        if inference_logger:
            inference_logger.log_llm_generation(llm_time_ms)
        
        return {
            "routing_decision": intent.to_dict(),
            "sources_queried": [s.value for s in sources_to_query],
            "results": results,
            "context": context,
            "llm_response": llm_response
        }
    
    def _format_context_multi_source(self, results: List[Dict]) -> str:
        """Format context from potentially multiple sources"""
        if not results:
            return "No relevant context found."
        
        # Group by source for better organization
        by_source = {}
        for r in results:
            source = r.get('source', 'unknown')
            if source not in by_source:
                by_source[source] = []
            by_source[source].append(r)
        
        context_parts = []
        
        source_labels = {
            "business_docs": "Business Documentation",
            "php_code": "PHP Backend Code",
            "js_code": "JavaScript Frontend Code",
            "blade_templates": "Blade Templates"
        }
        
        for source, source_results in by_source.items():
            label = source_labels.get(source, source)
            context_parts.append(f"\n{'='*60}")
            context_parts.append(f"Context from {label}:")
            context_parts.append('='*60)
            
            for i, r in enumerate(source_results, 1):
                file_path = r['metadata'].get('file_path') or r['metadata'].get('page_name') or 'N/A'
                context_parts.append(f"\n[{i}] Source: {file_path}")
                if 'rrf_score' in r:
                    context_parts.append(f"    Relevance Score: {r['rrf_score']:.4f}")
                context_parts.append(f"\n{r['content']}\n")
        
        # Join context and apply security filtering to redact sensitive data
        raw_context = "\n".join(context_parts)
        
        # Redact any sensitive information from the context before sending to LLM
        filtered_context = redact_sensitive(raw_context)
        
        return filtered_context
    
    def _build_system_prompt(self, 
                            sources: List[KnowledgeSource],
                            intent: IntentClassificationResult) -> str:
        """Build system prompt based on sources being queried with security hardening"""
        
        # Base domain prompt
        domain_prompt = "You are an expert banking application assistant. "
        
        # Code display guidelines
        code_guidelines = "\n\nIMPORTANT: Avoid including code snippets in your response unless the user explicitly asks for code examples, implementation details, or debugging help. Focus on explaining concepts, logic, and behavior in natural language. Only include code when it's essential to answer the question."
        
        # Build source-specific prompt
        if len(sources) == 1:
            source = sources[0]
            if source == KnowledgeSource.BUSINESS_DOCS:
                source_prompt = domain_prompt + "Answer based on the provided business documentation. Explain banking concepts, processes, and workflows clearly." + code_guidelines
            elif source == KnowledgeSource.PHP_CODE:
                source_prompt = domain_prompt + "Answer based on the provided PHP Laravel backend code. Explain implementation details, code structure, and backend logic in descriptive language." + code_guidelines
            elif source == KnowledgeSource.JS_CODE:
                source_prompt = domain_prompt + "Answer based on the provided JavaScript/React frontend code. Explain UI components, state management, and frontend behavior conceptually." + code_guidelines
            elif source == KnowledgeSource.BLADE_TEMPLATES:
                source_prompt = domain_prompt + "Answer based on the provided Blade templates. Explain view structure, forms, and frontend rendering." + code_guidelines
            else:
                source_prompt = domain_prompt + code_guidelines
        else:
            # Multi-source prompt
            source_prompt = domain_prompt + """Answer using the provided context from multiple sources:
- Business documentation explains WHAT and WHY (business rules and processes)
- PHP code shows HOW it's implemented in the backend
- JavaScript code shows frontend behavior and UI logic
- Blade templates show how it's rendered to users

Provide a comprehensive answer that:
1. Explains the business context first (if available)
2. Describes implementation details in natural language
3. References specific files and sections
4. Maintains coherence across all sources
5. Avoids including code snippets unless the user explicitly requests code examples or debugging help

IMPORTANT: Focus on explaining concepts, logic, and behavior rather than displaying code. Only include code blocks when essential to answer the question (e.g., user asks "show me the code", "how is this implemented", "debug this function").

If the context contains Mermaid diagram code, include it in a mermaid code block."""
        
        # Apply security hardening with banking-specific rules
        hardened_prompt = get_hardened_system_prompt(
            base_prompt=source_prompt,
            additional_rules=get_banking_security_addendum()
        )
        
        return hardened_prompt
