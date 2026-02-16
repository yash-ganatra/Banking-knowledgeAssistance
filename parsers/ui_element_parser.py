"""
UI Element Parser

Parses Blade template files to extract UI elements:
- Forms (<form action="..." method="...">)
- Inputs (<input>, <select>, <textarea>)
- Buttons (<button>, <input type="submit">)
- Links with actions (<a href="...">)

Creates UIElement nodes and identifies:
- VIEW_CONTAINS_ELEMENT relationships (BladeView -> UIElement)
- UI_POSTS_TO_ACTION relationships (UIElement -> Action via form action)
"""

import re
import logging
from pathlib import Path
from typing import List, Dict, Any, Optional, Tuple
from html.parser import HTMLParser

logger = logging.getLogger(__name__)


class BladeUIExtractor(HTMLParser):
    """HTML Parser to extract UI elements from Blade templates"""
    
    def __init__(self, view_name: str):
        super().__init__()
        self.view_name = view_name
        self.elements = []
        self.current_form = None
        self.form_counter = 0
        self.element_counter = 0
        
    def handle_starttag(self, tag: str, attrs: List[Tuple[str, str]]):
        attrs_dict = dict(attrs)
        
        if tag == "form":
            self._extract_form(attrs_dict)
        elif tag == "input":
            self._extract_input(attrs_dict)
        elif tag == "select":
            self._extract_select(attrs_dict)
        elif tag == "textarea":
            self._extract_textarea(attrs_dict)
        elif tag == "button":
            self._extract_button(attrs_dict)
        elif tag == "a" and self._is_action_link(attrs_dict):
            self._extract_action_link(attrs_dict)
    
    def handle_endtag(self, tag: str):
        if tag == "form":
            self.current_form = None
    
    def _extract_form(self, attrs: Dict[str, str]):
        self.form_counter += 1
        action = attrs.get("action", "")
        method = attrs.get("method", "GET").upper()
        form_id = attrs.get("id", f"form_{self.form_counter}")
        form_name = attrs.get("name", form_id)
        form_class = attrs.get("class", "")
        
        # Extract route name from {{ route('name') }} or action URL
        target_route = self._parse_route_or_url(action)
        
        element = {
            "id": f"{self.view_name}:form:{form_id}",
            "type": "form",
            "name": form_name,
            "html_id": form_id,
            "html_class": form_class,
            "method": method,
            "action": action,
            "target_route": target_route,
            "view_name": self.view_name
        }
        
        self.current_form = element
        self.elements.append(element)
    
    def _extract_input(self, attrs: Dict[str, str]):
        input_type = attrs.get("type", "text")
        
        # Skip hidden and submit (submit handled as button)
        if input_type in ["hidden"]:
            return
            
        if input_type == "submit":
            self._extract_submit_button(attrs)
            return
        
        self.element_counter += 1
        input_id = attrs.get("id", attrs.get("name", f"input_{self.element_counter}"))
        input_name = attrs.get("name", input_id)
        
        element = {
            "id": f"{self.view_name}:input:{input_id}",
            "type": "input",
            "input_type": input_type,
            "name": input_name,
            "html_id": input_id,
            "html_class": attrs.get("class", ""),
            "placeholder": attrs.get("placeholder", ""),
            "required": "required" in attrs,
            "validation": self._extract_validation(attrs),
            "form_id": self.current_form["id"] if self.current_form else None,
            "view_name": self.view_name
        }
        self.elements.append(element)
    
    def _extract_select(self, attrs: Dict[str, str]):
        self.element_counter += 1
        select_id = attrs.get("id", attrs.get("name", f"select_{self.element_counter}"))
        select_name = attrs.get("name", select_id)
        
        element = {
            "id": f"{self.view_name}:select:{select_id}",
            "type": "select",
            "name": select_name,
            "html_id": select_id,
            "html_class": attrs.get("class", ""),
            "required": "required" in attrs,
            "form_id": self.current_form["id"] if self.current_form else None,
            "view_name": self.view_name
        }
        self.elements.append(element)
    
    def _extract_textarea(self, attrs: Dict[str, str]):
        self.element_counter += 1
        ta_id = attrs.get("id", attrs.get("name", f"textarea_{self.element_counter}"))
        ta_name = attrs.get("name", ta_id)
        
        element = {
            "id": f"{self.view_name}:textarea:{ta_id}",
            "type": "textarea",
            "name": ta_name,
            "html_id": ta_id,
            "html_class": attrs.get("class", ""),
            "required": "required" in attrs,
            "form_id": self.current_form["id"] if self.current_form else None,
            "view_name": self.view_name
        }
        self.elements.append(element)
    
    def _extract_button(self, attrs: Dict[str, str]):
        self.element_counter += 1
        btn_type = attrs.get("type", "button")
        btn_id = attrs.get("id", f"button_{self.element_counter}")
        btn_name = attrs.get("name", btn_id)
        btn_class = attrs.get("class", "")
        
        # Check for onclick handlers
        onclick = attrs.get("onclick", "")
        
        element = {
            "id": f"{self.view_name}:button:{btn_id}",
            "type": "button",
            "button_type": btn_type,
            "name": btn_name,
            "html_id": btn_id,
            "html_class": btn_class,
            "onclick": onclick,
            "is_submit": btn_type == "submit",
            "form_id": self.current_form["id"] if self.current_form else None,
            "view_name": self.view_name
        }
        self.elements.append(element)
    
    def _extract_submit_button(self, attrs: Dict[str, str]):
        self.element_counter += 1
        btn_id = attrs.get("id", f"submit_{self.element_counter}")
        btn_name = attrs.get("name", btn_id)
        btn_value = attrs.get("value", "Submit")
        
        element = {
            "id": f"{self.view_name}:submit:{btn_id}",
            "type": "button",
            "button_type": "submit",
            "name": btn_name,
            "html_id": btn_id,
            "html_class": attrs.get("class", ""),
            "value": btn_value,
            "is_submit": True,
            "form_id": self.current_form["id"] if self.current_form else None,
            "view_name": self.view_name
        }
        self.elements.append(element)
    
    def _extract_action_link(self, attrs: Dict[str, str]):
        href = attrs.get("href", "")
        if not href or href == "#" or href.startswith("javascript:"):
            return
            
        self.element_counter += 1
        link_id = attrs.get("id", f"link_{self.element_counter}")
        link_class = attrs.get("class", "")
        
        target_route = self._parse_route_or_url(href)
        
        element = {
            "id": f"{self.view_name}:link:{link_id}",
            "type": "link",
            "name": link_id,
            "html_id": link_id,
            "html_class": link_class,
            "href": href,
            "target_route": target_route,
            "view_name": self.view_name
        }
        self.elements.append(element)
    
    def _is_action_link(self, attrs: Dict[str, str]) -> bool:
        """Check if link triggers a backend action"""
        href = attrs.get("href", "")
        link_class = attrs.get("class", "")
        
        # Links with route() calls
        if "route(" in href:
            return True
        # Links with action-like classes
        action_classes = ["btn", "action", "submit", "delete", "edit", "update"]
        if any(cls in link_class.lower() for cls in action_classes):
            return True
        # Links to URLs (not anchors or javascript)
        if href.startswith("/") and not href.startswith("//"):
            return True
        return False
    
    def _parse_route_or_url(self, action: str) -> Optional[str]:
        """Extract route name or URL from action attribute"""
        if not action:
            return None
            
        # Match {{ route('name') }} or {{ route('name', ...) }}
        route_match = re.search(r"route\s*\(\s*['\"]([^'\"]+)['\"]", action)
        if route_match:
            return route_match.group(1)
        
        # Match {{ url('path') }}
        url_match = re.search(r"url\s*\(\s*['\"]([^'\"]+)['\"]", action)
        if url_match:
            return url_match.group(1)
        
        # Direct URL path
        if action.startswith("/"):
            return action
        
        return action
    
    def _extract_validation(self, attrs: Dict[str, str]) -> str:
        """Extract validation rules from input attributes"""
        validations = []
        
        if "required" in attrs:
            validations.append("required")
        if attrs.get("type") == "email":
            validations.append("email")
        if attrs.get("pattern"):
            validations.append(f"pattern:{attrs['pattern']}")
        if attrs.get("minlength"):
            validations.append(f"minlength:{attrs['minlength']}")
        if attrs.get("maxlength"):
            validations.append(f"maxlength:{attrs['maxlength']}")
        if attrs.get("min"):
            validations.append(f"min:{attrs['min']}")
        if attrs.get("max"):
            validations.append(f"max:{attrs['max']}")
        
        return "|".join(validations) if validations else ""


class UIElementParser:
    """
    Parses Blade templates to extract UI elements for graph ingestion.
    """
    
    def __init__(self, project_path: str):
        self.project_path = Path(project_path)
        self.views_path = self.project_path / "resources/views"
    
    def parse(self) -> Dict[str, Any]:
        """
        Parse all blade files and return UI elements and relationships.
        
        Returns:
            Dict with:
            - elements: List of UIElement node data
            - view_contains_element: List of (view_id, element_id) tuples
            - ui_posts_to_action: List of (element_id, action_identifier) tuples
        """
        elements = []
        view_contains_element = []
        ui_posts_to_action = []
        
        if not self.views_path.exists():
            logger.warning(f"Views directory not found: {self.views_path}")
            return {"elements": [], "view_contains_element": [], "ui_posts_to_action": []}
        
        files = list(self.views_path.rglob("*.blade.php"))
        logger.info(f"Parsing {len(files)} blade files for UI elements")
        
        for file_path in files:
            try:
                view_data = self._parse_file(file_path)
                if view_data:
                    elements.extend(view_data["elements"])
                    view_contains_element.extend(view_data["view_contains_element"])
                    ui_posts_to_action.extend(view_data["ui_posts_to_action"])
            except Exception as e:
                logger.warning(f"Error parsing {file_path}: {e}")
        
        logger.info(f"Extracted {len(elements)} UI elements, {len(ui_posts_to_action)} form→action relationships")
        
        return {
            "elements": elements,
            "view_contains_element": view_contains_element,
            "ui_posts_to_action": ui_posts_to_action
        }
    
    def _parse_file(self, file_path: Path) -> Optional[Dict[str, Any]]:
        """Parse a single blade file for UI elements"""
        with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
            content = f.read()
        
        # Derive view name
        relative_path = file_path.relative_to(self.views_path)
        view_name = str(relative_path).replace(".blade.php", "").replace("/", ".")
        
        # Pre-process: extract Blade/Laravel-specific constructs
        # Convert {!! Form::select(...) !!} to standard HTML for parsing
        content = self._preprocess_blade(content, view_name)
        
        # Parse HTML elements
        extractor = BladeUIExtractor(view_name)
        try:
            extractor.feed(content)
        except Exception as e:
            logger.debug(f"HTML parse warning for {view_name}: {e}")
        
        elements = extractor.elements
        
        # Also extract Laravel Form facade elements
        facade_elements = self._extract_form_facade_elements(content, view_name)
        elements.extend(facade_elements)
        
        # Build relationships
        view_contains_element = [(view_name, el["id"]) for el in elements]
        
        # Extract form→action relationships
        ui_posts_to_action = []
        for el in elements:
            if el["type"] == "form" and el.get("target_route"):
                ui_posts_to_action.append((el["id"], el["target_route"]))
            elif el["type"] == "link" and el.get("target_route"):
                ui_posts_to_action.append((el["id"], el["target_route"]))
        
        return {
            "elements": elements,
            "view_contains_element": view_contains_element,
            "ui_posts_to_action": ui_posts_to_action
        }
    
    def _preprocess_blade(self, content: str, view_name: str) -> str:
        """Convert Blade syntax to parseable HTML where possible"""
        # Remove Blade comments
        content = re.sub(r'\{\{--.*?--\}\}', '', content, flags=re.DOTALL)
        
        # Keep {{ route('name') }} as-is for extraction
        # But clean up other Blade directives that break HTML parsing
        
        # Remove @php ... @endphp blocks
        content = re.sub(r'@php.*?@endphp', '', content, flags=re.DOTALL)
        
        # Remove @if, @else, @foreach etc (but keep content)
        content = re.sub(r'@(if|else|elseif|endif|foreach|endforeach|for|endfor|while|endwhile|unless|endunless|isset|endisset|empty|endempty|switch|case|break|default|endswitch)\s*(\([^)]*\))?', '', content)
        
        # Remove @section, @endsection, @extends, @include, @yield etc
        content = re.sub(r'@(section|endsection|extends|include|yield|push|endpush|stack|component|endcomponent|slot|endslot)\s*(\([^)]*\))?', '', content)
        
        # Remove @can, @cannot, @auth, @guest etc
        content = re.sub(r'@(can|cannot|endcan|endcannot|auth|endauth|guest|endguest)\s*(\([^)]*\))?', '', content)
        
        return content
    
    def _extract_form_facade_elements(self, content: str, view_name: str) -> List[Dict[str, Any]]:
        """Extract elements created via Laravel Form facade {!! Form::... !!}"""
        elements = []
        
        # Match Form::select('name', $options, $selected, ['id' => 'xxx', ...])
        select_pattern = re.compile(
            r"\{!!\s*Form::select\s*\(\s*['\"]([^'\"]+)['\"].*?(?:\[\s*['\"]id['\"]\s*=>\s*['\"]([^'\"]+)['\"])?"
        )
        
        # Match Form::text, Form::email, Form::password, etc
        input_pattern = re.compile(
            r"\{!!\s*Form::(text|email|password|number|tel|date|time)\s*\(\s*['\"]([^'\"]+)['\"].*?(?:\[\s*['\"]id['\"]\s*=>\s*['\"]([^'\"]+)['\"])?"
        )
        
        # Match Form::open(['route' => 'name', ...])
        form_open_pattern = re.compile(
            r"\{!!\s*Form::open\s*\(\s*\[.*?['\"](?:route|url|action)['\"]\s*=>\s*['\"]([^'\"]+)['\"].*?\]\s*\)\s*!!\}"
        )
        
        counter = 0
        
        for match in select_pattern.finditer(content):
            counter += 1
            name = match.group(1)
            html_id = match.group(2) or name
            elements.append({
                "id": f"{view_name}:select:{html_id}",
                "type": "select",
                "name": name,
                "html_id": html_id,
                "html_class": "",
                "view_name": view_name
            })
        
        for match in input_pattern.finditer(content):
            counter += 1
            input_type = match.group(1)
            name = match.group(2)
            html_id = match.group(3) or name
            elements.append({
                "id": f"{view_name}:input:{html_id}",
                "type": "input",
                "input_type": input_type,
                "name": name,
                "html_id": html_id,
                "html_class": "",
                "view_name": view_name
            })
        
        return elements


if __name__ == "__main__":
    import sys
    import json
    logging.basicConfig(level=logging.INFO)
    
    path = "."
    if len(sys.argv) > 1:
        path = sys.argv[1]
    
    parser = UIElementParser(path)
    result = parser.parse()
    
    print(f"Elements: {len(result['elements'])}")
    print(f"View→Element: {len(result['view_contains_element'])}")
    print(f"UI→Action: {len(result['ui_posts_to_action'])}")
    
    # Print sample
    if result['elements']:
        print("\nSample elements:")
        for el in result['elements'][:5]:
            print(f"  - {el['type']}: {el['name']} ({el['id']})")
    
    if result['ui_posts_to_action']:
        print("\nSample form→action mappings:")
        for src, target in result['ui_posts_to_action'][:5]:
            print(f"  - {src} → {target}")
