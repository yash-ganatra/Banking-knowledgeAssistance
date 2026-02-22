
# Banking Knowledge Assistance — One‑Page Overview

## Why this application exists
Enterprises with long‑lived banking and financial systems often face fragmented documentation, legacy codebases, and tribal knowledge locked in teams. This leads to slow change cycles, onboarding delays, and operational risk. The application centralizes knowledge from legacy code, configuration, and documentation into a unified, searchable intelligence layer to help teams understand, modernize, and maintain complex systems faster and more safely. It converts scattered artifacts into structured, retrievable knowledge that scales across teams and time.

## How it helps enterprise legacy systems
- **Accelerates discovery** of legacy logic, flows, and dependencies across services and codebases.
- **Reduces risk** by surfacing authoritative references from source, not memory, with traceable provenance.
- **Improves delivery** via quicker impact analysis, root‑cause investigation, and change planning.
- **Enables modernization** by mapping legacy artifacts into structured knowledge models and retrievable context.
- **Supports compliance** with auditable answers and repeatable retrieval pathways.

## High‑level architecture (components)
1. **Ingestion & Parsing**
   - Language‑aware parsers and chunkers extract code, metadata, and documentation.
   - Structured enrichment captures functions, controllers, UI elements, routes, and database references.
   - Normalizes naming conventions and links related artifacts across layers.

2. **Embedding & Indexing**
   - Chunks are embedded and stored in vector databases and BM25 indices.
   - Hybrid retrieval supports semantic + keyword search with configurable ranking.
   - Supports re‑embedding and incremental updates as sources change.

3. **Query Orchestration**
   - A query router selects the best retrieval strategy per query type.
   - Context is assembled from ranked chunks with provenance and metadata filters.
   - Integrates safeguards to avoid hallucinations and favor grounded sources.

4. **Inference & Response**
   - Retrieval‑augmented generation (RAG) produces grounded answers.
   - Responses include citations, summaries, and optional follow‑up prompts.
   - Logging captures prompts, sources, and outputs for auditability.

5. **Backend Services**
   - FastAPI‑based APIs for search, query, auth, and administration.
   - Data models, migrations, and security helpers for enterprise readiness.
   - Observability for usage metrics and troubleshooting.

6. **Frontend**
   - Vite + Tailwind UI for interactive querying, source inspection, and feedback.

## Capabilities
- Legacy code understanding and functional mapping
- Documentation search across code + docs
- Dependency discovery and impact analysis
- Auditable answers with source citations
- Custom chunking strategies for domain‑specific artifacts
- Cross‑layer traceability from UI to controller to database
- Configurable retrieval strategies per team or use case

## Ingestion workflow (summary)
1. **Collect artifacts**: source code, configs, and documentation.
2. **Parse and chunk**: semantic splitting by language and structure.
3. **Enrich metadata**: tags, ownership, route/controller relationships.
4. **Embed and index**: vector + BM25 for robust retrieval.
5. **Validate & monitor**: sampling checks and quality metrics.
6. **Serve queries**: orchestrated retrieval and grounded responses.

## Technical stack (high level)
- **Python** for ingestion, embedding, and inference
- **Vector DB + BM25** for hybrid retrieval
- **FastAPI** backend services
- **Vite + Tailwind** frontend

---
This one‑page overview explains the business need, legacy‑system benefits, architecture, capabilities, and ingestion flow in concise form for presentations.
