import concurrent.futures
import importlib.util
import json
import pathlib
import subprocess

HERE = pathlib.Path(__file__).resolve().parent
spec = importlib.util.spec_from_file_location('audit', HERE / 'run-audit.py')
a = importlib.util.module_from_spec(spec)
spec.loader.exec_module(a)

def lint(version):
    php = 'E:/OSPanel/modules/PHP-' + version + '/php.exe'
    rows = []
    for file in a.FILES:
        r = subprocess.run([php, '-l', str(file)], capture_output=True)
        rows.append(dict(file=str(file.relative_to(a.ROOT)).replace('\\', '/'),exit=r.returncode,stdout=r.stdout.decode('utf-8',errors='replace'),stderr=r.stderr.decode('utf-8',errors='replace')))
    (HERE / ('post-fix-php-' + version + '-lint.json')).write_text(json.dumps(rows,indent=2),encoding='utf-8')
    print(version,'checked',len(rows),'failures',sum(r['exit'] != 0 for r in rows),flush=True)

with concurrent.futures.ThreadPoolExecutor(max_workers=4) as pool:
    list(pool.map(lint,['7.4','8.0','8.1','8.3']))
for version,core in [('7.4','E:/.agents/docs/joomla/core/Joomla-core/4.x/4.4.14'),('8.3','E:/.agents/docs/joomla/core/Joomla-core/5.x/5.4.7'),('8.3','E:/OSPanel/home/joomla.local/public')]:
    a.run('installer-smoke-' + ('j6' if 'OSPanel' in core else 'j4' if '4.4.14' in core else 'j5'), ['E:/OSPanel/modules/PHP-' + version + '/php.exe', str(HERE / 'installer-smoke.php'),core])
before = json.loads((HERE / 'source-before.json').read_text(encoding='utf-8'))
after = a.snapshot()
changed = [f for f in after if after[f] != before[f]]
assert changed == ['lib_webtolk_wtcdek/script.php'],changed
(HERE / 'post-fix-source.json').write_text(json.dumps({'changed_product_php':changed,'hashes':after},indent=2),encoding='utf-8')
