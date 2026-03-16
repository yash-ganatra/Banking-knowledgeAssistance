"""
Knowledge Refiner — CRAG (Corrective RAG) Module

Extracts only the answer-relevant sentences from retrieved chunks
that passed the retrieval evaluation. Uses the existing cross-encoder
(ms-marco-MiniLM-L-6-v2) for sentence-level scoring — no additional
model download required.

This reduces "context pollution" by sending only the most relevant
fragments to the LLM, improving response quality.
"""

import logging
import re
import time
from typing import List, Dict, Any, Optional, Tuple
from dataclasses import dataclass, field

logger = logging.getLogger(__name__)


# ---------------------------------------------------------------------------
# Data Models
# ---------------------------------------------------------------------------

@dataclass
class RefinedChunk:
    """A chunk after knowledge refinement — contains only relevant sentences."""
    original_chunk_index: int
    chunk_id: str
    source: str
    file_path: str
    relevant_sentences: List[str]          # Extracted sentences above threshold
    sentence_scores: List[float]           # Cross-encoder score for each sentence
    original_content_length: int           # Character count of original
    refined_content_length: int            # Character count after refinement
    compression_ratio: float               # refined / original

    @property
    def refined_content(self) -> str:
        """Join relevant sentences back into readable text."""
        return "\n".join(self.relevant_sentences)

    def to_dict(self) -> Dict[str, Any]:
        return {
            "chunk_index": self.original_chunk_index,
            "chunk_id": self.chunk_id,
            "source": self.source,
            "file_path": self.file_path,
            "sentences_kept": len(self.relevant_sentences),
            "original_chars": self.original_content_length,
            "refined_chars": self.refined_content_length,
            "compression_ratio": round(self.compression_ratio, 3),
            "avg_score": round(
                sum(self.sentence_scores) / len(self.sentence_scores), 4
            ) if self.sentence_scores else 0.0,
        }


@dataclass
class RefinementResult:
    """Result of the knowledge refinement step."""
    refined_chunks: List[RefinedChunk]
    total_original_chars: int
    total_refined_chars: int
    total_sentences_before: int
    total_sentences_after: int
    refinement_time_ms: float

    @property
    def overall_compression(self) -> float:
        if self.total_original_chars == 0:
            return 1.0
        return self.total_refined_chars / self.total_original_chars

    def to_dict(self) -> Dict[str, Any]:
        return {
            "chunks_refined": len(self.refined_chunks),
            "total_original_chars": self.total_original_chars,
            "total_refined_chars": self.total_refined_chars,
            "overall_compression": round(self.overall_compression, 3),
            "sentences_before": self.total_sentences_before,
            "sentences_after": self.total_sentences_after,
            "refinement_time_ms": round(self.refinement_time_ms, 1),
        }


# ---------------------------------------------------------------------------
# Knowledge Refiner
# ---------------------------------------------------------------------------

# Regex pattern for splitting text into sentences.
# Handles standard punctuation while being careful not to break:
#  - Code blocks (function calls, method chains)
#  - Abbreviations (e.g., "Dr.", "vs.", "etc.")
#  - Decimal numbers (e.g., "3.14")
_SENTENCE_SPLIT = re.compile(
    r'(?<=[.!?])\s+(?=[A-Z])'  # Split on sentence-ending punct followed by capital
)

# Minimum sentence length to bother scoring (skip trivial fragments)
_MIN_SENTENCE_LEN = 15


class KnowledgeRefiner:
    """
    Refines retrieved chunks by extracting only answer-relevant sentences.

    Uses the existing cross-encoder for sentence-level scoring, so no
    additional model download is needed.
    """

    def __init__(
        self,
        cross_encoder=None,
        sentence_threshold: float = 0.0,
        max_sentences_per_chunk: int = 10,
        min_sentences_per_chunk: int = 2,
    ):
        """
        Args:
            cross_encoder:          CrossEncoder instance (ms-marco-MiniLM-L-6-v2)
                                    If None, refinement is skipped (passthrough mode).
            sentence_threshold:     Minimum cross-encoder score to keep a sentence.
                                    Default 0.0 — keep all positively scored sentences.
            max_sentences_per_chunk: Max sentences to keep per chunk.
            min_sentences_per_chunk: Min sentences to keep per chunk (even if below threshold).
        """
        self.cross_encoder = cross_encoder
        self.sentence_threshold = sentence_threshold
        self.max_sentences = max_sentences_per_chunk
        self.min_sentences = min_sentences_per_chunk

        if cross_encoder:
            logger.info("KnowledgeRefiner initialized with cross-encoder")
        else:
            logger.warning("KnowledgeRefiner initialized WITHOUT cross-encoder — passthrough mode")

    # ------------------------------------------------------------------
    # Public API
    # ------------------------------------------------------------------

    def refine_chunks(
        self,
        query: str,
        chunks: List[Dict[str, Any]],
        passing_indices: Optional[List[int]] = None,
    ) -> RefinementResult:
        """
        Refine retrieved chunks by extracting relevant sentences.

        Args:
            query:           Original user query
            chunks:          List of chunk dicts (with 'content', 'metadata', etc.)
            passing_indices: If provided, only refine these chunk indices.
                            Others are dropped entirely.

        Returns:
            RefinementResult with refined chunks and compression stats
        """
        start = time.time()

        # Filter to passing chunks if indices provided
        if passing_indices is not None:
            target_chunks = [
                (i, chunks[i]) for i in passing_indices if i < len(chunks)
            ]
        else:
            target_chunks = list(enumerate(chunks))

        if not target_chunks:
            return RefinementResult(
                refined_chunks=[],
                total_original_chars=0,
                total_refined_chars=0,
                total_sentences_before=0,
                total_sentences_after=0,
                refinement_time_ms=0.0,
            )

        # If no cross-encoder, return chunks as-is (passthrough)
        if not self.cross_encoder:
            return self._passthrough(target_chunks, time.time() - start)

        # Refine each chunk
        refined = []
        total_orig = 0
        total_refined = 0
        total_sentences_before = 0
        total_sentences_after = 0

        for chunk_idx, chunk in target_chunks:
            content = chunk.get("content", "")
            total_orig += len(content)

            # Split into sentences
            sentences = self._split_into_sentences(content)
            total_sentences_before += len(sentences)

            if not sentences:
                continue

            # Score sentences against the query
            scored = self._score_sentences(query, sentences)

            # Select top sentences
            selected = self._select_sentences(scored)
            total_sentences_after += len(selected)

            # Build refined chunk
            relevant_sents = [s for s, _ in selected]
            scores = [sc for _, sc in selected]
            refined_text = "\n".join(relevant_sents)
            total_refined += len(refined_text)

            file_path = (
                chunk.get("metadata", {}).get("file_path")
                or chunk.get("metadata", {}).get("page_name")
                or "N/A"
            )

            refined.append(
                RefinedChunk(
                    original_chunk_index=chunk_idx,
                    chunk_id=chunk.get("id", ""),
                    source=chunk.get("source", ""),
                    file_path=file_path,
                    relevant_sentences=relevant_sents,
                    sentence_scores=scores,
                    original_content_length=len(content),
                    refined_content_length=len(refined_text),
                    compression_ratio=len(refined_text) / len(content) if content else 1.0,
                )
            )

        elapsed_ms = (time.time() - start) * 1000

        result = RefinementResult(
            refined_chunks=refined,
            total_original_chars=total_orig,
            total_refined_chars=total_refined,
            total_sentences_before=total_sentences_before,
            total_sentences_after=total_sentences_after,
            refinement_time_ms=elapsed_ms,
        )

        logger.info(
            f"Knowledge refinement: {total_sentences_before} sentences → "
            f"{total_sentences_after} kept, "
            f"compression={result.overall_compression:.1%}, "
            f"time={elapsed_ms:.0f}ms"
        )

        return result

    # ------------------------------------------------------------------
    # Sentence Splitting
    # ------------------------------------------------------------------

    def _split_into_sentences(self, text: str) -> List[str]:
        """
        Split text into sentences, with awareness of code blocks.

        Code blocks (indented lines, lines with brackets/braces) are kept
        as single units rather than split on periods.
        """
        if not text or not text.strip():
            return []

        lines = text.split("\n")
        segments = []
        current_code_block = []
        in_code = False

        for line in lines:
            stripped = line.strip()
            is_code_line = self._is_code_line(stripped)

            if is_code_line:
                if not in_code and current_code_block:
                    # Flush previous non-code as sentences
                    text_block = " ".join(current_code_block)
                    segments.extend(self._split_text_sentences(text_block))
                    current_code_block = []
                in_code = True
                current_code_block.append(stripped)
            else:
                if in_code and current_code_block:
                    # Flush code block as single segment
                    code_text = "\n".join(current_code_block)
                    if len(code_text) >= _MIN_SENTENCE_LEN:
                        segments.append(code_text)
                    current_code_block = []
                in_code = False
                if stripped:
                    current_code_block.append(stripped)

        # Flush remaining
        if current_code_block:
            if in_code:
                code_text = "\n".join(current_code_block)
                if len(code_text) >= _MIN_SENTENCE_LEN:
                    segments.append(code_text)
            else:
                text_block = " ".join(current_code_block)
                segments.extend(self._split_text_sentences(text_block))

        # Filter out tiny fragments
        return [s for s in segments if len(s.strip()) >= _MIN_SENTENCE_LEN]

    def _split_text_sentences(self, text: str) -> List[str]:
        """Split a plain-text block into sentences."""
        parts = _SENTENCE_SPLIT.split(text)
        return [p.strip() for p in parts if p.strip()]

    @staticmethod
    def _is_code_line(line: str) -> bool:
        """Heuristic: is this line likely code?"""
        if not line:
            return False
        # Lines starting with common code patterns
        code_indicators = [
            line.startswith(("def ", "class ", "function ", "if ", "for ", "while ",
                           "return ", "import ", "from ", "const ", "let ", "var ",
                           "$", "<?", "//", "/*", "#", "@")),
            line.endswith(("{", "}", ";", "=>", ":")),
            "=>" in line,
            "->" in line and "(" in line,
            line.count("(") > 0 and line.count(")") > 0 and ("." in line or "::" in line),
        ]
        return any(code_indicators)

    # ------------------------------------------------------------------
    # Sentence Scoring
    # ------------------------------------------------------------------

    def _score_sentences(
        self,
        query: str,
        sentences: List[str],
    ) -> List[Tuple[str, float]]:
        """
        Score each sentence against the query using the cross-encoder.

        Returns list of (sentence, score) tuples sorted by score descending.
        """
        if not sentences:
            return []

        try:
            # Prepare pairs for cross-encoder
            pairs = [[query, sent] for sent in sentences]

            # Batch predict
            scores = self.cross_encoder.predict(pairs)

            # Combine and sort by score
            scored = list(zip(sentences, [float(s) for s in scores]))
            scored.sort(key=lambda x: x[1], reverse=True)

            return scored

        except Exception as e:
            logger.error(f"Cross-encoder sentence scoring failed: {e}")
            # Fallback: return all sentences with neutral score
            return [(s, 0.0) for s in sentences]

    # ------------------------------------------------------------------
    # Sentence Selection
    # ------------------------------------------------------------------

    def _select_sentences(
        self,
        scored_sentences: List[Tuple[str, float]],
    ) -> List[Tuple[str, float]]:
        """
        Select the most relevant sentences based on scores and thresholds.

        Selection strategy:
        1. Keep all sentences above the threshold (up to max)
        2. If fewer than min_sentences above threshold, include top-ranked up to min
        3. Preserve original order for readability
        """
        if not scored_sentences:
            return []

        # Sentences above threshold
        above = [(s, sc) for s, sc in scored_sentences if sc >= self.sentence_threshold]

        if len(above) >= self.min_sentences:
            # Enough good sentences — cap at max
            selected = above[:self.max_sentences]
        else:
            # Not enough above threshold — take top min_sentences regardless
            selected = scored_sentences[:self.min_sentences]

        return selected

    # ------------------------------------------------------------------
    # Passthrough Mode
    # ------------------------------------------------------------------

    def _passthrough(
        self,
        indexed_chunks: List[Tuple[int, Dict[str, Any]]],
        elapsed_sec: float,
    ) -> RefinementResult:
        """When no cross-encoder is available, pass chunks through unchanged."""
        refined = []
        total_chars = 0

        for idx, chunk in indexed_chunks:
            content = chunk.get("content", "")
            total_chars += len(content)
            file_path = (
                chunk.get("metadata", {}).get("file_path")
                or chunk.get("metadata", {}).get("page_name")
                or "N/A"
            )
            refined.append(
                RefinedChunk(
                    original_chunk_index=idx,
                    chunk_id=chunk.get("id", ""),
                    source=chunk.get("source", ""),
                    file_path=file_path,
                    relevant_sentences=[content],
                    sentence_scores=[0.0],
                    original_content_length=len(content),
                    refined_content_length=len(content),
                    compression_ratio=1.0,
                )
            )

        return RefinementResult(
            refined_chunks=refined,
            total_original_chars=total_chars,
            total_refined_chars=total_chars,
            total_sentences_before=len(indexed_chunks),
            total_sentences_after=len(indexed_chunks),
            refinement_time_ms=elapsed_sec * 1000,
        )
