import hashlib
import json
import pathlib
import re
import subprocess
import xml.etree.ElementTree as ET

HERE=pathlib.Path(__file__).resolve().parent
ROOT=HERE.parents[2]
audit=json.loads((HERE/'package-audit.json').read_text(encoding='utf-8'))
archive=pathlib.Path(audit['archive'])
assert hashlib.sha256(archive.read_bytes()).hexdigest()==audit['sha256']
snapshot=json.loads((HERE/'source-after-style.json').read_text(encoding='utf-8'))
assert all(hashlib.sha256((ROOT/rel).read_bytes()).hexdigest()==sha for rel,sha in snapshot.items())
records=json.loads((ROOT/'docs/tasks/2026-10-15-prerelease/tasks.json').read_text(encoding='utf-8'))
schema=json.loads((ROOT/'.webtolk/config/task.schema.json').read_text(encoding='utf-8'))
assert len(records)==13
for task in records:
    assert set(schema['required']).issubset(task)
    assert set(task).issubset(schema['properties'])
    assert all((ROOT/name).is_file() for name in task['artifacts'])
docs=list((ROOT/'docs/tasks/2026-10-15-prerelease').glob('*.md'))+list((ROOT/'docs/reports').glob('2026-10-15-*.md'))+list((ROOT/'docs/release').glob('2026-10-15-*.md'))
links=0
for file in docs:
    for label,href in re.findall(r'\[([^\]]+)\]\(([^)]+)\)',file.read_text(encoding='utf-8')):
        if '://' in href or href.startswith('#'): continue
        href=re.sub(r':\d+$','',href.split('#')[0])
        assert (file.parent/href).resolve().is_file(),(str(file),href)
        links+=1
ide=json.loads((HERE/'phpstorm-errors.json').read_text(encoding='utf-8'))
assert len(ide)==39 and not any(x.get('problems') or x.get('timedOut') or x.get('toolError') for x in ide)
ast=json.loads((HERE/'ast-comparison.json').read_text(encoding='utf-8'))
assert len(ast)==40 and all(x['same_normalized_ast'] for x in ast)
results={}
for version in ['7.4','8.0','8.1','8.3']:
    rows=json.loads((HERE/('php-'+version+'-lint.json')).read_text(encoding='utf-8'))
    assert len(rows)==40 and all(row['exit']==0 for row in rows)
    results[version]=len(rows)
prior=ROOT/'docs/reports/2026-10-15-prerelease-evidence'
count=lambda r:sorted(m['identifier'] for f in r['files'].values() for m in f['messages'])
stan_before=json.loads((prior/'phpstan.stdout').read_text(encoding='utf-8-sig'))
stan_after=json.loads((HERE/'phpstan.stdout').read_text(encoding='utf-8-sig'))
assert count(stan_before)==count(stan_after)
suite=ET.parse(HERE/'deferred-phpunit.xml').getroot().find('testsuite')
assert suite.get('tests')=='3' and suite.get('failures')=='3' and suite.get('errors')=='0'
diff=subprocess.run(['git','-c','core.autocrlf=false','diff','--check'],cwd=ROOT,capture_output=True)
assert diff.returncode==0,diff.stderr.decode(errors='replace')
status=subprocess.run(['git','status','--porcelain=v1'],cwd=ROOT,capture_output=True,check=True).stdout.decode('utf-8',errors='replace')
(HERE/'git-status.txt').write_text(status,encoding='utf-8')
result={'result':'passed','tasks':len(records),'documents':len(docs),'links_checked':links,'php_lint':results,'phpstorm_files':len(ide),'phpstorm_errors':0,'normalized_ast_equal':len(ast),'phpstan_message_identifiers_unchanged':True,'deferred_phpunit_failures':3,'package_sha256':audit['sha256'],'source_hashes_preserved':len(snapshot),'git_diff_check':True}
(HERE/'final-validation.json').write_text(json.dumps(result,indent=2),encoding='utf-8')
print(json.dumps(result,indent=2))
