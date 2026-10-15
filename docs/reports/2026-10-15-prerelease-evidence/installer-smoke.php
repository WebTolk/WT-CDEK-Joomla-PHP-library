<?php
/** Loads only local Joomla classes and the product provider; performs no installation. */
define('_JEXEC', 1);
define('JDEBUG', false);
$core = $argv[1];
foreach (['ROOT' => '', 'BASE' => '', 'SITE' => '', 'ADMINISTRATOR' => '/administrator', 'LIBRARIES' => '/libraries', 'PLATFORM' => '/libraries', 'THEMES' => '/templates', 'CACHE' => '/cache', 'CONFIGURATION' => '', 'LANGUAGE' => '/language', 'LAYOUTS' => '/layouts', 'PLUGINS' => '/plugins'] as $name => $suffix) {
    define('JPATH_' . $name, $core . $suffix);
}
require $core . '/libraries/loader.php';
JLoader::setup();
require $core . '/libraries/vendor/autoload.php';
$app = (new ReflectionClass(Joomla\CMS\Application\AdministratorApplication::class))->newInstanceWithoutConstructor();
$container = new Joomla\DI\Container();
$container->set(Joomla\CMS\Application\AdministratorApplication::class, $app, true);
$provider = require dirname(__DIR__, 3) . '/lib_webtolk_wtcdek/script.php';
$provider->register($container);
$installer = $container->get(Joomla\CMS\Installer\InstallerScriptInterface::class);
if (!$installer instanceof Joomla\CMS\Installer\InstallerScriptInterface) {
    throw new RuntimeException('Wrong installer interface');
}
$property = new ReflectionProperty($installer, 'app');
$property->setAccessible(true);
if ($property->getValue($installer) !== $app) {
    throw new RuntimeException('Application injection changed');
}
echo json_encode(['result' => 'passed', 'php' => PHP_VERSION, 'core' => $core, 'provider_loaded' => true, 'interface_resolved' => true, 'application_preserved' => true, 'installation_executed' => false]);
