import re
import glob
from pathlib import Path

translation_file = Path('assets/language-switcher.js').read_text(encoding='utf-8', errors='ignore')
keys = set(re.findall(r"'([^']+?)'\s*:\s*\{\s*en:\s*'[^']*'", translation_file))
keys |= set(re.findall(r'"([^"]+?)"\s*:\s*\{\s*en:\s*"[^"]*"', translation_file))

pattern = re.compile(r'>([^<]+?)<')
strings = {}
for path in sorted(glob.glob('*.php')):
    data = Path(path).read_text(encoding='utf-8', errors='ignore')
    for m in pattern.findall(data):
        s = m.strip()
        if not s:
            continue
        if any(x in s for x in ['<?php', '<?=', '}}', '}}', '<?', '?>', '@', 'function', 'return', 'var ', 'const ', 'class ']):
            continue
        if re.match(r'^[\s\W\d]+$', s):
            continue
        if len(s) < 2:
            continue
        if s in keys:
            continue
        if len(s) > 1 and not any(c.isalpha() for c in s):
            continue
        strings.setdefault(s, 0)
        strings[s] += 1

for s, count in sorted(strings.items(), key=lambda x: (-x[1], x[0])):
    print(repr(s), count)
print('--- missing count:', len(strings))
