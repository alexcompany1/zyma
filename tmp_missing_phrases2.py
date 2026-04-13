import re, glob
from pathlib import Path

translation_file = Path('assets/language-switcher.js').read_text(encoding='utf-8', errors='ignore')
keys = set(re.findall(r"'([^']+?)'\s*:\s*\{\s*en:\s*'[^']*'", translation_file))
keys |= set(re.findall(r'"([^"]+?)"\s*:\s*\{\s*en:\s*"[^"]*"', translation_file))

php_block = re.compile(r'<\?(?:php|=)[\s\S]*?\?>')
script_block = re.compile(r'<script[\s\S]*?>[\s\S]*?</script>', re.IGNORECASE)
style_block = re.compile(r'<style[\s\S]*?>[\s\S]*?</style>', re.IGNORECASE)
attr_pattern = re.compile(r'\b(?:placeholder|title|aria-label|alt)="([^"]+)"')
text_pattern = re.compile(r'>([^<]+?)<')

strings = {}
for path in sorted(glob.glob('*.php')):
    data = Path(path).read_text(encoding='utf-8', errors='ignore')
    data = php_block.sub(' ', data)
    data = script_block.sub(' ', data)
    data = style_block.sub(' ', data)
    for match in attr_pattern.findall(data):
        s = match.strip()
        if len(s) > 1 and any(c.isalpha() for c in s) and s not in keys:
            strings.setdefault(s, 0)
            strings[s] += 1
    for match in text_pattern.findall(data):
        s = match.strip()
        if not s or s in keys:
            continue
        if re.search(r'<\?|\?>|\{\{|\}\}|\$\w+|href=|src=|class=|id=|data-|@', s):
            continue
        if len(s) < 2 or not any(c.isalpha() for c in s):
            continue
        if s.startswith('http') or s.endswith('>') or s.startswith('<'):
            continue
        strings.setdefault(s, 0)
        strings[s] += 1

for s, count in sorted(strings.items(), key=lambda x: (-x[1], x[0])):
    print(repr(s), count)
print('--- missing count:', len(strings))
