"""
Utility functions to render Mermaid diagrams from query results
Supports multiple rendering backends: HTML, Markdown, IPython
"""

from typing import Dict, List


class MermaidRenderer:
    """Render Mermaid diagrams from CUBE query results"""
    
    @staticmethod
    def extract_diagrams(results: List[Dict]) -> List[Dict]:
        """
        Extract all Mermaid diagrams from query results
        
        Returns:
            List of dicts with {page_name, mermaid_code, hierarchy}
        """
        diagrams = []
        
        for result in results:
            metadata = result['metadata']
            
            # Check if this result has a diagram
            if metadata.get('is_mermaid') == 'True' or metadata.get('mermaid_code'):
                mermaid_code = metadata.get('mermaid_code', '')
                
                if mermaid_code:
                    diagrams.append({
                        'page_name': metadata.get('page_name', 'Unknown'),
                        'hierarchy_path': metadata.get('hierarchy_path', ''),
                        'mermaid_code': mermaid_code,
                        'chunk_id': result.get('id', '')
                    })
        
        return diagrams
    
    @staticmethod
    def render_html(mermaid_code: str, title: str = "Diagram") -> str:
        """
        Generate HTML with embedded Mermaid diagram
        Can be used in web apps, Jupyter, or saved to file
        """
        html = f"""
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <script>
        mermaid.initialize({{ startOnLoad: true, theme: 'default' }});
    </script>
    <style>
        body {{
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }}
        h1 {{
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }}
        .mermaid {{
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }}
    </style>
</head>
<body>
    <h1>{title}</h1>
    <div class="mermaid">
{mermaid_code}
    </div>
</body>
</html>
"""
        return html
    
    @staticmethod
    def render_markdown(mermaid_code: str, title: str = "Diagram") -> str:
        """
        Generate Markdown with Mermaid diagram
        Compatible with GitHub, GitLab, and most modern markdown viewers
        """
        return f"""# {title}

```mermaid
{mermaid_code}
```
"""
    
    @staticmethod
    def save_html(mermaid_code: str, filename: str, title: str = "CUBE Diagram"):
        """Save diagram as standalone HTML file"""
        html = MermaidRenderer.render_html(mermaid_code, title)
        
        with open(filename, 'w', encoding='utf-8') as f:
            f.write(html)
        
        print(f"✓ Saved diagram to: {filename}")
        print(f"  Open in browser to view the rendered diagram")
    
    @staticmethod
    def display_jupyter(mermaid_code: str, title: str = "Diagram"):
        """Display Mermaid diagram in Jupyter notebook"""
        try:
            from IPython.display import HTML, display
            
            html = MermaidRenderer.render_html(mermaid_code, title)
            display(HTML(html))
        except ImportError:
            print("⚠️  IPython not available. Install with: pip install ipython")
            print("\nAlternatively, save as HTML:")
            print(f"  MermaidRenderer.save_html(mermaid_code, 'diagram.html')")


def demo_usage():
    """
    Demonstrate how to use the diagram renderer
    """
    print("="*80)
    print("MERMAID DIAGRAM RENDERER - USAGE EXAMPLES")
    print("="*80)
    
    # Example 1: Extract from query results
    print("\n1. Extract diagrams from query results:")
    print("""
from query_cube_optimized import CUBEQueryEngine
from diagram_renderer import MermaidRenderer

engine = CUBEQueryEngine()
results = engine.query("show me the NPC flow", top_k=5)

# Extract all diagrams
diagrams = MermaidRenderer.extract_diagrams(results)

for diagram in diagrams:
    print(f"Diagram: {diagram['page_name']}")
    print(diagram['mermaid_code'])
""")
    
    # Example 2: Save as HTML
    print("\n2. Save diagram as HTML file:")
    print("""
# Get diagram from results
if results[0]['metadata'].get('mermaid_code'):
    mermaid_code = results[0]['metadata']['mermaid_code']
    page_name = results[0]['metadata']['page_name']
    
    # Save as HTML
    MermaidRenderer.save_html(
        mermaid_code, 
        'npc_flow.html',
        title=f"CUBE - {page_name}"
    )
    
    # Opens in default browser
    import webbrowser
    webbrowser.open('npc_flow.html')
""")
    
    # Example 3: Display in Jupyter
    print("\n3. Display in Jupyter Notebook:")
    print("""
# In Jupyter notebook
from diagram_renderer import MermaidRenderer

results = engine.query("admin flow diagram", top_k=3)
diagrams = MermaidRenderer.extract_diagrams(results)

for diagram in diagrams:
    MermaidRenderer.display_jupyter(
        diagram['mermaid_code'],
        title=diagram['page_name']
    )
""")
    
    # Example 4: Generate Markdown
    print("\n4. Generate Markdown documentation:")
    print("""
# Create documentation with diagrams
results = engine.query("all flowcharts", top_k=10)
diagrams = MermaidRenderer.extract_diagrams(results)

with open('cube_diagrams.md', 'w') as f:
    f.write("# CUBE Process Diagrams\\n\\n")
    
    for diagram in diagrams:
        md = MermaidRenderer.render_markdown(
            diagram['mermaid_code'],
            title=diagram['page_name']
        )
        f.write(md + "\\n---\\n\\n")
""")
    
    # Example 5: Interactive web viewer
    print("\n5. Create interactive viewer for all diagrams:")
    print("""
def create_diagram_gallery(results):
    diagrams = MermaidRenderer.extract_diagrams(results)
    
    html_parts = ['''
    <!DOCTYPE html>
    <html>
    <head>
        <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
        <script>mermaid.initialize({ startOnLoad: true });</script>
        <style>
            body { font-family: Arial; max-width: 1400px; margin: 20px auto; }
            .diagram-card { margin: 30px 0; padding: 20px; border: 1px solid #ddd; }
            h2 { color: #2c3e50; }
            .mermaid { background: #f8f9fa; padding: 20px; border-radius: 8px; }
        </style>
    </head>
    <body>
        <h1>CUBE Documentation - All Diagrams</h1>
    ''']
    
    for diagram in diagrams:
        html_parts.append(f'''
        <div class="diagram-card">
            <h2>{diagram['page_name']}</h2>
            <p><small>{diagram['hierarchy_path']}</small></p>
            <div class="mermaid">{diagram['mermaid_code']}</div>
        </div>
        ''')
    
    html_parts.append('</body></html>')
    
    with open('cube_diagram_gallery.html', 'w') as f:
        f.write(''.join(html_parts))
    
    print("✓ Created diagram gallery: cube_diagram_gallery.html")

# Use it
engine = CUBEQueryEngine()
all_results = engine.query("diagram flow process", top_k=50)
create_diagram_gallery(all_results)
""")
    
    print("\n" + "="*80)


if __name__ == "__main__":
    demo_usage()
