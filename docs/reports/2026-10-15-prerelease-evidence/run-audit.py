import concurrent.futures
import hashlib
import json
import pathlib
import subprocess
import time

ROOT = pathlib.Path(__file__).resolve().parents[3]
OUT = pathlib.Path(__file__).resolve().parent
PHP = 'E:/OSPanel/modules/PHP-8.3/php.exe'
QA = pathlib.Path('E:/.agents/tools/php-qa')
SCOPE = ['script.php', 'lib_webtolk_wtcdek', 'plg_system_wtcdek', 'plg_task_updatewtcdekdata']
FILES = sorted([ROOT / 'script.php'] + [p for d in SCOPE[1:] for p in (ROOT / d).rglob('*.php')])

def snapshot():
    return {str(p.relative_to(ROOT)).replace('\\', '/'): hashlib.sha256(p.read_bytes()).hexdigest()
            for p in FILES}

def run(name, args, timeout=240):
    start = time.time()
    try:
        result = subprocess.run(args, cwd=ROOT, capture_output=True, timeout=timeout)
        code, stdout, stderr = result.returncode, result.stdout, result.stderr
    except subprocess.TimeoutExpired as e:
        code, stdout, stderr = -999, e.stdout or b'', e.stderr or b''
    (OUT / (name + '.stdout')).write_bytes(stdout)
    (OUT / (name + '.stderr')).write_bytes(stderr)
    meta = dict(command=args, exit=code, seconds=round(time.time() - start, 2))
    (OUT / (name + '.run.json')).write_text(json.dumps(meta, indent=2), encoding='utf-8')
    print(name, code, meta['seconds'], flush=True)
    return meta

if __name__ == '__main__':
    OUT.mkdir(parents=True, exist_ok=True)
    (OUT / 'source-before.json').write_text(json.dumps(snapshot(), indent=2), encoding='utf-8')
    tasks = []
    with concurrent.futures.ThreadPoolExecutor(max_workers=3) as pool:
        tasks.append(pool.submit(run, 'phpcs', [PHP, str(QA / 'vendor/bin/phpcs'),
            '--standard=' + str(QA / 'config/ruleset.xml'), '--extensions=php', '--report=json', *SCOPE]))
        tasks.append(pool.submit(run, 'fixer-shared-probe', [PHP, str(QA / 'vendor/bin/php-cs-fixer'),
            'fix', '--dry-run', '--using-cache=no', '--config=' + str(QA / 'config/.php-cs-fixer.dist.php'),
            '--path-mode=override', *SCOPE]))
        tasks.append(pool.submit(run, 'phpcs-standards', [PHP, str(QA / 'vendor/bin/phpcs'), '-i']))
        lint = []
        for p in FILES:
            r = subprocess.run([PHP, '-l', str(p)], capture_output=True)
            lint.append(dict(file=str(p.relative_to(ROOT)), exit=r.returncode,
                             stdout=r.stdout.decode('utf-8', errors='replace'),
                             stderr=r.stderr.decode('utf-8', errors='replace')))
        (OUT / 'php-lint.json').write_text(json.dumps(lint, indent=2), encoding='utf-8')
        print('php-lint', len(lint), 'failed', sum(r['exit'] != 0 for r in lint), flush=True)
        for t in tasks:
            t.result()
    (OUT / 'source-after.json').write_text(json.dumps(snapshot(), indent=2), encoding='utf-8')
