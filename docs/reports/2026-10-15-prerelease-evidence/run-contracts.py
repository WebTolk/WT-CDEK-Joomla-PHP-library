import importlib.util
import pathlib

p = pathlib.Path(__file__).resolve().parent / 'run-audit.py'
s = importlib.util.spec_from_file_location('audit', p)
a = importlib.util.module_from_spec(s)
s.loader.exec_module(a)
a.run('phpunit', [a.PHP, str(a.QA / 'vendor/bin/phpunit'), '--do-not-cache-result',
                 '--log-junit', str(p.parent / 'phpunit.xml'), str(p.parent / 'ReleaseContractsTest.php')])
cores = [('4', 'E:/.agents/docs/joomla/core/Joomla-core/4.x/4.4.14', 'E:/OSPanel/modules/PHP-7.4/php.exe'),
         ('5', 'E:/.agents/docs/joomla/core/Joomla-core/5.x/5.4.7', a.PHP)]
for version, core, php in cores:
    for name in ['deliverypoints', 'ajax-cookie', 'ajax-routing']:
        launcher = "$_SERVER['HTTP_HOST']='localhost'; $_SERVER['SCRIPT_NAME']='/index.php'; $_SERVER['PHP_SELF']='/index.php'; require '.webtolk/tests/" + name + ".php';"
        a.run('native-j' + version + '-' + name, [php, '-r', launcher, core])
