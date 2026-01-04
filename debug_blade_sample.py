
import re
import json

# Sample Blade Template
sample_blade = """
@extends('layouts.app')

@section('title', 'User Dashboard')

@section('content')
<div class="dashboard-container">
    <h1>Welcome, {{ $user->name }}</h1>
    
    <!-- User Stats Section -->
    <div class="stats-grid" id="user-stats">
        <div class="stat-card">
            <h3>Balance</h3>
            <p>{{ $balance }}</p>
        </div>
        <div class="stat-card">
            <h3>Active Loans</h3>
            <p>{{ $loans->count() }}</p>
        </div>
    </div>

    @include('partials.recent-transactions')

    <form action="/update-profile" method="POST" class="mt-4">
        @csrf
        <label>Update Email</label>
        <input type="email" name="email" value="{{ $user->email }}">
        <button type="submit">Save</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    console.log('Dashboard loaded');
</script>
@endsection
"""

def chunk_blade_template(content, filename="sample.blade.php"):
    chunks = []
    metadata = {
        "extends": None,
        "sections": [], # Names of block sections
        "inline_sections": {}, # key: value pairs
        "includes": [],
        "has_form": False
    }
    
    # 1. Extract @extends
    extends_match = re.search(r"@extends\s*\(['\"](.*?)['\"]\)", content)
    if extends_match:
        metadata['extends'] = extends_match.group(1)
        
    # 2. Extract @includes
    metadata['includes'] = re.findall(r"@include\s*\(['\"](.*?)['\"]\)", content)
    
    # 3. Handle Inline Sections: @section('key', 'value')
    # Use simpler regex, assume no escaped quotes for prototype
    inline_pattern = r"@section\s*\(['\"](.*?)['\"]\s*,\s*['\"](.*?)['\"]\)"
    for key, val in re.findall(inline_pattern, content):
        metadata['inline_sections'][key] = val
        
    # 4. Handle Block Sections: @section('key') ... @endsection
    # We remove inline matches first to avoid confusion? No, just match block specifically.
    # Block sections usually don't have a second argument.
    block_pattern = r"@section\s*\(['\"]([^,]*?)['\"]\)(.*?)@endsection"
    
    # Note: Regex parsing of nested/complex code is brittle, but sufficient for this prototype verification
    block_sections = re.findall(block_pattern, content, re.DOTALL)
    
    if block_sections:
        for section_name, section_content in block_sections:
            section_content = section_content.strip()
            
            # Additional enrichment: Check for forms in this specific chunk
            chunk_metadata = metadata.copy()
            chunk_metadata['has_form'] = "<form" in section_content
            chunk_metadata['section_name'] = section_name
            chunk_metadata['source'] = filename
            chunk_metadata['type'] = "blade_section"
            
            # Remove the "sections" list from metadata to avoid clutter, 
            # or keep it to show structure? Let's just track this section.
            
            chunk = {
                "chunk_id": f"{filename}#section-{section_name}",
                "content": f"<!-- Section: {section_name} -->\n{section_content}",
                "metadata": chunk_metadata
            }
            chunks.append(chunk)
            metadata['sections'].append(section_name)
    
    # If no sections at all, treat as one chunk
    if not block_sections and not metadata['inline_sections']:
        chunks.append({
            "chunk_id": f"{filename}#full",
            "content": content.strip(),
            "metadata": {**metadata, "type": "blade_full", "source": filename}
        })
        
    return chunks

# Run and print
chunks = chunk_blade_template(sample_blade)
print(json.dumps(chunks, indent=2))
