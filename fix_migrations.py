import os
import re

dir_path = 'd:/budget-management/database/migrations'

# Match /* /* /* ... */ */ */
pattern = re.compile(r'/\*\s*/\*\s*/\*.*?\*/\s*\*/\s*\*/')

for filename in os.listdir(dir_path):
    if filename.endswith('.php'):
        filepath = os.path.join(dir_path, filename)
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        new_content = pattern.sub('', content)
        
        if new_content != content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"Fixed {filename}")

print("Done")
