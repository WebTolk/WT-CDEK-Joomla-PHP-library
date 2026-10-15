import concurrent.futures
import importlib.util
import json
import pathlib
import re

HERE = pathlib.Path(__file__).resolve().parent
spec = importlib.util.spec_from_file_location('audit', HERE / 'run-audit.py')
a = importlib.util.module_from_spec(spec)
spec.loader.exec_module(a)

# Keep the shared Joomla rule array intact; replace only its core-only Finder.
original = (a.QA / 'config/.php-cs-fixer.dist.php').read_text(encoding='utf-8')
finder = "$finder = PhpCsFixer\\Finder::create()->in([" + ','.join("'" + str(a.ROOT / p).replace('\\', '/') + "'" for p in a.SCOPE[1:]) + "])->notPath('/layouts/')->append(['" + str(a.ROOT / 'script.php').replace('\\', '/') + "']);\n\n"
adapted = original[:original.index('$finder =')] + finder + original[original.index('$config ='):]
(HERE / 'fixer-scope.php').write_text(adapted, encoding='utf-8')

core = pathlib.Path('E:/OSPanel/home/joomla.local/public')
root = str(a.ROOT).replace('\\', '/')
bootstrap = "<?php\ndefine('_JEXEC', 1);\ndefine('JDEBUG', false);\n"
for name, suffix in {'ROOT':'','BASE':'','SITE':'','ADMINISTRATOR':'/administrator','LIBRARIES':'/libraries','PLATFORM':'/libraries','THEMES':'/templates','CACHE':'/cache','CONFIGURATION':'','LANGUAGE':'/language','LAYOUTS':'/layouts','PLUGINS':'/plugins'}.items():
    bootstrap += f"define('JPATH_{name}', '{core}{suffix}');\n"
bootstrap += f"require '{core}/libraries/loader.php';\nJLoader::setup();\n$loader = require '{core}/libraries/vendor/autoload.php';\n"
for ns, path in [('Webtolk\\Cdekapi\\','lib_webtolk_wtcdek/src'),('Joomla\\Plugin\\System\\Wtcdek\\','plg_system_wtcdek/src'),('Joomla\\Plugin\\Task\\Updatewtcdekdata\\','plg_task_updatewtcdekdata/src'),('Joomla\\Component\\Scheduler\\Administrator\\',str(core / 'administrator/components/com_scheduler/src'))]:
    source = path if ':' in path else root + '/' + path
    bootstrap += "$loader->addPsr4('" + ns.replace('\\', '\\\\') + "', '" + source + "');\n"
(HERE / 'phpstan-bootstrap.php').write_text(bootstrap, encoding='utf-8')
config = 'parameters:\n    level: 5\n    phpVersion: 80300\n    tmpDir: E:/tmp/wtcdek-prerelease-cleanup-20261015/qa-cache\n    bootstrapFiles:\n        - ' + str(HERE / 'phpstan-bootstrap.php').replace('\\', '/') + '\n    paths:\n'
for p in a.SCOPE:
    config += '        - ' + root + '/' + p + '\n'
(HERE / 'phpstan.neon').write_text(config, encoding='utf-8')
with concurrent.futures.ThreadPoolExecutor(max_workers=3) as pool:
    jobs = [pool.submit(a.run, 'fixer', [a.PHP, str(a.QA / 'vendor/bin/php-cs-fixer'), 'fix', '--dry-run', '--using-cache=no', '--sequential', '--config=' + str(HERE / 'fixer-scope.php'), '--format=json']),
            pool.submit(a.run, 'phpstan', [a.PHP, str(a.QA / 'vendor/bin/phpstan'), 'analyse', '--configuration=' + str(HERE / 'phpstan.neon'), '--no-progress', '--error-format=json', '--memory-limit=1G']),
            pool.submit(a.run, 'phpunit-config-probe', [a.PHP, str(a.QA / 'vendor/bin/phpunit'), '--list-tests'])]
    for job in jobs:
        job.result()
for name in ['deliverypoints', 'ajax-cookie', 'ajax-routing']:
    a.run('native-' + name, [a.PHP, str(a.ROOT / '.webtolk/tests' / (name + '.php')), str(core)])
for name in ['wtcdek-watchfield', 'wtcdek-deliverypoints', 'wtcdek-tariffinfo']:
    a.run('syntax-' + name, ['E:/nodejs/node.exe', '--check', str(a.ROOT / 'lib_webtolk_wtcdek/media/js' / (name + '.js'))])
(HERE / 'source-after-extended.json').write_text(json.dumps(a.snapshot(), indent=2), encoding='utf-8')
