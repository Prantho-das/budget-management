
import os
import json
import re
import sys

# Ensure UTF-8 output
if sys.version_info >= (3, 7):
    sys.stdout.reconfigure(encoding='utf-8')

def find_missing():
    lang_dir = r'd:\budget-management\lang'
    en_path = os.path.join(lang_dir, 'en.json')
    bn_path = os.path.join(lang_dir, 'bn.json')
    
    en = json.load(open(en_path, encoding='utf-8')) if os.path.exists(en_path) else {}
    bn = json.load(open(bn_path, encoding='utf-8')) if os.path.exists(bn_path) else {}
    
    code_strings = set()
    root_dirs = [r'd:\budget-management\resources\views', r'd:\budget-management\app']
    
    for r_dir in root_dirs:
        for root, dirs, files in os.walk(r_dir):
            for file in files:
                if file.endswith('.php'):
                    try:
                        with open(os.path.join(root, file), 'r', encoding='utf-8') as f:
                            content = f.read()
                            # Find all __(...) and @lang(...)
                            matches = re.finditer(r'(__|@lang)\s*\(\s*([\'"])(.*?)\2\s*\)', content)
                            for m in matches:
                                s = m.group(3)
                                if s:
                                    code_strings.add(s)
                    except Exception as e:
                        print(f"Error reading {file}: {e}")

    print(f"Total unique strings found in code: {len(code_strings)}")
    
    print("\n--- Strings in Code NOT in bn.json ---")
    for s in sorted(code_strings):
        if s not in bn:
            status = "(In en.json)" if s in en else "(New/Missing in en.json)"
            print(f"{status}: {s}")
            
    print("\n--- Keys in en.json NOT in bn.json ---")
    for k in sorted(en.keys()):
        if k not in bn:
            print(f"Missing: {k}")

if __name__ == '__main__':
    find_missing()
