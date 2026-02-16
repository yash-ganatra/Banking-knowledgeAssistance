"""
Text-to-Cypher: Dynamic Graph Analytics via LLM-generated Cypher Queries

Replaces hardcoded graph analytics with LLM-generated Cypher.
Uses the graph schema to let the LLM write Cypher for any analytics question.
"""

import logging
import os
import re
import time
from typing import Optional, Dict, Any, List, Tuple

from groq import Groq

logger = logging.getLogger(__name__)


# Words that indicate a graph analytics question (vs. a code/doc question)
GRAPH_ANALYTICS_INDICATORS = [
    "most used", "most called", "least used", "least called",
    "top ", "how many", "count", "frequently", "popular",
    "heavily used", "maximum", "minimum",
    "which controller", "which model", "which table", "which function",
    "what tables", "what models", "what functions", "what controllers",
    "how often", "call count", "usage frequency",
    "relationship between", "connected to", "depends on",
    "reads from", "writes to", "calls",
]

# Blocklist for write operations — never execute these
WRITE_KEYWORDS = ["DELETE", "CREATE", "SET", "MERGE", "REMOVE", "DROP", "DETACH"]


class TextToCypher:
    """
    Converts natural-language graph analytics questions into Cypher queries.
    
    Flow: user query → LLM generates Cypher → execute on Neo4j → format results
    """
    
    def __init__(
        self,
        neo4j_connection,
        groq_api_key: Optional[str] = None,
        model: str = "llama-3.1-8b-instant"
    ):
        """
        Args:
            neo4j_connection: Neo4jConnection instance
            groq_api_key: Groq API key (reads from env if not provided)
            model: Groq model for Cypher generation (8b for speed/cost)
        """
        self.conn = neo4j_connection
        api_key = groq_api_key or os.getenv("GROQ_API_KEY")
        if not api_key:
            raise ValueError("GROQ_API_KEY required for TextToCypher")
        self.client = Groq(api_key=api_key)
        self.model = model
        
        # Build the schema description once
        self._schema_prompt = self._build_schema_prompt()
    
    def _build_schema_prompt(self) -> str:
        """
        Build a natural-language description of the graph schema
        from GraphSchema.NODE_LABELS and RELATIONSHIP_TYPES.
        """
        from utils.graph_db import GraphSchema
        
        lines = ["## Graph Database Schema\n"]
        
        # Node types
        lines.append("### Node Types:")
        for label, props in GraphSchema.NODE_LABELS.items():
            lines.append(f"- **{label}**: properties: {', '.join(props)}")
        
        lines.append("\n### Relationship Types (DIRECTION MATTERS — always follow these exactly):")
        # Only include relationships that actually exist in the DB to avoid hallucinations
        valid_rels = {
            'HAS_ACTION': ('Controller', 'Action'),
            'ACTION_CALLS_ACTION': ('Action', 'Action'),
            'ACTION_USES_MODEL': ('Action', 'Model'),
            'ACTION_READS_TABLE': ('Action', 'DBTable'),
            'ACTION_WRITES_TABLE': ('Action', 'DBTable'),
            'ROUTE_CALLS_ACTION': ('Route', 'Action'),
            'ACTION_LOADS_VIEW': ('Action', 'BladeView'),
            'VIEW_CONTAINS_ELEMENT': ('BladeView', 'UIElement'),
            'UI_POSTS_TO_ACTION': ('UIElement', 'Action')
        }
        for rel, (src, dst) in valid_rels.items():
            lines.append(f"- (:{src})-[:{rel}]->(:{dst})")
        
        lines.append("\n### Key Notes:")
        lines.append("- CRITICAL: Relationship directions are fixed. (Action)-[:ACTION_READS_TABLE]->(DBTable) means the Action reads from the table. NEVER reverse directions.")
        lines.append("- HAS_ACTION connects both Controller and HelperClass to Action nodes")
        lines.append("- Action nodes represent PHP methods/functions")
        lines.append("- DBTable nodes represent database tables referenced in code")
        lines.append("- ACTION_CALLS_ACTION represents inter-function calls (e.g., one method calling another)")
        lines.append("- Node IDs use the format: ClassName@methodName for Actions, table_name for DBTable")
        lines.append("- Controller and HelperClass nodes have a 'file' property with the PHP file path")
        
        return "\n".join(lines)
    
    def is_graph_analytics_query(self, query: str) -> bool:
        """
        Quick keyword check to decide if a query MIGHT need graph analytics.
        This is a fast pre-filter before calling the LLM.
        
        Args:
            query: User's natural language query
            
        Returns:
            True if query likely needs graph analytics
        """
        query_lower = query.lower()
        return any(indicator in query_lower for indicator in GRAPH_ANALYTICS_INDICATORS)
    
    def generate_cypher(self, query: str) -> Optional[str]:
        """
        Use LLM to generate a Cypher query from natural language.
        
        Args:
            query: User's natural language question
            
        Returns:
            Generated Cypher query string, or None if generation fails
        """
        system_prompt = f"""You are a Neo4j Cypher query expert. Given a user question about a codebase knowledge graph, generate a Cypher query to answer it.

{self._schema_prompt}

RULES:
1. Generate ONLY a single READ-ONLY Cypher query (MATCH/RETURN only, no CREATE/DELETE/SET/MERGE)
2. Return ONLY the Cypher query, no explanations or markdown
3. Always include meaningful aliases in RETURN (e.g., RETURN t.id AS table_name, count(r) AS usage_count)
4. Use ORDER BY and LIMIT when the question asks for "top N" or "most/least"
5. Default LIMIT to 10 if unspecified
6. CRITICAL: Follow relationship directions EXACTLY as defined in the schema. For example:
   - To find tables used by actions: MATCH (a:Action)-[:ACTION_READS_TABLE]->(t:DBTable)  (Action points TO DBTable)
   - To find models used by actions: MATCH (a:Action)-[:ACTION_USES_MODEL]->(m:Model)  (Action points TO Model)
   - NEVER reverse these arrows
7. For "most used tables": count incoming ACTION_READS_TABLE and ACTION_WRITES_TABLE relationships TO the DBTable:
   MATCH (t:DBTable)
   OPTIONAL MATCH (a1:Action)-[:ACTION_READS_TABLE]->(t)
   OPTIONAL MATCH (a2:Action)-[:ACTION_WRITES_TABLE]->(t)
   RETURN t.id AS table_name, count(DISTINCT a1) + count(DISTINCT a2) AS usage_count
   ORDER BY usage_count DESC
8. For "most called functions": count ROUTE_CALLS_ACTION + UI_POSTS_TO_ACTION + ACTION_CALLS_ACTION relationships pointing to the Action
9. Use OPTIONAL MATCH for each relationship type separately, then sum with count(DISTINCT) to avoid cross-product issues
10. When querying actions in a specific file, match through (parent)-[:HAS_ACTION]->(a:Action) WHERE parent.file contains the filename
11. CASE-INSENSITIVE MATCHING: Do NOT use {{name: toLower("val")}}. Instead use WHERE clause:
   CORRECT: MATCH (n:Controller) WHERE toLower(n.name) CONTAINS toLower("dashboard") RETURN n
   INCORRECT: MATCH (n:Controller {{name: toLower("dashboard")}})
12. Only use node labels and relationship types that exist in the schema above. Do NOT invent new ones.

EXAMPLES:
Q: "What tables does ReviewController use?"
A: MATCH (c:Controller)-[:HAS_ACTION]->(a:Action)
   WHERE toLower(c.name) CONTAINS toLower("ReviewController")
   OPTIONAL MATCH (a)-[:ACTION_READS_TABLE]->(t1:DBTable)
   OPTIONAL MATCH (a)-[:ACTION_WRITES_TABLE]->(t2:DBTable)
   WITH t1, t2
   WHERE t1 IS NOT NULL OR t2 IS NOT NULL
   RETURN coalesce(t1.id, t2.id) as table_name, count(*) as usage_count, collect(distinct t1.id) + collect(distinct t2.id) as operations

Q: "Relationship between ReviewController and ACCOUNT_DETAILS table"
A: MATCH (c:Controller)-[:HAS_ACTION]->(a:Action)
   WHERE toLower(c.name) CONTAINS toLower("ReviewController")
   OPTIONAL MATCH (a)-[r1:ACTION_READS_TABLE]->(t:DBTable)
   WHERE toLower(t.id) CONTAINS toLower("ACCOUNT_DETAILS")
   OPTIONAL MATCH (a)-[r2:ACTION_WRITES_TABLE]->(t)
   WHERE toLower(t.id) CONTAINS toLower("ACCOUNT_DETAILS")
   WITH c, a, t, r1, r2
   WHERE t IS NOT NULL
   RETURN c.name as controller, a.name as action, type(r1) as read_rel, type(r2) as write_rel, t.id as table

Q: "What routes point to DashboardController?"
A: MATCH (c:Controller)-[:HAS_ACTION]->(a:Action)<-[:ROUTE_CALLS_ACTION]-(r:Route)
   WHERE toLower(c.name) CONTAINS toLower("DashboardController")
   RETURN r.method as method, r.uri as uri, a.name as action, c.name as controller"""

        user_prompt = f"Generate a Cypher query for: {query}"
        
        try:
            response = self.client.chat.completions.create(
                model=self.model,
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": user_prompt}
                ],
                temperature=0.0,  # Deterministic for consistent queries
                max_tokens=500
            )
            
            cypher = response.choices[0].message.content.strip()
            
            # Clean up — remove markdown code blocks if present
            if "```" in cypher:
                cypher = re.sub(r'```(?:cypher|sql)?\n?', '', cypher)
                cypher = cypher.replace('```', '').strip()
            
            # Safety check — reject write operations
            cypher_upper = cypher.upper()
            for keyword in WRITE_KEYWORDS:
                # Check for keyword as a standalone word (not part of another word)
                if re.search(rf'\b{keyword}\b', cypher_upper):
                    logger.warning(f"TextToCypher: Rejected write operation: {keyword}")
                    return None
            
            logger.info(f"TextToCypher generated: {cypher}")
            return cypher
            
        except Exception as e:
            logger.error(f"TextToCypher generation failed: {e}")
            return None
    
    def execute_cypher(self, cypher: str) -> Optional[List[Dict[str, Any]]]:
        """
        Execute a Cypher query and return results as a list of dicts.
        
        Args:
            cypher: Cypher query string
            
        Returns:
            List of result records as dicts, or None on error
        """
        try:
            with self.conn.session() as session:
                result = session.run(cypher)
                records = []
                for record in result:
                    records.append(dict(record))
                return records
        except Exception as e:
            logger.error(f"TextToCypher execution failed: {e}")
            return None
    
    def _retry_with_error(self, query: str, failed_cypher: str, error: str) -> Optional[str]:
        """
        Retry Cypher generation by feeding the error back to the LLM.
        
        Args:
            query: Original user question
            failed_cypher: The Cypher that failed
            error: Error message from Neo4j
            
        Returns:
            Corrected Cypher query, or None
        """
        retry_prompt = f"""The following Cypher query failed with an error. Fix it.

Original question: {query}

Failed query:
{failed_cypher}

Error:
{error}

{self._schema_prompt}

Generate ONLY the corrected Cypher query, nothing else. Remember: READ-ONLY queries only (MATCH/RETURN)."""

        try:
            response = self.client.chat.completions.create(
                model=self.model,
                messages=[{"role": "user", "content": retry_prompt}],
                temperature=0.0,
                max_tokens=500
            )
            
            cypher = response.choices[0].message.content.strip()
            if "```" in cypher:
                cypher = re.sub(r'```(?:cypher|sql)?\n?', '', cypher)
                cypher = cypher.replace('```', '').strip()
            
            # Safety re-check
            cypher_upper = cypher.upper()
            for keyword in WRITE_KEYWORDS:
                if re.search(rf'\b{keyword}\b', cypher_upper):
                    return None
            
            logger.info(f"TextToCypher retry generated: {cypher}")
            return cypher
            
        except Exception as e:
            logger.error(f"TextToCypher retry failed: {e}")
            return None
    
    def format_results(self, records: List[Dict[str, Any]], query: str) -> str:
        """
        Format Cypher query results into a human-readable string for LLM context.
        
        Args:
            records: List of result dicts from Neo4j
            query: Original user query (for context)
            
        Returns:
            Formatted string representation of results
        """
        if not records:
            return "No results found in the graph database."
        
        lines = [f"### Graph Analytics Results ({len(records)} records):\n"]
        
        # Get column keys from first record
        keys = list(records[0].keys())
        
        # Build a table-like format
        for i, record in enumerate(records, 1):
            parts = []
            for key in keys:
                value = record[key]
                # Format key nicely
                display_key = key.replace('_', ' ').title()
                parts.append(f"{display_key}: **{value}**")
            lines.append(f"{i}. {' | '.join(parts)}")
        
        return "\n".join(lines)
    
    def execute_and_format(self, query: str) -> Tuple[Optional[str], Optional[str], Optional[List[Dict]]]:
        """
        Full pipeline: generate Cypher → execute → format results.
        
        Args:
            query: User's natural language question
            
        Returns:
            Tuple of (formatted_result_text, cypher_query, raw_records)
            Returns (None, None, None) if the pipeline fails
        """
        start = time.time()
        
        # Step 1: Generate Cypher
        cypher = self.generate_cypher(query)
        if not cypher:
            return None, None, None
        
        # Step 2: Execute
        records = self.execute_cypher(cypher)
        
        # Step 3: If execution failed, retry once with error feedback
        if records is None:
            logger.info("TextToCypher: First attempt failed, retrying with error feedback")
            # Re-execute to capture the actual error message
            try:
                with self.conn.session() as session:
                    session.run(cypher)
            except Exception as e:
                error_msg = str(e)
                corrected = self._retry_with_error(query, cypher, error_msg)
                if corrected:
                    cypher = corrected
                    records = self.execute_cypher(cypher)
        
        if records is None:
            logger.warning("TextToCypher: All attempts failed")
            return None, cypher, None
        
        # Step 4: Format results
        formatted = self.format_results(records, query)
        
        elapsed = (time.time() - start) * 1000
        logger.info(f"TextToCypher pipeline completed in {elapsed:.0f}ms: {len(records)} records")
        
        return formatted, cypher, records
