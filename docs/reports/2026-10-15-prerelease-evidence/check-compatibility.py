import importlib.util
import json
import pathlib
import subprocess

HERE = pathlib.Path(__file__).resolve().parent
spec = importlib.util.spec_from_file_location('audit', HERE / 'run-audit.py')
a = importlib.util.module_from_spec(spec)
spec.loader.exec_module(a)
results = []
for version in ['7.4', '8.0', '8.1']:
    php = 'E:/OSPanel/modules/PHP-' + version + '/php.exe'
    rows = []
    for file in a.FILES:
        result = subprocess.run([php, '-l', str(file)], capture_output=True)
        rows.append(dict(file=str(file.relative_to(a.ROOT)).replace('\\', '/'), exit=result.returncode,
                         stdout=result.stdout.decode('utf-8', errors='replace'), stderr=result.stderr.decode('utf-8', errors='replace')))
    (HERE / ('php-' + version + '-lint.json')).write_text(json.dumps(rows, indent=2), encoding='utf-8')
    print(version, len(rows), 'failed', [(r['file'],r['stdout']) for r in rows if r['exit']], flush=True)
    results.append(dict(version=version, checked=len(rows), failed=sum(r['exit'] != 0 for r in rows)))
(HERE / 'php-compatibility.json').write_text(json.dumps(results, indent=2), encoding='utf-8')
main = subprocess.run(['git','show','main:lib_webtolk_wtcdek/script.php'], cwd=a.ROOT, capture_output=True, check=True).stdout
target = pathlib.Path('E:/tmp/wtcdek-prerelease-cleanup-20261015/main-library-installer.php')
target.write_bytes(main)
a.run('main-installer-php74', ['E:/OSPanel/modules/PHP-7.4/php.exe','-l',str(target)])
# Host context required by native Joomla Uri::base() in CLI; product/fixture unchanged.
launcher = "$_SERVER['HTTP_HOST']='localhost'; $_SERVER['SCRIPT_NAME']='/index.php'; $_SERVER['PHP_SELF']='/index.php'; require '.webtolk/tests/deliverypoints.php';"
a.run('native-deliverypoints-host', [a.PHP,'-r',launcher])
