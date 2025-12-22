import os
import re

# Ignore these function prefixes
IGNORE_PREFIXES = ['Carbon::', 'Arr::', 'Session::', 'Cache::', 'DB::table', 'DB::raw', 'File::', 'guzzleClient']

def should_ignore(call_line):
    return any(prefix in call_line for prefix in IGNORE_PREFIXES)

def extract_functions_with_calls(code):
    results = []
    
    # Match function definitions and their bodies
    pattern = r'(public|protected|private)?\s*(static)?\s*function\s+(\w+)\s*\([^)]*\)\s*\{([\s\S]*?)\}'
    matches = re.finditer(pattern, code)

    for match in matches:
        full_signature = match.group(0).split('{')[0].strip()
        body = match.group(4)

        # Get lines with function calls that are not in ignore list
        call_lines = []
        for line in body.splitlines():
            line = line.strip()
            if 'Carbon::' in line and 'insertInto' in line:
                    call_lines.append(line)
            if re.search(r'[A-Za-z_\\]+(?:::|->)[A-Za-z_]+\s*\(', line) and not should_ignore(line):
                call_lines.append(line)
        
        if call_lines:
            results.append((full_signature, call_lines))
    
    return results

def parse_php_files(directory):
    for root, _, files in os.walk(directory):
        for file in files:
            if file.endswith('.php'):
                filepath = os.path.join(root, file)
                with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                    code = f.read()
                    function_blocks = extract_functions_with_calls(code)
                    if function_blocks:
                        print(file)
                        print("=" * 25)
                        for func_sig, calls in function_blocks:
                            print(func_sig)
                            for call in calls:
                                print(f"              {call}")
                        print()

# === USAGE ===
if __name__ == '__main__':
    directory_path = './'  
    parse_php_files(directory_path)
