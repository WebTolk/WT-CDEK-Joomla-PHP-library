<?php
/** Audit probes: real product/Joomla classes, memory-only cache/logger, no site bootstrap. */
require_once __DIR__ . '/phpstan-bootstrap.php';

use Joomla\CMS\Cache\CacheController;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\OutputController;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\DI\Container;
use Joomla\Registry\Registry;
use PHPUnit\Framework\TestCase;
use Webtolk\Cdekapi\CdekRequest;

final class AuditMemoryOutput extends OutputController
{
    public array $values = [];
    public function __construct() {}
    public function get($id, $group = null) { return $this->values[$id] ?? false; }
    public function store($data, $id, $group = null, $wrkarounds = true) { $this->values[$id] = $data; return true; }
}

final class AuditCaptureLog extends Log
{
    public array $entries = [];
    public function __construct() { parent::__construct(); }
    protected function addLogEntry(LogEntry $entry) { $this->entries[] = $entry; }
}

final class ReleaseContractsTest extends TestCase
{
    private AuditMemoryOutput $cache;

    protected function setUp(): void
    {
        $this->cache = new AuditMemoryOutput();
        $factory = new class($this->cache) implements CacheControllerFactoryInterface {
            private AuditMemoryOutput $cache;
            public function __construct(AuditMemoryOutput $cache) { $this->cache = $cache; }
            public function createCacheController($type = 'output', $options = []): CacheController { return $this->cache; }
        };
        Factory::$container = new Container();
        Factory::$container->set('config', new Registry(['cache_path' => '', 'cache_handler' => 'file']));
        Factory::$container->set(CacheControllerFactoryInterface::class, $factory);
        $params = new ReflectionProperty(CdekRequest::class, 'pluginParams');
        $params->setAccessible(true);
        $params->setValue(null, ['show_library_errors' => 0]);
    }

    public function testErrorPriorityReachesJoomlaLogger(): void
    {
        $logger = new AuditCaptureLog();
        Log::setInstance($logger);
        (new CdekRequest(false, 'audit-account', 'audit-secret'))->saveToLog('offline audit error', 'ERROR');
        self::assertCount(1, $logger->entries);
        self::assertSame(Log::ERROR, $logger->entries[0]->priority, 'ERROR was silently downgraded by Joomla LogEntry.');
    }

    public function testTokenCacheSeparatesAccountsAndEnvironments(): void
    {
        foreach ([[false, 'account-a', 'token-a'], [false, 'account-b', 'token-b'], [true, 'account-a', 'token-sandbox']] as $row) {
            $request = new CdekRequest($row[0], $row[1], 'offline-audit-secret');
            $request->storeTokenData(['token' => $row[2], 'token_type' => 'Bearer', 'expires_in' => 3600]);
        }
        self::assertCount(3, $this->cache->values, 'Accounts and sandbox overwrite the same cache entry.');
    }

    public function testSchedulerPreservesExistingPointsWhenProviderReturnsAnError(): void
    {
        $queries = [];
        $db = $this->createMock(\Joomla\Database\DatabaseInterface::class);
        $db->method('quoteName')->willReturnCallback(static function ($value) { return '`' . $value . '`'; });
        $db->method('setQuery')->willReturnCallback(static function ($query) use (&$queries, $db) { $queries[] = (string) $query; return $db; });
        $db->method('execute')->willReturn(true);
        Factory::$container->set(\Joomla\Database\DatabaseInterface::class, $db);
        $plugin = (new ReflectionClass(\Joomla\Plugin\Task\Updatewtcdekdata\Extension\Updatewtcdekdata::class))->newInstanceWithoutConstructor();
        $plugin->setDatabase($db);
        $provider = new class {
            public function getDeliveryPoints($options) { return ['error_code' => 503, 'error_message' => 'offline failure']; }
        };
        $method = new ReflectionMethod($plugin, 'updateDeliverypoints');
        $method->setAccessible(true);
        $failure = null;
        try {
            $method->invoke($plugin, $provider, new Registry(['countries_for_update' => ['RU']]));
        } catch (\Throwable $error) {
            $failure = get_class($error) . ': ' . $error->getMessage();
            file_put_contents(__DIR__ . '/scheduler-exception.txt', (string) $error);
        }
        self::assertSame([], $queries, 'Existing points were truncated before provider success. Failure: ' . $failure);
    }
}
