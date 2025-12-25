# Mermaid Diagram Preservation Strategy

## 🎯 Problem Solved

**Original Question:** "Why are we converting Mermaid diagrams to text? Can't we keep diagrams so users can see them?"

**Answer:** YES! We now keep BOTH:
1. ✅ **Original Mermaid code** → for rendering/visualization
2. ✅ **Text description** → for semantic search

## 🔄 How It Works

### 1. **At Chunking Stage**

```python
# In chunk_cube_docs_optimized.py

# Original Mermaid code is preserved in metadata
diagram_metadata = {
    'chunk_id': f"diagram_{page['id']}",
    'is_mermaid': True,
    'mermaid_code': page['mermaid_diagram'],  # ← PRESERVED!
    # ... other metadata
}

# Text description is created for embedding
diagram_text = convert_mermaid_to_text(page['mermaid_diagram'])

# Chunk stored with BOTH
chunk = {
    'content': diagram_text,  # ← For search
    'metadata': diagram_metadata  # ← Contains original code
}
```

### 2. **At Search/Retrieval Stage**

```python
# Query: "Show me the NPC clearance flow"

results = engine.query("NPC clearance process", top_k=5)

# Result contains:
{
    'content': 'Diagram: NPC Flow\nProcess Steps:\n- Branch Sale\n- NPC L1...',  # ← Searchable text
    'metadata': {
        'mermaid_code': 'flowchart TD\n  A[Branch] --> B[NPC L1]...',  # ← Original diagram!
        'is_mermaid': True
    }
}
```

### 3. **At Display/Rendering Stage**

```python
# Extract and render diagrams
from inference.diagram_renderer import MermaidRenderer

diagrams = MermaidRenderer.extract_diagrams(results)

# Save as HTML (opens in browser)
MermaidRenderer.save_html(
    diagrams[0]['mermaid_code'],
    'npc_flow.html'
)

# Or display in Jupyter
MermaidRenderer.display_jupyter(diagrams[0]['mermaid_code'])

# Or export to Markdown
markdown = MermaidRenderer.render_markdown(diagrams[0]['mermaid_code'])
```

## 📊 Why This Approach?

### ✅ Benefits

| Feature | Text Only | Diagram Only | **Our Hybrid** |
|---------|-----------|--------------|----------------|
| Semantic Search | ✅ | ❌ | ✅ |
| Visual Rendering | ❌ | ✅ | ✅ |
| LLM Context | ✅ | ❌ | ✅ |
| User Experience | ❌ | ✅ | ✅ |
| Storage Cost | Low | Low | Low |

### 🎯 Use Cases Supported

1. **Text Search** → "What are the steps in NPC flow?"
   - Matches the text description
   - Returns the chunk with both text + diagram code

2. **Visual Rendering** → User clicks "Show Diagram"
   - Extract `mermaid_code` from metadata
   - Render using Mermaid.js in browser/Jupyter/Markdown

3. **LLM Context** → GPT/Claude processes the query
   - Gets text description for understanding
   - Can reference diagram steps in response
   - User can view actual diagram alongside

## 🚀 Implementation Files

### 1. **Chunking** (`utils/chunk_cube_docs_optimized.py`)
- Preserves original Mermaid code in metadata
- Generates searchable text description
- Stores both in chunk

### 2. **Query Engine** (`inference/query_cube_optimized.py`)
- Retrieves chunks with diagrams
- `print_results()` shows diagram preview
- Full diagram code accessible in results

### 3. **Diagram Renderer** (`inference/diagram_renderer.py`)
- Extract diagrams from results
- Render as HTML, Markdown, or Jupyter
- Create galleries of multiple diagrams

### 4. **Test Suite** (`tests/test_diagram_rendering.py`)
- Demonstrates all rendering methods
- Creates example outputs
- Validates diagram preservation

## 📖 Example Workflow

```python
# 1. Initialize query engine
from inference.query_cube_optimized import CUBEQueryEngine
from inference.diagram_renderer import MermaidRenderer

engine = CUBEQueryEngine()

# 2. Search for diagrams
results = engine.query("show me the admin API flow", top_k=5)

# 3. Check for diagrams
diagrams = MermaidRenderer.extract_diagrams(results)

if diagrams:
    print(f"Found {len(diagrams)} diagrams!")
    
    # 4a. Save as HTML for browser viewing
    MermaidRenderer.save_html(
        diagrams[0]['mermaid_code'],
        'admin_flow.html',
        title=diagrams[0]['page_name']
    )
    
    # 4b. Or create a gallery of all diagrams
    html = create_gallery(diagrams)
    
    # 4c. Or display in Jupyter
    MermaidRenderer.display_jupyter(diagrams[0]['mermaid_code'])
```

## 🎨 Rendering Options

### 1. **HTML Standalone**
```python
MermaidRenderer.save_html(mermaid_code, 'diagram.html')
# Opens in any browser - no dependencies
```

### 2. **Jupyter Notebook**
```python
MermaidRenderer.display_jupyter(mermaid_code)
# Renders inline in notebooks
```

### 3. **Markdown File**
```python
md = MermaidRenderer.render_markdown(mermaid_code)
# Compatible with GitHub, GitLab, VSCode
```

### 4. **Web Application**
```javascript
// React/Vue component
import mermaid from 'mermaid';

function DiagramViewer({ code }) {
  return <div className="mermaid">{code}</div>;
}
```

## 🧪 Testing

```bash
# Run the test suite
python tests/test_diagram_rendering.py

# This will:
# 1. Query for diagrams
# 2. Extract Mermaid code
# 3. Create HTML files
# 4. Generate a gallery
# 5. Open in browser
```

## 📈 Results

### Before (Text Only)
```
User: "Show me the NPC flow"
System: "Here's the process:
  - Branch Sale
  - NPC Reviewer 1
  - NPC Reviewer 2
  ..."
User: 😐 "Can I see a diagram?"
System: ❌ "No visual available"
```

### After (Text + Diagram)
```
User: "Show me the NPC flow"
System: "Here's the process [text] 📊 Diagram available"
User: 😊 *clicks diagram*
System: ✅ Renders beautiful flowchart
```

## 🎯 Key Takeaway

**You get the best of both worlds:**
- 🔍 **Searchable** via text embeddings
- 👁️ **Viewable** via original Mermaid code
- 🤖 **Understandable** by LLMs (text context)
- 👤 **User-friendly** (visual diagrams)

All with minimal storage overhead! 🚀
