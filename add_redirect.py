import os
import glob

base_dir = r"c:\laragon\www\absensilhk\app\Filament\Resources"

# Find all Create*.php and Edit*.php
files = glob.glob(os.path.join(base_dir, "**", "Pages", "Create*.php"), recursive=True) + \
        glob.glob(os.path.join(base_dir, "**", "Pages", "Edit*.php"), recursive=True)

method_str = """
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
"""

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if 'getRedirectUrl()' not in content:
        # Find the last closing brace
        last_brace_index = content.rfind('}')
        if last_brace_index != -1:
            new_content = content[:last_brace_index] + method_str + content[last_brace_index:]
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"Updated: {filepath}")
        else:
            print(f"Could not find closing brace in: {filepath}")
    else:
        print(f"Already contains getRedirectUrl: {filepath}")
