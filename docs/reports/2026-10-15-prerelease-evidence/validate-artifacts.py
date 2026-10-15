import hashlib
import json
import pathlib
import re

HERE = pathlib.Path(__file__).resolve().parent
ROOT = HERE.parents[2]
tasks_root = ROOT / 'docs/tasks/2026-10-15-prerelease'
tasks = json.loads((tasks_root / 'tasks.json').read_text(encoding='utf-8'))
schema = json.loads((ROOT / '.webtolk/config/task.schema.json').read_text(encoding='utf-8'))
for task in tasks:
    assert set(task) == set(schema['properties']), task['id']
    assert set(schema['required']).issubset(task), task['id']
    assert task['mode'] in schema['properties']['mode']['enum'], task['id']
    for artifact in task['artifacts']:
        assert (ROOT / artifact).is_file(), artifact
assert len(tasks) == 13
assert len({t['id'] for t in tasks}) == 13
assert sum(t['stage'] == 'verified-source-fix' for t in tasks) == 1
files = list(tasks_root.glob('*.md')) + list((ROOT / 'docs/reports').glob('2026-10-15-*.md'))
checked = 0
for file in files:
    for label, href in re.findall(r'\[([^\]]+)\]\(([^)]+)\)',file.read_text(encoding='utf-8')):
        if '://' in href or href.startswith('#'):
            continue
        href = re.sub(r':\d+$', '', href.split('#')[0])
        assert (file.parent / href).resolve().is_file(), (str(file), href)
        checked += 1
for file in HERE.glob('*.json'):
    json.loads(file.read_text(encoding='utf-8-sig'))
result = {'tasks':len(tasks),'source_fix_done':1,'open_tasks':12,'markdown_files_checked':len(files),'local_links_checked':checked,'task_schema_shape':'passed','json_parse':'passed'}
(HERE / 'artifact-validation.json').write_text(json.dumps(result,indent=2),encoding='utf-8')
print(json.dumps(result))
