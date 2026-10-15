import datetime
import hashlib
import json
import pathlib
import shutil
import subprocess
import sys
import zipfile

HERE=pathlib.Path(__file__).resolve().parent
ROOT=HERE.parents[2]
BACKUP=pathlib.Path('E:/tmp/wtcdek-whats-new-rebuild-20261015')
ARCHIVE=ROOT/'.packages/WT Cdek library package_1.4.0.zip'
INI=['language/ru-RU/pkg_lib_wtcdek.sys.ini','language/en-GB/pkg_lib_wtcdek.sys.ini']
SCOPE=['script.php','pkg_lib_wtcdek.xml','language','lib_webtolk_wtcdek','plg_system_wtcdek','plg_task_updatewtcdekdata']

def hashes():
    files=[]
    for name in SCOPE:
        path=ROOT/name
        files.extend([path] if path.is_file() else [p for p in path.rglob('*') if p.is_file()])
    return {str(p.relative_to(ROOT)).replace('\\','/'):hashlib.sha256(p.read_bytes()).hexdigest() for p in sorted(files)}

if sys.argv[1]=='prepare':
    BACKUP.mkdir(parents=True,exist_ok=False)
    snapshot=hashes()
    (HERE/'source-before.json').write_text(json.dumps(snapshot,indent=2),encoding='utf-8')
    for name in INI:
        target=BACKUP/name
        target.parent.mkdir(parents=True,exist_ok=True)
        shutil.copy2(ROOT/name,target)
    target=BACKUP/'WT Cdek library package_1.4.0.before-whats-new.zip'
    shutil.copy2(ARCHIVE,target)
    before=dict(path=str(target),bytes=target.stat().st_size,sha256=hashlib.sha256(target.read_bytes()).hexdigest())
    assert before['sha256']=='45cd8942f90233060c89b8236c751f62259f31753c7a28d1b67ee454b85640f9'
    (HERE/'previous-package.json').write_text(json.dumps(before,indent=2),encoding='utf-8')
    print('Previous ZIP and both INI files preserved')
elif sys.argv[1]=='build':
    before=json.loads((HERE/'source-before.json').read_text(encoding='utf-8'))
    current=hashes()
    assert sorted(k for k in current if before[k]!=current[k])==sorted(INI)
    # Read the actual INI using supported PHP 7.4 before building.
    for name in INI:
        php="$v=parse_ini_file($argv[1],false,INI_SCANNER_RAW); if ($v===false) exit(1); echo json_encode($v['PKG_LIB_WTCDEK_WHATS_NEW'],JSON_UNESCAPED_UNICODE);"
        r=subprocess.run(['E:/OSPanel/modules/PHP-7.4/php.exe','-r',php,str(ROOT/name)],cwd=ROOT,capture_output=True,check=True)
        text=json.loads(r.stdout)
        assert text.count('<li>')==4 and '<code>Deliverypoints</code>' in text and '<code>watchfield</code>' in text
    args=['E:/OSPanel/modules/PHP-8.3/php.exe','E:/.agents/tools/phing-packager/phing-latest.phar','-f','phing.xml','3. Package release']
    r=subprocess.run(args,cwd=ROOT,capture_output=True)
    (HERE/'packager.stdout').write_bytes(r.stdout)
    (HERE/'packager.stderr').write_bytes(r.stderr)
    (HERE/'packager-run.json').write_text(json.dumps(dict(command=args,exit=r.returncode),indent=2),encoding='utf-8')
    assert r.returncode==0,r.stderr
    assert hashes()==current,'Packager changed source'
    old=json.loads((HERE/'previous-package.json').read_text(encoding='utf-8'))
    with zipfile.ZipFile(old['path']) as prior,zipfile.ZipFile(ARCHIVE) as package:
        assert package.testzip() is None
        assert package.namelist()==prior.namelist()
        changed=[name for name in package.namelist() if package.read(name)!=prior.read(name)]
        assert sorted(changed)==sorted(INI),changed
        texts={}
        for name in INI:
            assert package.read(name)==(ROOT/name).read_bytes()
            texts[name]=next(line for line in package.read(name).decode('utf-8-sig').splitlines() if line.startswith('PKG_LIB_WTCDEK_WHATS_NEW'))
        result=dict(archive=str(ARCHIVE),bytes=ARCHIVE.stat().st_size,sha256=hashlib.sha256(ARCHIVE.read_bytes()).hexdigest(),entries=len(package.namelist()),changed_entries=changed,other_64_entries_identical=True,source_preserved=True,ini_php74_parse='passed',whats_new=texts,previous_package=old,result='passed')
    (HERE/'source-after.json').write_text(json.dumps(current,indent=2),encoding='utf-8')
    (HERE/'verification.json').write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf-8')
    print(json.dumps({k:v for k,v in result.items() if k!='whats_new'},ensure_ascii=False,indent=2))
