import concurrent.futures
import hashlib
import json
import os
import pathlib
import shutil
import subprocess
import sys
import time

sys.dont_write_bytecode = True
HERE = pathlib.Path(__file__).resolve().parent
ROOT = HERE.parents[2]
WORK = pathlib.Path('E:/tmp/wtcdek-style-package-20261015')
PHP = 'E:/OSPanel/modules/PHP-8.3/php.exe'
QA = pathlib.Path('E:/.agents/tools/php-qa')
SCOPE = ['script.php','lib_webtolk_wtcdek','plg_system_wtcdek','plg_task_updatewtcdekdata']
FILES = sorted([ROOT/'script.php'] + [p for d in SCOPE[1:] for p in (ROOT/d).rglob('*.php')])

def run(name,args,timeout=240):
    start=time.time()
    try:
        r=subprocess.run(args,cwd=ROOT,capture_output=True,timeout=timeout)
        code,out,err=r.returncode,r.stdout,r.stderr
    except subprocess.TimeoutExpired as e:
        code,out,err=-999,e.stdout or b'',e.stderr or b''
    (HERE/(name+'.stdout')).write_bytes(out)
    (HERE/(name+'.stderr')).write_bytes(err)
    result=dict(command=args,exit=code,seconds=round(time.time()-start,2))
    (HERE/(name+'.run.json')).write_text(json.dumps(result,indent=2),encoding='utf-8')
    print(name,code,flush=True)
    return result

def hashes():
    roots=[ROOT/p for p in SCOPE] + [ROOT/'pkg_lib_wtcdek.xml',ROOT/'language']
    paths=[]
    for p in roots:
        paths.extend([p] if p.is_file() else [f for f in p.rglob('*') if f.is_file()])
    return {str(p.relative_to(ROOT)).replace('\\','/'):hashlib.sha256(p.read_bytes()).hexdigest() for p in sorted(paths)}

def prepare():
    WORK.mkdir(parents=True,exist_ok=False)
    before=hashes()
    for rel in before:
        target=WORK/'source-before'/rel
        target.parent.mkdir(parents=True,exist_ok=True)
        shutil.copy2(ROOT/rel,target)
    old=ROOT/'.packages/WT Cdek library package_1.4.0.zip'
    backup=WORK/'WT Cdek library package_1.4.0.before-style.zip'
    shutil.copy2(old,backup)
    (HERE/'source-before.json').write_text(json.dumps(before,indent=2),encoding='utf-8')
    (HERE/'previous-package.json').write_text(json.dumps(dict(source=str(old),backup=str(backup),bytes=old.stat().st_size,sha256=hashlib.sha256(old.read_bytes()).hexdigest()),indent=2),encoding='utf-8')
    shared=(QA/'config/.php-cs-fixer.dist.php').read_text(encoding='utf-8')
    # Use the exact shared rule array, replacing only the core-specific Finder.
    finder="$finder = PhpCsFixer\\Finder::create()->in(["+','.join("'"+str(ROOT/p).replace('\\','/')+"'" for p in SCOPE[1:])+"])->notPath('/layouts/')->append(['"+str(ROOT/'script.php').replace('\\','/')+"']);\n\n"
    config=shared[:shared.index('$finder =')]+finder+shared[shared.index('$config ='):]
    (HERE/'php-cs-fixer.php').write_text(config,encoding='utf-8')
    print('snapshot',len(before),'files; PHP',len(FILES),'old ZIP preserved',flush=True)

def format_source():
    run('fixer-apply',[PHP,str(QA/'vendor/bin/php-cs-fixer'),'fix','--config='+str(HERE/'php-cs-fixer.php'),'--using-cache=no','--sequential','--format=json'])
    run('phpcbf-apply',[PHP,str(QA/'vendor/bin/phpcbf'),'--standard='+str(QA/'config/ruleset.xml'),'--extensions=php',*SCOPE])
    run('phpcs-after-autofix',[PHP,str(QA/'vendor/bin/phpcs'),'--standard='+str(QA/'config/ruleset.xml'),'--extensions=php','--report=json',*SCOPE])
    (HERE/'source-after-autofix.json').write_text(json.dumps(hashes(),indent=2),encoding='utf-8')

def finish():
    eol=[]
    for file in FILES:
        old=file.read_bytes()
        new=old.replace(b'\r\n',b'\n').replace(b'\r',b'\n')
        if old!=new:
            file.write_bytes(new)
            eol.append(str(file.relative_to(ROOT)))
    (HERE/'eol-normalization.json').write_text(json.dumps(eol,indent=2),encoding='utf-8')
    run('fixer-final-apply',[PHP,str(QA/'vendor/bin/php-cs-fixer'),'fix','--config='+str(HERE/'php-cs-fixer.php'),'--using-cache=no','--sequential','--format=json'])
    run('phpcs-final',[PHP,str(QA/'vendor/bin/phpcs'),'--standard='+str(QA/'config/ruleset.xml'),'--extensions=php','--report=json',*SCOPE])
    run('fixer-final-check',[PHP,str(QA/'vendor/bin/php-cs-fixer'),'fix','--dry-run','--config='+str(HERE/'php-cs-fixer.php'),'--using-cache=no','--sequential','--format=json'])
    (HERE/'source-after-style.json').write_text(json.dumps(hashes(),indent=2),encoding='utf-8')

if __name__=='__main__':
    if sys.argv[1]=='prepare': prepare()
    elif sys.argv[1]=='format': format_source()
    elif sys.argv[1]=='finish': finish()
