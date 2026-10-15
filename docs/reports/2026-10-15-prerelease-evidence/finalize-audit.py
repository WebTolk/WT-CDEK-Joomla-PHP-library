import collections
import hashlib
import importlib.util
import json
import pathlib
import re
import subprocess
import xml.etree.ElementTree as ET
import zipfile

HERE = pathlib.Path(__file__).resolve().parent
spec = importlib.util.spec_from_file_location('audit', HERE / 'run-audit.py')
a = importlib.util.module_from_spec(spec)
spec.loader.exec_module(a)
before = json.loads((HERE / 'source-before.json').read_text(encoding='utf-8'))
current = a.snapshot()
changed = [f for f in current if current[f] != before[f]]
assert changed == ['lib_webtolk_wtcdek/script.php'], 'Product changes exceed the authorized installer fix'
phpcs = json.loads((HERE / 'phpcs.stdout').read_text(encoding='utf-8-sig'))
fixer = json.loads((HERE / 'fixer.stdout').read_text(encoding='utf-8-sig'))
stan = json.loads((HERE / 'phpstan.stdout').read_text(encoding='utf-8-sig'))
ide = json.loads((HERE / 'phpstorm.json').read_text(encoding='utf-8'))
summary = {'product_php_files': len(current), 'changed_product_php': changed, 'other_product_php_unchanged': True,
           'phpcs': {**phpcs['totals'], 'affected_files': sum(bool(v['messages']) for v in phpcs['files'].values()),
                     'top_rules': collections.Counter(m['source'] for f in phpcs['files'].values() for m in f['messages']).most_common(15)},
           'fixer': {'affected_files': len(fixer['files']), 'layout_excluded_by_shared_policy': True},
           'phpstan': {'level': 5, 'php_version': '8.3', **stan['totals']}, 'phpstorm': ide['summary']}
summary['native'] = []
for name in ['native-j4-deliverypoints','native-j4-ajax-cookie','native-j4-ajax-routing','native-j5-deliverypoints','native-j5-ajax-cookie','native-j5-ajax-routing','native-deliverypoints-host','native-ajax-cookie','native-ajax-routing']:
    run = json.loads((HERE / (name + '.run.json')).read_text(encoding='utf-8'))
    result = json.loads((HERE / (name + '.stdout')).read_text(encoding='utf-8-sig'))
    summary['native'].append({'name':name, 'exit':run['exit'], **result})
summary['native_checks'] = sum(r['checks'] for r in summary['native'])
summary['installer_fix'] = {'post_fix_lint': [], 'provider_smoke': []}
for version in ['7.4','8.0','8.1','8.3']:
    rows = json.loads((HERE / ('post-fix-php-' + version + '-lint.json')).read_text(encoding='utf-8'))
    summary['installer_fix']['post_fix_lint'].append({'php':version,'checked':len(rows),'failed':sum(r['exit'] != 0 for r in rows)})
for version in ['j4','j5','j6']:
    summary['installer_fix']['provider_smoke'].append(json.loads((HERE / ('installer-smoke-' + version + '.stdout')).read_text(encoding='utf-8-sig')))
summary['phpunit'] = dict(ET.parse(HERE / 'phpunit.xml').getroot().find('testsuite').attrib)
summary['baseline_main'] = {}
patterns = {'lib_webtolk_wtcdek/script.php':['readonly'],
            'lib_webtolk_wtcdek/src/CdekRequest.php':["$cache->get('wt_cdek')", "$cache->store(json_encode($tokenData), 'wt_cdek')"],
            'lib_webtolk_wtcdek/src/Traits/LogTrait.php':["'Log::' . $priority"],
            'plg_task_updatewtcdekdata/src/Extension/Updatewtcdekdata.php':["$this->clearTable('#__lib_wtcdek_delivery_points')", "array_key_exists('code', $deliveryPoint)"]}
for file, needles in patterns.items():
    content = subprocess.run(['git','show','main:'+file],cwd=a.ROOT,capture_output=True,check=True).stdout.decode('utf-8-sig')
    summary['baseline_main'][file] = {needle:needle in content for needle in needles}
metadata = []
for directory in a.SCOPE[1:]:
    for file in sorted((a.ROOT / directory).rglob('*.xml')):
        ET.parse(file)
        metadata.append(str(file.relative_to(a.ROOT)))
ET.parse(a.ROOT / 'pkg_lib_wtcdek.xml')
metadata.append('pkg_lib_wtcdek.xml')
for file in (a.ROOT / 'lib_webtolk_wtcdek/media').glob('*.json'):
    json.loads(file.read_text(encoding='utf-8-sig'))
summary['metadata_xml_parsed'] = metadata
summary['packages'] = []
for name in ['WT Cdek library package_1.3.2.zip','WT Cdek library package_1.4.0-dev.zip','WT Cdek library package_1.4.0.zip']:
    file = a.ROOT / '.packages' / name
    row = {'name':name,'bytes':file.stat().st_size,'sha256':hashlib.sha256(file.read_bytes()).hexdigest()}
    with zipfile.ZipFile(file) as archive:
        row['entries'] = len(archive.namelist())
        row['bad_crc_entry'] = archive.testzip()
    summary['packages'].append(row)
(HERE / 'summary.json').write_text(json.dumps(summary,ensure_ascii=False,indent=2),encoding='utf-8')
(HERE / 'source-final.json').write_text(json.dumps(current,indent=2),encoding='utf-8')
print(json.dumps(summary,ensure_ascii=False,indent=2))
