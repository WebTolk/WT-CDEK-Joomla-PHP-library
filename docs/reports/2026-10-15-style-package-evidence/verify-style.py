import concurrent.futures
import importlib.util
import json
import pathlib
import subprocess
import sys

sys.dont_write_bytecode=True
HERE=pathlib.Path(__file__).resolve().parent
spec=importlib.util.spec_from_file_location('style',HERE/'style-runner.py')
a=importlib.util.module_from_spec(spec)
spec.loader.exec_module(a)

def lint(version):
    rows=[]
    for file in a.FILES:
        r=subprocess.run(['E:/OSPanel/modules/PHP-'+version+'/php.exe','-l',str(file)],capture_output=True)
        rows.append(dict(file=str(file.relative_to(a.ROOT)).replace('\\','/'),exit=r.returncode,stdout=r.stdout.decode('utf-8',errors='replace'),stderr=r.stderr.decode('utf-8',errors='replace')))
    (HERE/('php-'+version+'-lint.json')).write_text(json.dumps(rows,indent=2),encoding='utf-8')
    print('lint',version,len(rows),'failures',sum(r['exit']!=0 for r in rows),flush=True)

with concurrent.futures.ThreadPoolExecutor(max_workers=4) as pool:
    list(pool.map(lint,['7.4','8.0','8.1','8.3']))
for tag,core,php in [('j4','E:/.agents/docs/joomla/core/Joomla-core/4.x/4.4.14','E:/OSPanel/modules/PHP-7.4/php.exe'),('j5','E:/.agents/docs/joomla/core/Joomla-core/5.x/5.4.7',a.PHP),('j6','E:/OSPanel/home/joomla.local/public',a.PHP)]:
    for test in ['deliverypoints','ajax-cookie','ajax-routing']:
        launcher="$_SERVER['HTTP_HOST']='localhost'; $_SERVER['SCRIPT_NAME']='/index.php'; $_SERVER['PHP_SELF']='/index.php'; require '.webtolk/tests/"+test+".php';"
        a.run('native-'+tag+'-'+test,[php,'-r',launcher,core])
    a.run('installer-'+tag,[php,str(a.ROOT/'docs/reports/2026-10-15-prerelease-evidence/installer-smoke.php'),core])
previous=a.ROOT/'docs/reports/2026-10-15-prerelease-evidence'
with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:
    jobs=[pool.submit(a.run,'phpstan',[a.PHP,str(a.QA/'vendor/bin/phpstan'),'analyse','--configuration='+str(previous/'phpstan.neon'),'--no-progress','--error-format=json','--memory-limit=1G']),
          pool.submit(a.run,'deferred-phpunit',[a.PHP,str(a.QA/'vendor/bin/phpunit'),'--do-not-cache-result','--log-junit',str(HERE/'deferred-phpunit.xml'),str(previous/'ReleaseContractsTest.php')])]
    for job in jobs: job.result()
before=json.loads((HERE/'source-before.json').read_text(encoding='utf-8'))
after=a.hashes()
changed=[file for file in after if before[file]!=after[file]]
assert all(file.endswith('.php') for file in changed),changed
(HERE/'changed-files.json').write_text(json.dumps(changed,indent=2),encoding='utf-8')
print('Changed PHP files',len(changed),'; all non-PHP product files preserved',flush=True)
