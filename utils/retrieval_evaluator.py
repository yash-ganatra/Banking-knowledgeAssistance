"""
Retrieval Evaluator — CRAG (Corrective RAG) Module

Evaluates whether retrieved chunks actually contain information sufficient
to answer the user's query. Uses a lightweight LLM call to judge each chunk
as CORRECT, AMBIGUOUS, or INCORRECT.

All chunks are evaluated in a single batched LLM call to minimize latency.
Based on the aggregate verdict, the pipeline can:
  - CORRECT:   Proceed to knowledge refinement → LLM generation
  - AMBIGUOUS: Keep good chunks, optionally trigger a single retry
  - INCORRECT: Rewrite query and retry retrieval (max 1 retry)
"""

import json
import logging
import time
from enum import Enum
from typing import List, Dict, Any, Optional, Tuple
from dataclasses import dataclass, field

logger = logging.getLogger(__name__)


# ---------------------------------------------------------------------------
# Data Models
# ---------------------------------------------------------------------------

class EvaluationVerdict(str, Enum):
    """Verdict for individual chunk or aggregate evaluation."""
    CORRECT = "correct"       # Chunk contains specific answer information
    AMBIGUOUS = "ambiguous"   # Chunk partially relevant but may not answer the question
    INCORRECT = "incorrect"   # Chunk is irrelevant to the question


class CorrectiveAction(str, Enum):
    """Recommended corrective action based on aggregate verdict."""
    NONE = "none"                         # Proceed normally
    REFINE = "refine"                     # Apply knowledge refinement only
    RETRY_WITH_REWRITE = "retry_rewrite"  # Rewrite query and retry retrieval
    RETRY_ALT_SOURCES = "retry_alt"       # Retry with alternative sources


@dataclass
class ChunkEvaluation:
    """Evaluation result for a single retrieved chunk."""
    chunk_index: int
    verdict: EvaluationVerdict
    confidence: float          # 0.0 – 1.0
    reason: str                # Brief explanation from the LLM
    chunk_id: str = ""
    source: str = ""


@dataclass
class RetrievalEvaluation:
    """Aggregate evaluation result for all retrieved chunks."""
    aggregate_verdict: EvaluationVerdict
    overall_confidence: float
    chunk_evaluations: List[ChunkEvaluation]
    recommended_action: CorrectiveAction
    correct_count: int = 0
    ambiguous_count: int = 0
    incorrect_count: int = 0
    evaluation_time_ms: float = 0.0

    def get_passing_chunk_indices(self) -> List[int]:
        """Return indices of chunks that passed evaluation (CORRECT or AMBIGUOUS)."""
        return [
            e.chunk_index for e in self.chunk_evaluations
            if e.verdict in (EvaluationVerdict.CORRECT, EvaluationVerdict.AMBIGUOUS)
        ]

    def to_dict(self) -> Dict[str, Any]:
        """Serialize for logging / API response."""
        return {
            "aggregate_verdict": self.aggregate_verdict.value,
            "overall_confidence": round(self.overall_confidence, 3),
            "correct_count": self.correct_count,
            "ambiguous_count": self.ambiguous_count,
            "incorrect_count": self.incorrect_count,
            "recommended_action": self.recommended_action.value,
            "evaluation_time_ms": round(self.evaluation_time_ms, 1),
            "chunk_evaluations": [
                {
                    "index": e.chunk_index,
                    "verdict": e.verdict.value,
                    "confidence": round(e.confidence, 3),
                    "reason": e.reason,
                    "chunk_id": e.chunk_id,
                    "source": e.source,
                }
                for e in self.chunk_evaluations
            ],
        }


# ---------------------------------------------------------------------------
# Retrieval Evaluator
# ---------------------------------------------------------------------------

class RetrievalEvaluator:
    """
    LLM-based retrieval evaluator for CRAG.

    Batches all retrieved chunks into a single LLM call and asks the model
    to judge each chunk's relevance to the user query.
    """

    def __init__(
        self,
        groq_api_key: str,
        model: str = "llama-3.1-8b-instant",
        rate_limiter=None,
    ):
        """
        Args:
            groq_api_key: Groq API key
            model:        Model for evaluation (8b-instant for speed)
            rate_limiter: Optional shared GroqRateLimiter instance
        """
        from groq import Groq
        from utils.groq_rate_limiter import GroqRateLimiter

        self.client = Groq(api_key=groq_api_key)
        self.model = model
        self.rate_limiter = rate_limiter or GroqRateLimiter(
            max_retries=3,
            base_delay=2.0,
            daily_token_limit=100_000,
            enable_cache=True,
            cache_ttl=1800,
        )
        logger.info(f"RetrievalEvaluator initialized (model={model})")

    # ------------------------------------------------------------------
    # Public API
    # ------------------------------------------------------------------

    def evaluate_batch(
        self,
        query: str,
        chunks: List[Dict[str, Any]],
        max_preview_chars: int = 800,
    ) -> RetrievalEvaluation:
        """
        Evaluate all retrieved chunks in a single LLM call.

        Args:
            query:             Original user query
            chunks:            List of chunk dicts (must have 'content', 'metadata', optionally 'id'/'source')
            max_preview_chars: Max chars per chunk to send to evaluator (keeps prompt small)

        Returns:
            RetrievalEvaluation with per-chunk verdicts and aggregate decision
        """
        if not chunks:
            return RetrievalEvaluation(
                aggregate_verdict=EvaluationVerdict.INCORRECT,
                overall_confidence=1.0,
                chunk_evaluations=[],
                recommended_action=CorrectiveAction.RETRY_WITH_REWRITE,
                evaluation_time_ms=0.0,
            )

        start = time.time()

        # Build the evaluation prompt
        prompt = self._build_evaluation_prompt(query, chunks, max_preview_chars)

        # Call the LLM
        try:
            raw_response = self._call_llm(prompt, query)
            chunk_evals = self._parse_evaluation_response(raw_response, len(chunks), chunks)
        except Exception as e:
            logger.error(f"CRAG evaluation LLM call failed: {e}")
            # Fallback: treat everything as AMBIGUOUS so pipeline continues
            chunk_evals = [
                ChunkEvaluation(
                    chunk_index=i,
                    verdict=EvaluationVerdict.AMBIGUOUS,
                    confidence=0.5,
                    reason="Evaluation failed — defaulting to AMBIGUOUS",
                    chunk_id=c.get("id", ""),
                    source=c.get("source", ""),
                )
                for i, c in enumerate(chunks)
            ]

        elapsed_ms = (time.time() - start) * 1000

        # Compute aggregate
        aggregate, confidence = self._compute_aggregate_verdict(chunk_evals)
        action = self._recommend_action(aggregate, confidence)

        correct = sum(1 for e in chunk_evals if e.verdict == EvaluationVerdict.CORRECT)
        ambiguous = sum(1 for e in chunk_evals if e.verdict == EvaluationVerdict.AMBIGUOUS)
        incorrect = sum(1 for e in chunk_evals if e.verdict == EvaluationVerdict.INCORRECT)

        evaluation = RetrievalEvaluation(
            aggregate_verdict=aggregate,
            overall_confidence=confidence,
            chunk_evaluations=chunk_evals,
            recommended_action=action,
            correct_count=correct,
            ambiguous_count=ambiguous,
            incorrect_count=incorrect,
            evaluation_time_ms=elapsed_ms,
        )

        logger.info(
            f"CRAG evaluation: {aggregate.value} "
            f"(correct={correct}, ambiguous={ambiguous}, incorrect={incorrect}) "
            f"action={action.value} in {elapsed_ms:.0f}ms"
        )

        return evaluation

    # ------------------------------------------------------------------
    # Prompt Building
    # ------------------------------------------------------------------

    def _build_evaluation_prompt(
        self,
        query: str,
        chunks: List[Dict[str, Any]],
        max_chars: int = 800,
    ) -> str:
        """Build a structured evaluation prompt with all chunks."""

        chunk_texts = []
        for i, chunk in enumerate(chunks):
            content = chunk.get("content", "")[:max_chars]
            source = chunk.get("source", "unknown")
            file_path = (
                chunk.get("metadata", {}).get("file_path")
                or chunk.get("metadata", {}).get("page_name")
                or "N/A"
            )
            chunk_texts.append(
                f"--- CHUNK {i} [source: {source}, file: {file_path}] ---\n{content}\n"
            )

        all_chunks_text = "\n".join(chunk_texts)

        return f"""You are a retrieval quality evaluator for a banking knowledge assistant.

TASK: Determine whether each retrieved chunk contains information that can help answer the user's question. Do NOT answer the question yourself.

USER QUESTION: "{query}"

RETRIEVED CHUNKS:
{all_chunks_text}

For EACH chunk, output a JSON verdict:
- "correct":   The chunk contains specific information that directly helps answer the question.
- "ambiguous":  The chunk is somewhat related but doesn't clearly answer the question.
- "incorrect":  The chunk is irrelevant or off-topic for this question.

Respond with ONLY a JSON array, one object per chunk, in order. Example for 3 chunks:
[
  {{"index": 0, "verdict": "correct", "confidence": 0.9, "reason": "Contains FD tenure limits"}},
  {{"index": 1, "verdict": "incorrect", "confidence": 0.85, "reason": "About loan processing, not FD"}},
  {{"index": 2, "verdict": "ambiguous", "confidence": 0.6, "reason": "Mentions FD but no tenure info"}}
]

IMPORTANT: Return ONLY the JSON array, no other text."""

    # ------------------------------------------------------------------
    # LLM Interaction
    # ------------------------------------------------------------------

    def _call_llm(self, system_prompt: str, query: str) -> str:
        """Make the evaluation LLM call with retry logic."""

        @self.rate_limiter.with_retry
        def _make_call(client, model, messages, temperature, max_tokens):
            return client.chat.completions.create(
                model=model,
                messages=messages,
                temperature=temperature,
                max_tokens=max_tokens,
            )

        response = _make_call(
            client=self.client,
            model=self.model,
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": f"Evaluate the chunks for this query: {query}"},
            ],
            temperature=0.1,   # Low temperature for consistent JSON output
            max_tokens=1024,
        )

        return response.choices[0].message.content

    # ------------------------------------------------------------------
    # Response Parsing
    # ------------------------------------------------------------------

    def _parse_evaluation_response(
        self,
        raw_response: str,
        expected_count: int,
        chunks: List[Dict[str, Any]],
    ) -> List[ChunkEvaluation]:
        """Parse the LLM's JSON response into ChunkEvaluation objects."""

        # Try to extract JSON from the response
        evaluations = []

        try:
            # Clean response — sometimes LLM wraps in markdown code block
            cleaned = raw_response.strip()
            if cleaned.startswith("```"):
                # Remove markdown code fences
                lines = cleaned.split("\n")
                cleaned = "\n".join(
                    line for line in lines
                    if not line.strip().startswith("```")
                )

            parsed = json.loads(cleaned)

            if not isinstance(parsed, list):
                raise ValueError("Expected JSON array")

            for item in parsed:
                idx = item.get("index", len(evaluations))
                verdict_str = item.get("verdict", "ambiguous").lower().strip()
                confidence = float(item.get("confidence", 0.5))
                reason = item.get("reason", "")

                # Map verdict string
                verdict_map = {
                    "correct": EvaluationVerdict.CORRECT,
                    "ambiguous": EvaluationVerdict.AMBIGUOUS,
                    "incorrect": EvaluationVerdict.INCORRECT,
                }
                verdict = verdict_map.get(verdict_str, EvaluationVerdict.AMBIGUOUS)

                chunk = chunks[idx] if idx < len(chunks) else {}
                evaluations.append(
                    ChunkEvaluation(
                        chunk_index=idx,
                        verdict=verdict,
                        confidence=min(max(confidence, 0.0), 1.0),
                        reason=reason[:200],  # Cap reason length
                        chunk_id=chunk.get("id", ""),
                        source=chunk.get("source", ""),
                    )
                )

        except (json.JSONDecodeError, ValueError, KeyError) as e:
            logger.warning(f"Failed to parse CRAG evaluation JSON: {e}. Using fallback.")
            # Fallback: keyword-based parsing of the raw response
            evaluations = self._fallback_parse(raw_response, expected_count, chunks)

        # Pad if fewer evaluations than expected
        while len(evaluations) < expected_count:
            idx = len(evaluations)
            chunk = chunks[idx] if idx < len(chunks) else {}
            evaluations.append(
                ChunkEvaluation(
                    chunk_index=idx,
                    verdict=EvaluationVerdict.AMBIGUOUS,
                    confidence=0.5,
                    reason="No evaluation received — defaulting to AMBIGUOUS",
                    chunk_id=chunk.get("id", ""),
                    source=chunk.get("source", ""),
                )
            )

        return evaluations[:expected_count]

    def _fallback_parse(
        self,
        raw_response: str,
        expected_count: int,
        chunks: List[Dict[str, Any]],
    ) -> List[ChunkEvaluation]:
        """Keyword-based fallback when JSON parsing fails."""
        evaluations = []
        response_lower = raw_response.lower()

        for i in range(expected_count):
            chunk = chunks[i] if i < len(chunks) else {}

            # Look for verdict keywords near chunk references
            # Simple heuristic: count keyword occurrences
            if f"chunk {i}" in response_lower or f"index {i}" in response_lower:
                # Find the section about this chunk
                section_start = response_lower.find(f"chunk {i}")
                if section_start == -1:
                    section_start = response_lower.find(f"index {i}")
                section = response_lower[section_start:section_start + 200]

                if "incorrect" in section or "irrelevant" in section:
                    verdict = EvaluationVerdict.INCORRECT
                elif "correct" in section or "relevant" in section or "directly" in section:
                    verdict = EvaluationVerdict.CORRECT
                else:
                    verdict = EvaluationVerdict.AMBIGUOUS
            else:
                verdict = EvaluationVerdict.AMBIGUOUS

            evaluations.append(
                ChunkEvaluation(
                    chunk_index=i,
                    verdict=verdict,
                    confidence=0.5,
                    reason="Parsed via fallback heuristic",
                    chunk_id=chunk.get("id", ""),
                    source=chunk.get("source", ""),
                )
            )

        return evaluations

    # ------------------------------------------------------------------
    # Aggregate Verdict
    # ------------------------------------------------------------------

    def _compute_aggregate_verdict(
        self,
        evaluations: List[ChunkEvaluation],
    ) -> Tuple[EvaluationVerdict, float]:
        """
        Compute aggregate verdict using majority voting with confidence weighting.

        Returns:
            Tuple of (aggregate_verdict, overall_confidence)
        """
        if not evaluations:
            return EvaluationVerdict.INCORRECT, 1.0

        correct = sum(1 for e in evaluations if e.verdict == EvaluationVerdict.CORRECT)
        ambiguous = sum(1 for e in evaluations if e.verdict == EvaluationVerdict.AMBIGUOUS)
        incorrect = sum(1 for e in evaluations if e.verdict == EvaluationVerdict.INCORRECT)
        total = len(evaluations)

        # Weighted confidence: average of individual confidences
        avg_confidence = sum(e.confidence for e in evaluations) / total

        # Decision logic:
        # - If majority CORRECT → CORRECT
        # - If majority INCORRECT → INCORRECT
        # - Otherwise → AMBIGUOUS
        if correct > total / 2:
            return EvaluationVerdict.CORRECT, avg_confidence
        elif incorrect > total / 2:
            return EvaluationVerdict.INCORRECT, avg_confidence
        else:
            return EvaluationVerdict.AMBIGUOUS, avg_confidence

    def _recommend_action(
        self,
        verdict: EvaluationVerdict,
        confidence: float,
    ) -> CorrectiveAction:
        """Recommend corrective action based on the aggregate verdict."""
        if verdict == EvaluationVerdict.CORRECT:
            # High-quality retrieval — apply refinement for maximum precision
            return CorrectiveAction.REFINE

        elif verdict == EvaluationVerdict.AMBIGUOUS:
            if confidence < 0.4:
                # Very low confidence even on ambiguous — worth retrying
                return CorrectiveAction.RETRY_WITH_REWRITE
            else:
                # Partial coverage — refine what we have
                return CorrectiveAction.REFINE

        else:  # INCORRECT
            return CorrectiveAction.RETRY_WITH_REWRITE
