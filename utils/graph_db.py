"""
Neo4j Graph Database Connection and Query Utilities

Provides connection management, schema initialization, and Cypher query builders
for the Graph-Enhanced RAG system.

Schema follows the GraphDB.docx specification:
- Nodes: Route, Controller, Action, Model, BladeView, UIElement, JSFunction, DBTable, DBColumn
- Relationships: ROUTE_CALLS_ACTION, HAS_ACTION, ACTION_LOADS_VIEW, ACTION_USES_MODEL, etc.
"""

import os
import logging
from typing import Dict, List, Optional, Any, Tuple
from dataclasses import dataclass, field
from contextlib import contextmanager

try:
    from neo4j import GraphDatabase, Driver
    from neo4j.exceptions import ServiceUnavailable, AuthError
except ImportError:
    raise ImportError("Please install neo4j: pip install neo4j>=5.0.0")

logger = logging.getLogger(__name__)


# =============================================================================
# Data Classes
# =============================================================================

@dataclass
class GraphNode:
    """Represents a node in the code knowledge graph"""
    label: str  # Node type: Route, Controller, Action, Model, etc.
    properties: Dict[str, Any]
    node_id: Optional[str] = None


@dataclass 
class GraphRelationship:
    """Represents a relationship between nodes"""
    rel_type: str  # Relationship type: ROUTE_CALLS_ACTION, HAS_ACTION, etc.
    from_node_id: str
    to_node_id: str
    properties: Dict[str, Any] = field(default_factory=dict)


@dataclass
class GraphQueryResult:
    """Result from a graph traversal query"""
    entities: List[Dict[str, Any]]  # Related entities found
    paths: List[List[str]]  # Traversal paths taken
    depth_reached: int
    query_time_ms: float


# =============================================================================
# Schema Definition
# =============================================================================

class GraphSchema:
    """
    Schema definition for the code knowledge graph.
    Matches the GraphDB.docx specification.
    """
    
    # Node labels and their key properties
    NODE_LABELS = {
        "Route": ["id", "uri", "method", "middleware", "file"],
        "Controller": ["id", "name", "namespace", "file"],
        "Action": ["id", "name", "visibility", "start_line", "end_line", "controller_id"],
        "Model": ["id", "name", "table", "file"],
        "BladeView": ["id", "name", "file"],
        "UIElement": ["id", "type", "name", "html_id", "validation"],
        "JSFunction": ["id", "name", "file"],
        "DBTable": ["id", "name"],
        "DBColumn": ["id", "name", "type", "nullable", "default"],
    }
    
    # Relationship types with (from_label, to_label)
    RELATIONSHIP_TYPES = {
        "ROUTE_CALLS_ACTION": ("Route", "Action"),
        "HAS_ACTION": ("Controller", "Action"),
        "ACTION_LOADS_VIEW": ("Action", "BladeView"),
        "ACTION_USES_MODEL": ("Action", "Model"),
        "ACTION_READS_TABLE": ("Action", "DBTable"),
        "ACTION_WRITES_TABLE": ("Action", "DBTable"),
        "MODEL_MAPS_TO_TABLE": ("Model", "DBTable"),
        "TABLE_HAS_COLUMN": ("DBTable", "DBColumn"),
        "VIEW_CONTAINS_ELEMENT": ("BladeView", "UIElement"),
        "VIEW_INCLUDES_JS": ("BladeView", "JSFunction"),
        "JS_VALIDATES_ELEMENT": ("JSFunction", "UIElement"),
        "UI_POSTS_TO_ACTION": ("UIElement", "Action"),
    }
    
    @classmethod
    def get_schema_creation_queries(cls) -> List[str]:
        """
        Generate Cypher queries to create constraints and indexes.
        
        Returns:
            List of Cypher CREATE CONSTRAINT/INDEX queries
        """
        queries = []
        
        # Create unique constraints on id for each node type
        for label in cls.NODE_LABELS.keys():
            queries.append(
                f"CREATE CONSTRAINT {label.lower()}_id_unique IF NOT EXISTS "
                f"FOR (n:{label}) REQUIRE n.id IS UNIQUE"
            )
        
        # Create indexes for commonly queried properties
        index_specs = [
            ("Route", "uri"),
            ("Controller", "name"),
            ("Action", "name"),
            ("Model", "name"),
            ("BladeView", "name"),
            ("JSFunction", "name"),
            ("DBTable", "name"),
        ]
        
        for label, prop in index_specs:
            queries.append(
                f"CREATE INDEX {label.lower()}_{prop}_idx IF NOT EXISTS "
                f"FOR (n:{label}) ON (n.{prop})"
            )
        
        # Create fulltext index for searching across all code entities
        fulltext_labels = ["Controller", "Action", "Model", "BladeView", "JSFunction"]
        queries.append(
            f"CREATE FULLTEXT INDEX code_entity_search IF NOT EXISTS "
            f"FOR (n:{':'.join(fulltext_labels)}) ON EACH [n.name, n.file]"
        )
        
        return queries


# =============================================================================
# Neo4j Connection Manager
# =============================================================================

class Neo4jConnection:
    """
    Manages Neo4j database connections with connection pooling and retry logic.
    
    Usage:
        conn = Neo4jConnection.from_env()
        with conn.session() as session:
            result = session.run("MATCH (n) RETURN count(n)")
    """
    
    def __init__(
        self,
        uri: str,
        user: str,
        password: str,
        max_connection_lifetime: int = 3600,
        max_connection_pool_size: int = 50
    ):
        """
        Initialize Neo4j connection.
        
        Args:
            uri: Neo4j bolt URI (e.g., bolt://localhost:7687)
            user: Database username
            password: Database password
            max_connection_lifetime: Max lifetime of connections in seconds
            max_connection_pool_size: Max connections in pool
        """
        self.uri = uri
        self.user = user
        self._driver: Optional[Driver] = None
        
        try:
            self._driver = GraphDatabase.driver(
                uri,
                auth=(user, password),
                max_connection_lifetime=max_connection_lifetime,
                max_connection_pool_size=max_connection_pool_size
            )
            # Verify connectivity
            self._driver.verify_connectivity()
            logger.info(f"Connected to Neo4j at {uri}")
        except AuthError as e:
            logger.error(f"Neo4j authentication failed: {e}")
            raise
        except ServiceUnavailable as e:
            logger.error(f"Neo4j service unavailable at {uri}: {e}")
            raise
    
    @classmethod
    def from_env(cls) -> "Neo4jConnection":
        """
        Create connection from environment variables.
        
        Environment variables:
            NEO4J_URI: Bolt URI (default: bolt://localhost:7687)
            NEO4J_USER: Username (default: neo4j)
            NEO4J_PASSWORD: Password (required)
        """
        uri = os.getenv("NEO4J_URI", "bolt://localhost:7687")
        user = os.getenv("NEO4J_USER", "neo4j")
        password = os.getenv("NEO4J_PASSWORD")
        
        if not password:
            raise ValueError("NEO4J_PASSWORD environment variable is required")
        
        return cls(uri, user, password)
    
    @contextmanager
    def session(self, database: str = "neo4j"):
        """
        Context manager for database sessions.
        
        Args:
            database: Database name (default: neo4j)
            
        Yields:
            Neo4j session
        """
        if not self._driver:
            raise RuntimeError("Neo4j driver not initialized")
        
        session = self._driver.session(database=database)
        try:
            yield session
        finally:
            session.close()
    
    def close(self):
        """Close the driver connection."""
        if self._driver:
            self._driver.close()
            logger.info("Neo4j connection closed")
    
    def is_connected(self) -> bool:
        """Check if connection is healthy."""
        if not self._driver:
            return False
        try:
            self._driver.verify_connectivity()
            return True
        except Exception:
            return False
    
    def init_schema(self) -> Dict[str, bool]:
        """
        Initialize the graph schema with constraints and indexes.
        
        Returns:
            Dict mapping query description to success status
        """
        results = {}
        queries = GraphSchema.get_schema_creation_queries()
        
        with self.session() as session:
            for query in queries:
                try:
                    session.run(query)
                    results[query.split(" ")[2]] = True  # Extract constraint/index name
                    logger.debug(f"Executed: {query[:50]}...")
                except Exception as e:
                    results[query.split(" ")[2]] = False
                    logger.warning(f"Schema query failed: {e}")
        
        logger.info(f"Schema initialization: {sum(results.values())}/{len(results)} successful")
        return results


# =============================================================================
# Graph Query Builders
# =============================================================================

class GraphQuery:
    """
    Cypher query builders for common graph traversal patterns.
    
    Provides pre-built queries for:
    - Function call graphs
    - Route → Controller → Action → Model flow
    - View → UIElement → Action connections
    - Related entity lookups
    """
    
    def __init__(self, connection: Neo4jConnection):
        """
        Initialize query builder.
        
        Args:
            connection: Neo4jConnection instance
        """
        self.conn = connection
    
    def get_function_call_graph(
        self,
        function_name: str,
        depth: int = 2,
        include_models: bool = True
    ) -> GraphQueryResult:
        """
        Get the call graph for a function/action, showing what it calls
        and what calls it — including relationship types.
        
        Args:
            function_name: Name of the function/action to analyze
            depth: Maximum traversal depth (1-3)
            include_models: Whether to include Model dependencies
            
        Returns:
            GraphQueryResult with related entities, paths, and relationship triples
        """
        import time
        start = time.time()
        
        depth = min(max(depth, 1), 3)  # Clamp to 1-3
        
        # Query outgoing: this action uses/loads/reads/writes what
        outgoing_query = """
        MATCH (a:Action {name: $name})
        OPTIONAL MATCH (a)-[r:ACTION_USES_MODEL|ACTION_LOADS_VIEW|ACTION_READS_TABLE|ACTION_WRITES_TABLE]->(related)
        RETURN a.name as source, type(r) as rel_type, 
               related, labels(related) as target_labels
        """
        
        # Query incoming: what calls/posts to this action
        incoming_query = """
        MATCH (a:Action {name: $name})
        OPTIONAL MATCH (caller)-[r:ROUTE_CALLS_ACTION|UI_POSTS_TO_ACTION|HAS_ACTION]->(a)
        RETURN caller, labels(caller) as caller_labels, 
               type(r) as rel_type, a.name as target
        """
        
        entities = []
        relationships = []
        
        with self.conn.session() as session:
            # Get outgoing relationships
            result = session.run(outgoing_query, name=function_name)
            for record in result:
                if record["related"] and record["rel_type"]:
                    related_dict = dict(record["related"])
                    target_labels = record["target_labels"] or []
                    entity_type = target_labels[0] if target_labels else "Entity"
                    related_dict["type"] = entity_type
                    entities.append(related_dict)
                    relationships.append({
                        "source": record["source"],
                        "source_type": "Action",
                        "relationship": record["rel_type"],
                        "target": related_dict.get("name", "Unknown"),
                        "target_type": entity_type
                    })
            
            # Get incoming relationships
            result = session.run(incoming_query, name=function_name)
            for record in result:
                if record["caller"] and record["rel_type"]:
                    caller_dict = dict(record["caller"])
                    caller_labels = record["caller_labels"] or []
                    caller_type = caller_labels[0] if caller_labels else "Entity"
                    caller_dict["type"] = caller_type
                    entities.append(caller_dict)
                    relationships.append({
                        "source": caller_dict.get("name", "Unknown"),
                        "source_type": caller_type,
                        "relationship": record["rel_type"],
                        "target": record["target"],
                        "target_type": "Action"
                    })
        
        elapsed = (time.time() - start) * 1000
        
        return GraphQueryResult(
            entities=entities,
            paths=relationships,  # Now contains structured relationship triples
            depth_reached=depth,
            query_time_ms=elapsed
        )
    
    def get_route_flow(self, route_uri: str) -> GraphQueryResult:
        """
        Get the complete request flow for a route:
        Route → Controller → Action → Model → DBTable
        
        Args:
            route_uri: URI pattern of the route (e.g., "/loans/{id}/approve")
            
        Returns:
            GraphQueryResult with the complete flow
        """
        import time
        start = time.time()
        
        query = """
        MATCH (r:Route {uri: $uri})
        OPTIONAL MATCH (r)-[:ROUTE_CALLS_ACTION]->(a:Action)
        OPTIONAL MATCH (a)<-[:HAS_ACTION]-(c:Controller)
        OPTIONAL MATCH (a)-[:ACTION_USES_MODEL]->(m:Model)
        OPTIONAL MATCH (a)-[:ACTION_LOADS_VIEW]->(v:BladeView)
        OPTIONAL MATCH (m)-[:MODEL_MAPS_TO_TABLE]->(t:DBTable)
        RETURN r, c, a, collect(distinct m) as models, 
               collect(distinct v) as views, collect(distinct t) as tables
        """
        
        entities = []
        
        with self.conn.session() as session:
            result = session.run(query, uri=route_uri)
            for record in result:
                if record["r"]:
                    entities.append({"type": "Route", **dict(record["r"])})
                if record["c"]:
                    entities.append({"type": "Controller", **dict(record["c"])})
                if record["a"]:
                    entities.append({"type": "Action", **dict(record["a"])})
                for m in record["models"]:
                    if m:
                        entities.append({"type": "Model", **dict(m)})
                for v in record["views"]:
                    if v:
                        entities.append({"type": "BladeView", **dict(v)})
                for t in record["tables"]:
                    if t:
                        entities.append({"type": "DBTable", **dict(t)})
        
        elapsed = (time.time() - start) * 1000
        
        return GraphQueryResult(
            entities=entities,
            paths=[],
            depth_reached=4,  # Fixed depth for route flow
            query_time_ms=elapsed
        )
    
    def get_related_views(self, controller_name: str) -> GraphQueryResult:
        """
        Get all Blade views loaded by a controller's actions,
        including relationship triples.
        
        Args:
            controller_name: Name of the controller
            
        Returns:
            GraphQueryResult with views, elements, and relationship triples
        """
        import time
        start = time.time()
        
        query = """
        MATCH (c:Controller {name: $name})-[r1:HAS_ACTION]->(a:Action)
        OPTIONAL MATCH (a)-[r2:ACTION_LOADS_VIEW]->(v:BladeView)
        OPTIONAL MATCH (v)-[r3:VIEW_CONTAINS_ELEMENT]->(e:UIElement)
        OPTIONAL MATCH (e)-[r4:UI_POSTS_TO_ACTION]->(target:Action)
        RETURN c.name as ctrl_name, a, v, e, target,
               type(r1) as r1_type, type(r2) as r2_type, 
               type(r3) as r3_type, type(r4) as r4_type
        """
        
        entities = []
        relationships = []
        seen_entities = set()
        seen_rels = set()
        
        with self.conn.session() as session:
            result = session.run(query, name=controller_name)
            for record in result:
                ctrl = record["ctrl_name"]
                
                # Action entity
                if record["a"]:
                    a_dict = {"type": "Action", **dict(record["a"])}
                    a_name = a_dict.get("name", "")
                    if a_name and a_name not in seen_entities:
                        seen_entities.add(a_name)
                        entities.append(a_dict)
                    # Controller -> HAS_ACTION -> Action
                    rel_key = f"{ctrl}-HAS_ACTION-{a_name}"
                    if rel_key not in seen_rels:
                        seen_rels.add(rel_key)
                        relationships.append({
                            "source": ctrl, "source_type": "Controller",
                            "relationship": "HAS_ACTION",
                            "target": a_name, "target_type": "Action"
                        })
                    
                    # Action -> ACTION_LOADS_VIEW -> BladeView
                    if record["v"] and record["r2_type"]:
                        v_dict = {"type": "BladeView", **dict(record["v"])}
                        v_name = v_dict.get("name", "")
                        if v_name and v_name not in seen_entities:
                            seen_entities.add(v_name)
                            entities.append(v_dict)
                        rel_key = f"{a_name}-ACTION_LOADS_VIEW-{v_name}"
                        if rel_key not in seen_rels:
                            seen_rels.add(rel_key)
                            relationships.append({
                                "source": a_name, "source_type": "Action",
                                "relationship": "ACTION_LOADS_VIEW",
                                "target": v_name, "target_type": "BladeView"
                            })
                        
                        # BladeView -> VIEW_CONTAINS_ELEMENT -> UIElement
                        if record["e"] and record["r3_type"]:
                            e_dict = {"type": "UIElement", **dict(record["e"])}
                            e_name = e_dict.get("name", e_dict.get("html_id", ""))
                            if e_name and e_name not in seen_entities:
                                seen_entities.add(e_name)
                                entities.append(e_dict)
                            rel_key = f"{v_name}-VIEW_CONTAINS_ELEMENT-{e_name}"
                            if rel_key not in seen_rels:
                                seen_rels.add(rel_key)
                                relationships.append({
                                    "source": v_name, "source_type": "BladeView",
                                    "relationship": "VIEW_CONTAINS_ELEMENT",
                                    "target": e_name, "target_type": "UIElement"
                                })
                            
                            # UIElement -> UI_POSTS_TO_ACTION -> TargetAction
                            if record["target"] and record["r4_type"]:
                                t_dict = {"type": "TargetAction", **dict(record["target"])}
                                t_name = t_dict.get("name", "")
                                if t_name and t_name not in seen_entities:
                                    seen_entities.add(t_name)
                                    entities.append(t_dict)
                                rel_key = f"{e_name}-UI_POSTS_TO_ACTION-{t_name}"
                                if rel_key not in seen_rels:
                                    seen_rels.add(rel_key)
                                    relationships.append({
                                        "source": e_name, "source_type": "UIElement",
                                        "relationship": "UI_POSTS_TO_ACTION",
                                        "target": t_name, "target_type": "TargetAction"
                                    })
        
        elapsed = (time.time() - start) * 1000
        
        return GraphQueryResult(
            entities=entities,
            paths=relationships,
            depth_reached=3,
            query_time_ms=elapsed
        )
    
    def find_entities_by_name(
        self,
        name_pattern: str,
        labels: Optional[List[str]] = None,
        limit: int = 10
    ) -> List[Dict[str, Any]]:
        """
        Find entities by name pattern using fulltext search.
        
        Args:
            name_pattern: Search pattern (supports wildcards)
            labels: Optional list of labels to filter by
            limit: Maximum results
            
        Returns:
            List of matching entities
        """
        # Use fulltext index for fuzzy matching
        query = """
        CALL db.index.fulltext.queryNodes('code_entity_search', $pattern)
        YIELD node, score
        WHERE score > 0.5
        RETURN node, labels(node) as labels, score
        ORDER BY score DESC
        LIMIT $limit
        """
        
        entities = []
        
        with self.conn.session() as session:
            result = session.run(query, pattern=name_pattern, limit=limit)
            for record in result:
                node_labels = record["labels"]
                if labels and not any(l in node_labels for l in labels):
                    continue
                entities.append({
                    "labels": node_labels,
                    "score": record["score"],
                    **dict(record["node"])
                })
        
        return entities
    
    def get_entity_neighbors(
        self,
        entity_id: str,
        label: str,
        relationship_types: Optional[List[str]] = None,
        direction: str = "both"
    ) -> List[Dict[str, Any]]:
        """
        Get all neighbors of an entity.
        
        Args:
            entity_id: ID of the entity
            label: Label of the entity
            relationship_types: Filter by relationship types (optional)
            direction: "in", "out", or "both"
            
        Returns:
            List of neighboring entities with relationship info
        """
        # Build direction-specific pattern
        if direction == "in":
            pattern = f"(neighbor)-[r]->({label.lower()}:{label} {{id: $id}})"
        elif direction == "out":
            pattern = f"({label.lower()}:{label} {{id: $id}})-[r]->(neighbor)"
        else:
            pattern = f"({label.lower()}:{label} {{id: $id}})-[r]-(neighbor)"
        
        rel_filter = ""
        if relationship_types:
            rel_types = "|".join(relationship_types)
            rel_filter = f"AND type(r) IN [{', '.join(repr(t) for t in relationship_types)}]"
        
        query = f"""
        MATCH {pattern}
        WHERE true {rel_filter}
        RETURN neighbor, type(r) as rel_type, labels(neighbor) as labels
        """
        
        neighbors = []
        
        with self.conn.session() as session:
            result = session.run(query, id=entity_id)
            for record in result:
                neighbors.append({
                    "rel_type": record["rel_type"],
                    "labels": record["labels"],
                    **dict(record["neighbor"])
                })
        
        return neighbors


# =============================================================================
# Graph Loader (Bulk Data Import)
# =============================================================================

class GraphLoader:
    """
    Loads parsed code artifacts into Neo4j.
    Reads JSON files from parsers and creates nodes/relationships.
    """
    
    def __init__(self, connection: Neo4jConnection):
        """
        Initialize graph loader.
        
        Args:
            connection: Neo4jConnection instance
        """
        self.conn = connection
    
    def create_node(self, node: GraphNode) -> Optional[str]:
        """
        Create or merge a single node.
        
        Args:
            node: GraphNode to create
            
        Returns:
            Node ID if successful, None otherwise
        """
        props = ", ".join(f"{k}: ${k}" for k in node.properties.keys())
        query = f"""
        MERGE (n:{node.label} {{{props}}})
        RETURN n.id as id
        """
        
        with self.conn.session() as session:
            try:
                result = session.run(query, **node.properties)
                record = result.single()
                return record["id"] if record else None
            except Exception as e:
                logger.error(f"Failed to create node {node.label}: {e}")
                return None
    
    def create_relationship(self, rel: GraphRelationship) -> bool:
        """
        Create a relationship between two nodes.
        
        Args:
            rel: GraphRelationship to create
            
        Returns:
            True if successful, False otherwise
        """
        # Get the expected labels from schema
        rel_spec = GraphSchema.RELATIONSHIP_TYPES.get(rel.rel_type)
        if not rel_spec:
            logger.warning(f"Unknown relationship type: {rel.rel_type}")
            from_label, to_label = "Node", "Node"
        else:
            from_label, to_label = rel_spec
        
        query = f"""
        MATCH (from:{from_label} {{id: $from_id}})
        MATCH (to:{to_label} {{id: $to_id}})
        MERGE (from)-[r:{rel.rel_type}]->(to)
        RETURN type(r) as rel_type
        """
        
        with self.conn.session() as session:
            try:
                result = session.run(
                    query,
                    from_id=rel.from_node_id,
                    to_id=rel.to_node_id
                )
                return result.single() is not None
            except Exception as e:
                logger.error(f"Failed to create relationship {rel.rel_type}: {e}")
                return False
    
    def load_nodes_batch(self, nodes: List[Dict[str, Any]], label: str) -> int:
        """
        Batch load nodes of the same type.
        
        Args:
            nodes: List of node property dicts
            label: Node label
            
        Returns:
            Number of nodes created/merged
        """
        if not nodes:
            return 0
        
        # Use UNWIND for batch operations
        query = f"""
        UNWIND $nodes as node
        MERGE (n:{label} {{id: node.id}})
        SET n += node
        RETURN count(n) as count
        """
        
        with self.conn.session() as session:
            try:
                result = session.run(query, nodes=nodes)
                record = result.single()
                count = record["count"] if record else 0
                logger.info(f"Loaded {count} {label} nodes")
                return count
            except Exception as e:
                logger.error(f"Batch load failed for {label}: {e}")
                return 0
    
    def load_relationships_batch(
        self,
        relationships: List[Tuple[str, str]],
        rel_type: str,
        from_label: str,
        to_label: str
    ) -> int:
        """
        Batch load relationships.
        
        Args:
            relationships: List of (from_id, to_id) tuples
            rel_type: Relationship type
            from_label: Label of source nodes
            to_label: Label of target nodes
            
        Returns:
            Number of relationships created
        """
        if not relationships:
            return 0
        
        rels = [{"from_id": f, "to_id": t} for f, t in relationships]
        
        query = f"""
        UNWIND $rels as rel
        MATCH (from:{from_label} {{id: rel.from_id}})
        MATCH (to:{to_label} {{id: rel.to_id}})
        MERGE (from)-[r:{rel_type}]->(to)
        RETURN count(r) as count
        """
        
        with self.conn.session() as session:
            try:
                result = session.run(query, rels=rels)
                record = result.single()
                count = record["count"] if record else 0
                logger.info(f"Created {count} {rel_type} relationships")
                return count
            except Exception as e:
                logger.error(f"Batch relationship load failed for {rel_type}: {e}")
                return 0
    
    def clear_graph(self) -> bool:
        """
        Clear all nodes and relationships from the graph.
        WARNING: This is destructive!
        
        Returns:
            True if successful
        """
        with self.conn.session() as session:
            try:
                session.run("MATCH (n) DETACH DELETE n")
                logger.warning("Graph cleared - all nodes and relationships deleted")
                return True
            except Exception as e:
                logger.error(f"Failed to clear graph: {e}")
                return False
    
    def get_stats(self) -> Dict[str, int]:
        """
        Get statistics about the graph.
        
        Returns:
            Dict with node/relationship counts by type
        """
        stats = {}
        
        with self.conn.session() as session:
            # Count nodes by label
            for label in GraphSchema.NODE_LABELS.keys():
                result = session.run(f"MATCH (n:{label}) RETURN count(n) as count")
                record = result.single()
                stats[f"nodes_{label}"] = record["count"] if record else 0
            
            # Count relationships by type
            for rel_type in GraphSchema.RELATIONSHIP_TYPES.keys():
                result = session.run(
                    f"MATCH ()-[r:{rel_type}]->() RETURN count(r) as count"
                )
                record = result.single()
                stats[f"rels_{rel_type}"] = record["count"] if record else 0
        
        return stats


# =============================================================================
# Convenience Functions
# =============================================================================

def get_graph_connection() -> Neo4jConnection:
    """
    Get a Neo4j connection from environment variables.
    
    Returns:
        Neo4jConnection instance
    """
    return Neo4jConnection.from_env()


def init_graph_schema(connection: Optional[Neo4jConnection] = None) -> Dict[str, bool]:
    """
    Initialize the graph schema.
    
    Args:
        connection: Optional connection (creates new if not provided)
        
    Returns:
        Dict with schema creation results
    """
    conn = connection or get_graph_connection()
    return conn.init_schema()
