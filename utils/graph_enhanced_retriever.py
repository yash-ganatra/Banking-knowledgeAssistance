"""
Graph-Enhanced Retriever

Integrates Neo4j graph context with ChromaDB vector retrieval.
Enhances retrieved chunks by adding related entities discovered through
graph traversal, providing the LLM with a "high-level map" of code relationships.

Key Features:
- Entity extraction from retrieved chunks (function names, class names)
- Graph traversal to find related entities
- RRF-based merging of vector results with graph context
- Configurable graph enhancement for different query types
"""

import logging
import re
from typing import Dict, List, Optional, Any, Tuple, Set
from dataclasses import dataclass, field
from enum import Enum

logger = logging.getLogger(__name__)


class GraphEnhancementMode(Enum):
    """When to apply graph enhancement"""
    ALWAYS = "always"
    CODE_QUERIES_ONLY = "code_queries_only"
    FLOW_QUERIES_ONLY = "flow_queries_only"
    DISABLED = "disabled"


@dataclass
class GraphContext:
    """Context extracted from the knowledge graph"""
    related_entities: List[Dict[str, Any]]
    call_graph: List[Dict[str, Any]]  # Functions that call/are called by retrieved chunks
    route_flow: Optional[Dict[str, Any]]  # Route → Controller → Action flow if applicable
    relationships: List[Dict[str, Any]]  # Explicit relationship triples: source → type → target
    traversal_depth: int
    query_time_ms: float
    cypher_queries: List[str] = field(default_factory=list)  # Cypher queries executed
    cypher_analytics_text: Optional[str] = None  # Pre-formatted text from TextToCypher
    
    def to_context_string(self) -> str:
        """
        Format graph context as a string for LLM prompt.
        
        Returns:
            Formatted context string showing relationships
        """
        if not self.related_entities and not self.call_graph and not self.cypher_analytics_text:
            return ""
        
        parts = []
        
        # If TextToCypher produced formatted results, use them directly
        if self.cypher_analytics_text:
            parts.append(self.cypher_analytics_text)
            return "\n".join(parts)
        
        if self.call_graph:
            parts.append("### Related Code Flow:")
            for item in self.call_graph[:5]:
                entity_type = item.get("type", "Entity")
                name = item.get("name", "Unknown")
                file_path = item.get("file", "")
                parts.append(f"- {entity_type}: `{name}` ({file_path})")
        
        if self.route_flow:
            parts.append("\n### Request Flow:")
            route = self.route_flow.get("route", {})
            controller = self.route_flow.get("controller", {})
            action = self.route_flow.get("action", {})
            models = self.route_flow.get("models", [])
            
            if route:
                parts.append(f"- Route: `{route.get('method', 'GET')} {route.get('uri', '')}`")
            if controller and action:
                parts.append(f"- Handler: `{controller.get('name', '')}@{action.get('name', '')}`")
            if models:
                model_names = [f"`{m.get('name', '')}`" for m in models[:3]]
                parts.append(f"- Models: {', '.join(model_names)}")
        
        if self.related_entities:
            # Check if any entities have call_count (function analytics query result)
            has_call_counts = any(e.get("call_count") is not None for e in self.related_entities)
            
            if has_call_counts:
                parts.append("\n### Function Call Frequency (sorted by most called):")
                for entity in self.related_entities[:50]:
                    name = entity.get("name", "Unknown")
                    call_count = entity.get("call_count", 0)
                    callers = entity.get("callers", [])
                    caller_info = ""
                    if callers:
                        caller_names = [f"`{c.get('name', '?')}`" for c in callers[:5]]
                        caller_info = f" (called by: {', '.join(caller_names)})"
                    parts.append(f"- `{name}`: **{call_count} calls**{caller_info}")
            else:
                # Standard entity listing
                by_type: Dict[str, List[str]] = {}
                for entity in self.related_entities[:50]:
                    entity_type = entity.get("type", entity.get("labels", ["Entity"])[0] if isinstance(entity.get("labels"), list) else "Entity")
                    name = entity.get("name", "Unknown")
                    if entity_type not in by_type:
                        by_type[entity_type] = []
                    by_type[entity_type].append(name)
                
                parts.append("\n### Related Entities:")
                for entity_type, names in by_type.items():
                    parts.append(f"- {entity_type}s: {', '.join(f'`{n}`' for n in names[:50])}")
        
        return "\n".join(parts) if parts else ""

    def to_dict(self) -> Dict[str, Any]:
        """Convert to dictionary for JSON serialization/logging."""
        return {
            "related_entities": self.related_entities,
            "call_graph": self.call_graph,
            "route_flow": self.route_flow,
            "relationships": self.relationships,
            "traversal_depth": self.traversal_depth,
            "query_time_ms": self.query_time_ms,
            "cypher_queries": self.cypher_queries,
            "cypher_analytics_text": self.cypher_analytics_text
        }


@dataclass
class GraphEnhancementConfig:
    """Configuration for graph enhancement"""
    max_traversal_depth: int = 2
    max_related_entities: int = 10
    rrf_graph_weight: float = 0.3  # Weight for graph-discovered entities in RRF
    entity_extraction_patterns: List[str] = None  # Regex patterns for entity extraction
    
    def __post_init__(self):
        if self.entity_extraction_patterns is None:
            # Default patterns for PHP/Laravel code
            self.entity_extraction_patterns = [
                r'class\s+(\w+)',  # Class definitions
                r'function\s+(\w+)',  # Function definitions
                r'(\w+)Controller',  # Controller references
                r'(\w+)::(\w+)',  # Static method calls (Model::method)
                r"view\s*\(\s*['\"]([^'\"]+)['\"]",  # view() calls
                r'route\s*\(\s*[\'"]([^\'"]+)[\'"]',  # route() calls
            ]


class GraphEnhancedRetriever:
    """
    Enhances vector retrieval with graph context.
    
    Workflow:
    1. Receive initial vector search results
    2. Extract entity names from result content/metadata
    3. Query graph for related entities
    4. Optionally fetch vector embeddings for discovered entities
    5. Merge results using RRF with graph context bonus
    
    Usage:
        retriever = GraphEnhancedRetriever(neo4j_uri="bolt://localhost:7687")
        enhanced_results = await retriever.enhance_results(
            query="How does loan approval work?",
            results=vector_results,
            max_graph_depth=2
        )
    """
    
    def __init__(
        self,
        neo4j_uri: Optional[str] = None,
        config: Optional[GraphEnhancementConfig] = None,
        mode: GraphEnhancementMode = GraphEnhancementMode.CODE_QUERIES_ONLY,
        groq_api_key: Optional[str] = None
    ):
        """
        Initialize the graph-enhanced retriever.
        
        Args:
            neo4j_uri: Neo4j bolt URI (defaults to env var NEO4J_URI)
            config: Enhancement configuration
            mode: When to apply graph enhancement
            groq_api_key: Groq API key for Text-to-Cypher (reads from env if not provided)
        """
        self.config = config or GraphEnhancementConfig()
        self.mode = mode
        self.connection = None
        self.query_builder = None
        self._init_attempted = False
        self._neo4j_uri = neo4j_uri
        self._groq_api_key = groq_api_key
        self.text_to_cypher = None  # Initialized lazily after connection
        
        # Compile entity extraction patterns
        self._entity_patterns = [
            re.compile(p, re.MULTILINE) for p in self.config.entity_extraction_patterns
        ]
    
    def _ensure_connection(self) -> bool:
        """
        Lazily initialize Neo4j connection.
        
        Returns:
            True if connection is available
        """
        if self.connection is not None:
            return self.connection.is_connected()
        
        if self._init_attempted:
            return False
        
        self._init_attempted = True
        
        try:
            from utils.graph_db import Neo4jConnection, GraphQuery
            
            if self._neo4j_uri:
                import os
                user = os.getenv("NEO4J_USER", "neo4j")
                password = os.getenv("NEO4J_PASSWORD", "")
                self.connection = Neo4jConnection(self._neo4j_uri, user, password)
            else:
                self.connection = Neo4jConnection.from_env()
            
            self.query_builder = GraphQuery(self.connection)
            
            # Initialize Text-to-Cypher for dynamic analytics
            try:
                from utils.text_to_cypher import TextToCypher
                self.text_to_cypher = TextToCypher(
                    neo4j_connection=self.connection,
                    groq_api_key=self._groq_api_key
                )
                logger.info("TextToCypher initialized for dynamic graph analytics")
            except Exception as e:
                logger.warning(f"TextToCypher not available: {e}")
                self.text_to_cypher = None
            
            logger.info("Graph enhancement enabled - Neo4j connected")
            return True
            
        except Exception as e:
            logger.warning(f"Graph enhancement disabled - Neo4j not available: {e}")
            return False
    
    def should_enhance(
        self,
        query_type: str,
        requires_code: bool
    ) -> bool:
        """
        Determine if graph enhancement should be applied.
        
        Args:
            query_type: Query type from intent classification
            requires_code: Whether query requires code examples
            
        Returns:
            True if enhancement should be applied
        """
        if self.mode == GraphEnhancementMode.DISABLED:
            return False
        
        if self.mode == GraphEnhancementMode.ALWAYS:
            return True
        
        if self.mode == GraphEnhancementMode.CODE_QUERIES_ONLY:
            return query_type in ["implementation", "debugging", "architecture"] or requires_code
        
        if self.mode == GraphEnhancementMode.FLOW_QUERIES_ONLY:
            return query_type in ["architecture", "debugging"]
        
        return False
    
    def extract_entities(
        self,
        results: List[Dict[str, Any]]
    ) -> Set[Tuple[str, str]]:
        """
        Extract entity names from retrieved chunks.
        
        Args:
            results: Vector search results
            
        Returns:
            Set of (entity_name, entity_type) tuples
        """
        entities = set()
        
        for result in results:
            content = result.get("content", "")
            metadata = result.get("metadata", {})
            
            # Extract from content using patterns
            for pattern in self._entity_patterns:
                matches = pattern.findall(content)
                for match in matches:
                    if isinstance(match, tuple):
                        # Multiple groups - take first non-empty
                        for group in match:
                            if group:
                                entities.add((group, "detected"))
                                break
                    else:
                        entities.add((match, "detected"))
            
            # Extract from metadata
            if "class_name" in metadata:
                entities.add((metadata["class_name"], "Controller" if "Controller" in metadata["class_name"] else "Class"))
            
            if "method_name" in metadata:
                entities.add((metadata["method_name"], "Action"))
            
            if "file_name" in metadata:
                file_name = metadata["file_name"]
                # Extract view name from blade files
                if ".blade.php" in file_name:
                    view_name = file_name.replace(".blade.php", "")
                    entities.add((view_name, "BladeView"))
        
        logger.debug(f"Extracted {len(entities)} entities from {len(results)} results")
        return entities
    
    def get_graph_context(
        self,
        entities: Set[Tuple[str, str]],
        query: str,
        max_depth: int = 2
    ) -> GraphContext:
        """
        Query the graph for related entities.
        
        Args:
            entities: Extracted entity names
            query: Original query (for route detection)
            max_depth: Maximum traversal depth
            
        Returns:
            GraphContext with related entities and flow information
        """
        import time
        start = time.time()
        
        if not self._ensure_connection() or not self.query_builder:
            return GraphContext([], [], None, [], 0, 0)
        
        related_entities = []
        call_graph = []
        all_relationships = []
        route_flow = None
        cypher_queries = []  # Track all Cypher queries executed
        file_query_handled = False  # Skip call graph traversal if file query already answered
        
        # Check if query mentions a specific PHP file
        file_match = re.search(r'\b([\w]+\.php)\b', query, re.IGNORECASE)
        if file_match:
            file_name = file_match.group(1)
            file_query_handled = True  # File-specific query — skip per-entity call graph traversal
            
            # Detect analytical queries (e.g., "most called", "frequently used")
            analytics_keywords = r'(most\s+called|most\s+used|maximum|frequently|how\s+many\s+times|call\s+count|usage\s+frequency|popular|least\s+called|least\s+used)'
            is_analytics_query = bool(re.search(analytics_keywords, query, re.IGNORECASE))
            
            try:
                if is_analytics_query:
                    # Use analytics query with call counts
                    file_result = self.query_builder.get_most_called_in_file(file_name)
                    logger.info(f"Analytics query: ranked {len(file_result.entities)} functions by call count in '{file_name}'")
                else:
                    # Use basic file query
                    file_result = self.query_builder.get_actions_by_file(file_name)
                    logger.info(f"File query: found {len(file_result.entities)} entities in '{file_name}'")
                
                if file_result.entities:
                    related_entities.extend(file_result.entities)
                    all_relationships.extend(file_result.paths)
                cypher_queries.extend(file_result.cypher_queries)
            except Exception as e:
                logger.warning(f"File-based graph query failed for '{file_name}': {e}")
        
        # Check if query mentions a route pattern
        route_match = re.search(r'/[\w/{}]+', query)
        if route_match:
            route_uri = route_match.group()
            file_query_handled = True  # Route query provides complete flow — skip entity traversal
            try:
                flow_result = self.query_builder.get_route_flow(route_uri)
                if flow_result.entities:
                    route_flow = self._format_route_flow(flow_result.entities)
                    related_entities.extend(flow_result.entities)
                cypher_queries.extend(flow_result.cypher_queries)
                logger.info(f"Route flow query: found {len(flow_result.entities)} entities for '{route_uri}'")
            except Exception as e:
                logger.warning(f"Route flow query failed: {e}")
        
        # Query call graph for each action/function entity
        # Skip if file query or route query already provided complete results (avoids redundant traversals)
        # ALSO: Limit to top 3 entities to prevent query explosion
        if not file_query_handled:
            entities_list = list(entities)[:3]  # Limit to top 3 entities
            logger.info(f"Querying graph for {len(entities_list)} entities (limited from {len(entities)} extracted)")
            for entity_name, entity_type in entities_list:
                if entity_type in ["Action", "detected"]:
                    try:
                        graph_result = self.query_builder.get_function_call_graph(
                            entity_name,
                            depth=max_depth
                        )
                        call_graph.extend(graph_result.entities)
                        # Collect relationship triples from paths
                        if graph_result.paths:
                            all_relationships.extend(graph_result.paths)
                        cypher_queries.extend(graph_result.cypher_queries)
                    except Exception as e:
                        logger.debug(f"Call graph query failed for {entity_name}: {e}")
                
                elif entity_type in ["Controller", "Class"] and entity_name.endswith("Controller"):
                    try:
                        views_result = self.query_builder.get_related_views(entity_name)
                        related_entities.extend(views_result.entities)
                        # Collect relationship triples from paths
                        if views_result.paths:
                            all_relationships.extend(views_result.paths)
                        cypher_queries.extend(views_result.cypher_queries)
                    except Exception as e:
                        logger.debug(f"Related views query failed for {entity_name}: {e}")
        
        # Deduplicate entities by id
        seen_ids = set()
        unique_entities = []
        for entity in related_entities + call_graph:
            entity_id = entity.get("id", str(entity))
            if entity_id not in seen_ids:
                seen_ids.add(entity_id)
                unique_entities.append(entity)
        
        # Deduplicate relationships
        seen_rels = set()
        unique_rels = []
        for rel in all_relationships:
            rel_key = f"{rel.get('source')}-{rel.get('relationship')}-{rel.get('target')}"
            if rel_key not in seen_rels:
                seen_rels.add(rel_key)
                unique_rels.append(rel)
        
        elapsed = (time.time() - start) * 1000
        
        return GraphContext(
            related_entities=unique_entities[:max(self.config.max_related_entities, len(unique_entities))],
            call_graph=call_graph[:5],
            route_flow=route_flow,
            relationships=unique_rels,
            traversal_depth=max_depth,
            query_time_ms=elapsed,
            cypher_queries=cypher_queries
        )
    
    def _format_route_flow(self, entities: List[Dict[str, Any]]) -> Dict[str, Any]:
        """Format route flow entities into structured dict."""
        flow = {"route": None, "controller": None, "action": None, "models": [], "views": []}
        
        for entity in entities:
            entity_type = entity.get("type", "")
            if entity_type == "Route":
                flow["route"] = entity
            elif entity_type == "Controller":
                flow["controller"] = entity
            elif entity_type == "Action":
                flow["action"] = entity
            elif entity_type == "Model":
                flow["models"].append(entity)
            elif entity_type == "BladeView":
                flow["views"].append(entity)
        
        return flow
    
    async def enhance_results(
        self,
        query: str,
        results: Dict[str, List[Dict[str, Any]]],
        query_type: str = "implementation",
        requires_code: bool = True,
        max_graph_depth: int = 2
    ) -> Tuple[Dict[str, List[Dict[str, Any]]], Optional[GraphContext]]:
        """
        Enhance vector retrieval results with graph context.
        
        Args:
            query: User query
            results: Results by source from vector retrieval
            query_type: Query type from intent classifier
            requires_code: Whether query requires code
            max_graph_depth: Maximum graph traversal depth
            
        Returns:
            Tuple of (enhanced results, graph context)
        """
        # Ensure Neo4j + TextToCypher are initialized (lazy)
        self._ensure_connection()
        
        # Detect graph analytics queries using TextToCypher (replaces hardcoded regex)
        if self.text_to_cypher and self.text_to_cypher.is_graph_analytics_query(query):
            logger.info("Graph analytics query detected via TextToCypher, bypassing should_enhance")
            try:
                formatted_text, cypher, records = self.text_to_cypher.execute_and_format(query)
                # records is not None means query executed (even if empty list)
                if formatted_text and records is not None:
                    graph_context = GraphContext(
                        related_entities=[],
                        call_graph=[],
                        route_flow=None,
                        relationships=[],
                        traversal_depth=0,
                        query_time_ms=0,
                        cypher_queries=[cypher] if cypher else [],
                        cypher_analytics_text=formatted_text
                    )
                    logger.info(f"TextToCypher returned {len(records)} records")
                    return results, graph_context
            except Exception as e:
                logger.warning(f"TextToCypher failed: {e}")
            
            # TextToCypher was the intended path but failed — do NOT fall through
            # to the noisy entity extraction. Return results without graph context.
            logger.info("TextToCypher analytics path failed, skipping entity extraction to avoid noise")
            return results, None
        
        # Check if enhancement should be applied
        if not self.should_enhance(query_type, requires_code):
            logger.debug(f"Graph enhancement skipped for query_type={query_type}")
            return results, None
        
        # Flatten results for entity extraction
        all_results = []
        for source_results in results.values():
            all_results.extend(source_results)
        
        if not all_results:
            return results, None
        
        # Extract entities from results
        entities = self.extract_entities(all_results)
        
        if not entities:
            logger.debug("No entities extracted from results")
            return results, None
        
        # Get graph context
        graph_context = self.get_graph_context(entities, query, max_graph_depth)
        
        if not graph_context.related_entities and not graph_context.call_graph:
            logger.debug("No graph context found")
            return results, graph_context
        
        # Boost results that are related to graph-discovered entities
        enhanced_results = self._apply_graph_boost(results, graph_context)
        
        logger.info(
            f"Graph enhancement: found {len(graph_context.related_entities)} related entities, "
            f"query_time={graph_context.query_time_ms:.1f}ms"
        )
        
        return enhanced_results, graph_context
    
    def _apply_graph_boost(
        self,
        results: Dict[str, List[Dict[str, Any]]],
        graph_context: GraphContext
    ) -> Dict[str, List[Dict[str, Any]]]:
        """
        Apply score boost to results that connect to graph-discovered entities.
        
        Args:
            results: Original results by source
            graph_context: Graph context with related entities
            
        Returns:
            Results with boosted scores
        """
        # Build set of entity names from graph context for matching
        graph_entity_names = set()
        for entity in graph_context.related_entities + graph_context.call_graph:
            name = entity.get("name", "")
            if name:
                graph_entity_names.add(name.lower())
        
        if not graph_entity_names:
            return results
        
        enhanced = {}
        
        for source, source_results in results.items():
            enhanced_source_results = []
            
            for result in source_results:
                result_copy = result.copy()
                content = result.get("content", "").lower()
                metadata = result.get("metadata", {})
                
                # Check if result mentions any graph-discovered entities
                boost_score = 0.0
                for entity_name in graph_entity_names:
                    if entity_name in content:
                        boost_score += 0.1
                    if entity_name == metadata.get("class_name", "").lower():
                        boost_score += 0.2
                    if entity_name == metadata.get("method_name", "").lower():
                        boost_score += 0.2
                
                # Apply boost to existing scores
                if boost_score > 0:
                    boost_score = min(boost_score, self.config.rrf_graph_weight)
                    result_copy["graph_boost"] = boost_score
                    
                    # Boost RRF score if present
                    if "rrf_score" in result_copy:
                        result_copy["rrf_score"] += boost_score
                    
                    # Reduce distance if present (lower = better)
                    if "distance" in result_copy:
                        result_copy["distance"] *= (1 - boost_score)
                
                enhanced_source_results.append(result_copy)
            
            # Re-sort by boosted score
            enhanced_source_results.sort(
                key=lambda x: x.get("rrf_score", 0) if "rrf_score" in x else -x.get("distance", 999),
                reverse=("rrf_score" in enhanced_source_results[0] if enhanced_source_results else False)
            )
            
            enhanced[source] = enhanced_source_results
        
        return enhanced
    
    def format_context_for_llm(
        self,
        vector_context: str,
        graph_context: Optional[GraphContext]
    ) -> str:
        """
        Combine vector and graph context for LLM prompt.
        
        Args:
            vector_context: Context from vector retrieval
            graph_context: Context from graph traversal
            
        Returns:
            Combined context string
        """
        if not graph_context:
            return vector_context
        
        graph_str = graph_context.to_context_string()
        
        if not graph_str:
            return vector_context
        
        return f"""## Code Relationships (from knowledge graph)
{graph_str}

## Retrieved Code/Documentation
{vector_context}"""
    
    def close(self):
        """Close the Neo4j connection."""
        if self.connection:
            self.connection.close()


# =============================================================================
# Integration Helper
# =============================================================================

def create_graph_enhanced_retriever(
    neo4j_uri: Optional[str] = None,
    mode: str = "code_queries_only",
    max_depth: int = 2,
    graph_weight: float = 0.3,
    groq_api_key: Optional[str] = None
) -> GraphEnhancedRetriever:
    """
    Factory function to create a configured GraphEnhancedRetriever.
    
    Args:
        neo4j_uri: Neo4j connection URI
        mode: Enhancement mode ("always", "code_queries_only", "flow_queries_only", "disabled")
        max_depth: Maximum graph traversal depth
        graph_weight: RRF weight for graph-discovered entities
        groq_api_key: Groq API key for Text-to-Cypher analytics
        
    Returns:
        Configured GraphEnhancedRetriever instance
    """
    mode_enum = GraphEnhancementMode(mode)
    config = GraphEnhancementConfig(
        max_traversal_depth=max_depth,
        rrf_graph_weight=graph_weight
    )
    
    return GraphEnhancedRetriever(
        neo4j_uri=neo4j_uri,
        config=config,
        mode=mode_enum,
        groq_api_key=groq_api_key
    )
