import hashlib
import importlib.util
import json
import pathlib
import re
import subprocess
import sys
import xml.etree.ElementTree as ET
import zipfile

sys.dont_write_bytecode=True
HERE=pathlib.Path(__file__).resolve().parent
spec=importlib.util.spec_from_file_location('style',HERE/'style-runner.py')
a=importlib.util.module_from_spec(spec)
spec.loader.exec_module(a)
archive=a.ROOT/'.packages/WT Cdek library package_1.4.0.zip'
stage=a.WORK/'final-package-audit'
assert not stage.exists(),'Do not overwrite audit extraction'
snapshot=json.loads((HERE/'source-after-style.json').read_text(encoding='utf-8'))
assert a.hashes()==snapshot,'Packager changed source files'
result=dict(archive=str(archive),bytes=archive.stat().st_size,sha256=hashlib.sha256(archive.read_bytes()).hexdigest(),source_unchanged=True,php=[],manifests=[],js=[])
with zipfile.ZipFile(archive) as package:
    names=package.namelist()
    assert package.testzip() is None
    assert len(names)==len(set(names))
    assert all(not n.startswith(('/', '.', 'docs/', 'tools/', 'tests/')) and '..' not in pathlib.PurePosixPath(n).parts for n in names)
    assert set(names)==set(snapshot),(set(names)-set(snapshot),set(snapshot)-set(names))
    result['entries']=len(names)
    for name in names:
        content=package.read(name)
        if name.endswith(('.php','.xml','.ini','.json','.js')):
            assert b'__DEPLOY_VERSION__' not in content and b'__DEPLOY_DATE__' not in content,name
        target=(stage/name).resolve()
        assert target.is_relative_to(stage.resolve()),name
        target.parent.mkdir(parents=True,exist_ok=True)
        target.write_bytes(content)
        if name.endswith('.php'):
            checked=subprocess.run(['E:/OSPanel/modules/PHP-7.4/php.exe','-l',str(target)],capture_output=True)
            assert checked.returncode==0,(name,checked.stdout,checked.stderr)
            result['php'].append(name)
        if name.endswith('.xml'):
            node=ET.fromstring(content)
            version=node.findtext('version')
            if version is not None:
                assert version=='1.4.0',(name,version)
                result['manifests'].append(dict(file=name,version=version))
        if name.endswith('.js'):
            # Shared builder replaces release-header metadata; script bodies stay exact.
            body=lambda b:re.sub(rb'^\s*/\*\*.*?\*/\s*',b'',b,count=1,flags=re.S)
            assert body(content)==body((a.ROOT/name).read_bytes()),name
            checked=subprocess.run(['E:/nodejs/node.exe','--check',str(target)],capture_output=True)
            assert checked.returncode==0,(name,checked.stderr)
            result['js'].append(name)
        if name.endswith('.json'):
            json.loads(content.decode('utf-8-sig'))
    assert len(result['manifests'])==4
listfile=HERE/'package-php-files.json'
listfile.write_text(json.dumps(result['php']),encoding='utf-8')
def tokens(root):
    r=subprocess.run([a.PHP,str(HERE/'php-token-manifest.php'),str(root),str(listfile)],capture_output=True,check=True)
    return json.loads(r.stdout)
source_tokens=tokens(a.ROOT)
archive_tokens=tokens(stage)
assert source_tokens==archive_tokens,'Packaged executable PHP differs from source'
(HERE/'package-php-tokens.json').write_text(json.dumps(archive_tokens,indent=2),encoding='utf-8')
result['php_tokens_equal_source']=True
previous=json.loads((HERE/'previous-package.json').read_text(encoding='utf-8'))
assert hashlib.sha256(pathlib.Path(previous['backup']).read_bytes()).hexdigest()==previous['sha256']
result['previous_package_preserved']=previous
result['style']=json.loads((HERE/'phpcs-final.stdout').read_text(encoding='utf-8-sig'))['totals']
assert result['style']['errors']==result['style']['warnings']==0
assert json.loads((HERE/'fixer-final-check.stdout').read_text(encoding='utf-8-sig'))['files']==[]
result['native_checks']=sum(json.loads(p.read_text(encoding='utf-8-sig'))['checks'] for p in HERE.glob('native-*.stdout'))
result['result']='passed'
(HERE/'package-audit.json').write_text(json.dumps(result,indent=2,ensure_ascii=False),encoding='utf-8')
print(json.dumps({**result,'php':len(result['php']),'js':len(result['js'])},indent=2,ensure_ascii=False))
