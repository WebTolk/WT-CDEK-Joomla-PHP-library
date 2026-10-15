import hashlib
import json
import pathlib
import re
import shutil
import subprocess
import zipfile

HERE=pathlib.Path(__file__).resolve().parent
ROOT=HERE.parents[2]
WORK=pathlib.Path('E:/tmp/wtcdek-github-release-20261015')
WORK.mkdir(parents=True,exist_ok=False)
archive=ROOT/'.packages/WT Cdek library package_1.4.0.zip'
sha=hashlib.sha256(archive.read_bytes()).hexdigest()
assert sha=='ca4fbd9107226f0a0322bbe227b8f7586a2008ade493280157fa164556b09c08'
asset=WORK/'WT.Cdek.library.package_1.4.0.zip'
shutil.copy2(archive,asset)
assert hashlib.sha256(asset.read_bytes()).hexdigest()==sha
git=lambda *args:subprocess.run(['git',*args],cwd=ROOT,capture_output=True,check=True).stdout.decode('utf-8',errors='replace').splitlines()
paths=set(git('diff','--name-only'))|set(git('ls-files','--others','--exclude-standard'))
for name in paths:
    assert name.startswith(('language/','lib_webtolk_wtcdek/','plg_system_wtcdek/','plg_task_updatewtcdekdata/','docs/')) or name=='script.php',name
patterns={
    'github_token':r'\b(?:gh[pousr]_[A-Za-z0-9]{30,}|github_pat_[A-Za-z0-9_]{40,})\b',
    'openai_key':r'\bsk-(?:proj-|svcacct-)?[A-Za-z0-9_-]{30,}\b',
    'aws_access_key':r'\b(?:AKIA|ASIA)[A-Z0-9]{16}\b',
    'private_key':r'-----BEGIN (?:RSA |OPENSSH |EC )?PRIVATE KEY-----',
    'authorization_bearer':r'(?i)authorization["\s:=>]+bearer\s+[A-Za-z0-9._-]{30,}',
    'credential_url':r'(?i)https?://[^\s/"<>]+:[^\s/"<>]+@',
}
findings=[]
for name in sorted(paths):
    file=ROOT/name
    if not file.is_file():continue
    if file.suffix.lower() not in ['.php','.js','.json','.xml','.ini','.md','.txt','.stdout','.stderr','.neon','.patch','.py']:continue
    text=file.read_text(encoding='utf-8-sig',errors='replace')
    for kind,pattern in patterns.items():
        for match in re.finditer(pattern,text):
            findings.append(dict(file=name,line=text.count('\n',0,match.start())+1,kind=kind))
with zipfile.ZipFile(asset) as package:
    assert len(package.namelist())==66 and package.testzip() is None
    assert all(not n.startswith(('.git/','.webtolk/','docs/','.idea/')) for n in package.namelist())
    php=[n for n in package.namelist() if n.endswith('.php')]
    # Compare package PHP against the exact working sources used by the release commit.
    for name in php:
        strip=lambda b:re.sub(rb'/\*\*.*?\*/',b'',b,flags=re.S)
        assert strip(package.read(name))==strip((ROOT/name).read_bytes()),name
    for name in ['language/ru-RU/pkg_lib_wtcdek.sys.ini','language/en-GB/pkg_lib_wtcdek.sys.ini']:
        assert package.read(name)==(ROOT/name).read_bytes()
result={'candidate_files':len(paths),'secret_scan_findings':findings,'release_asset':str(asset),'asset_size':asset.stat().st_size,'sha256':sha,'package_php_matches_working_sources':len(php),'tag':'v.1.4.0','target_branch':'main','source_branch':'dev','notes_file':'docs/release/2026-10-15-github-1.4.0-notes.md'}
(HERE/'preflight.json').write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps(result,ensure_ascii=False,indent=2))
assert not findings,'Potential secret requires review before staging'
