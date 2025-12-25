"""
Test script to demonstrate Mermaid diagram preservation and rendering
Run this after embedding the chunks
"""

import sys
sys.path.append('..')

from inference.query_cube_optimized import CUBEQueryEngine
from inference.diagram_renderer import MermaidRenderer


def test_diagram_retrieval():
    """Test that diagrams are preserved and retrievable"""
    print("="*80)
    print("TEST: Mermaid Diagram Retrieval and Rendering")
    print("="*80)
    
    # Initialize engine
    engine = CUBEQueryEngine()
    
    # Test queries that should return diagrams
    test_queries = [
        "Show me the NPC flow diagram",
        "What is the CUBE architecture?",
        "Admin API flow",
        "Application status flow"
    ]
    
    for query in test_queries:
        print(f"\n{'─'*80}")
        print(f"Query: {query}")
        print(f"{'─'*80}")
        
        results = engine.query(query, top_k=3, rerank=True)
        
        # Extract diagrams
        diagrams = MermaidRenderer.extract_diagrams(results)
        
        if diagrams:
            print(f"\n✅ Found {len(diagrams)} diagram(s)!")
            
            for i, diagram in enumerate(diagrams, 1):
                print(f"\n  📊 Diagram {i}: {diagram['page_name']}")
                print(f"     Path: {diagram['hierarchy_path']}")
                print(f"\n     Mermaid Code Preview:")
                code_preview = diagram['mermaid_code'][:150]
                print(f"     {code_preview}...")
                
                # Save as HTML
                filename = f"test_diagram_{i}.html"
                MermaidRenderer.save_html(
                    diagram['mermaid_code'],
                    filename,
                    title=f"CUBE - {diagram['page_name']}"
                )
        else:
            print("  ⚠️  No diagrams found for this query")
    
    print(f"\n{'='*80}")
    print("✓ Test Complete!")
    print("  Check the generated HTML files to view the rendered diagrams")
    print("="*80)


def demonstrate_usage():
    """Show different ways to work with diagrams"""
    print("\n" + "="*80)
    print("DEMONSTRATION: Working with Mermaid Diagrams")
    print("="*80)
    
    engine = CUBEQueryEngine()
    
    # Example 1: Get diagram and render in multiple formats
    print("\n1. Retrieve and convert diagram to different formats:")
    print("─"*80)
    
    results = engine.query("CUBE flow diagram", top_k=5)
    diagrams = MermaidRenderer.extract_diagrams(results)
    
    if diagrams:
        diagram = diagrams[0]
        
        print(f"\n📊 Working with: {diagram['page_name']}")
        
        # Save as HTML
        MermaidRenderer.save_html(
            diagram['mermaid_code'],
            'cube_flow.html',
            title=diagram['page_name']
        )
        
        # Generate Markdown
        markdown = MermaidRenderer.render_markdown(
            diagram['mermaid_code'],
            title=diagram['page_name']
        )
        
        with open('cube_flow.md', 'w') as f:
            f.write(markdown)
        
        print("  ✓ Saved as HTML: cube_flow.html")
        print("  ✓ Saved as Markdown: cube_flow.md")
    
    # Example 2: Create gallery of all diagrams
    print("\n2. Create gallery of all diagrams:")
    print("─"*80)
    
    # Get results that might contain diagrams
    all_results = engine.query("flow diagram process architecture", top_k=30)
    all_diagrams = MermaidRenderer.extract_diagrams(all_results)
    
    print(f"  Found {len(all_diagrams)} total diagrams")
    
    if all_diagrams:
        # Create HTML gallery
        html_parts = ['''
<!DOCTYPE html>
<html>
<head>
    <title>CUBE Diagrams Gallery</title>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <script>
        mermaid.initialize({ startOnLoad: true, theme: 'default' });
    </script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f7fa;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            border-bottom: 3px solid #3498db;
            padding-bottom: 20px;
        }
        .diagram-card {
            background: white;
            margin: 30px 0;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .diagram-card h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        .diagram-card .path {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 20px;
        }
        .mermaid {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>📊 CUBE Documentation - Diagram Gallery</h1>
    <p style="text-align: center; color: #7f8c8d;">
        All process flows and architecture diagrams from CUBE documentation
    </p>
''']
        
        for diagram in all_diagrams:
            html_parts.append(f'''
    <div class="diagram-card">
        <h2>{diagram['page_name']}</h2>
        <div class="path">{diagram['hierarchy_path']}</div>
        <div class="mermaid">
{diagram['mermaid_code']}
        </div>
    </div>
''')
        
        html_parts.append('</body></html>')
        
        with open('cube_diagram_gallery.html', 'w', encoding='utf-8') as f:
            f.write(''.join(html_parts))
        
        print("  ✓ Created: cube_diagram_gallery.html")
        print("    Open in your browser to view all diagrams")
    
    print("\n" + "="*80)
    print("✓ Demonstration Complete!")
    print("\nGenerated files:")
    print("  - test_diagram_*.html (individual diagrams)")
    print("  - cube_flow.html (example single diagram)")
    print("  - cube_flow.md (markdown format)")
    print("  - cube_diagram_gallery.html (all diagrams)")
    print("="*80)


if __name__ == "__main__":
    print("\n🚀 Starting Mermaid Diagram Tests...\n")
    
    try:
        test_diagram_retrieval()
        demonstrate_usage()
        
        print("\n✅ All tests passed!")
        print("\n💡 Tip: Open the HTML files in your browser to see the rendered diagrams")
        
    except Exception as e:
        print(f"\n❌ Error: {e}")
        print("\nMake sure you've run the embedding script first:")
        print("  python embedding_vectordb/embed_cube_optimized_chunks.py")
