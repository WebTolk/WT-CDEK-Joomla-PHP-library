<?php
/**
 * Library to connect to CDEK service.
 * @package    WT Cdek library package
 * @author     Sergey Tolkachyov
 * @copyright   Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version     1.3.0
 * @license     GNU General Public License version 3 or later. Only for *.php files!
 * @link        https://web-tolk.ru
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi;
defined('_JEXEC') or die;

use InvalidArgumentException;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\OutputController;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;
use Webtolk\Cdekapi\Entities\CalculatorEntity;
use Webtolk\Cdekapi\Entities\CheckEntity;
use Webtolk\Cdekapi\Entities\DeliveryEntity;
use Webtolk\Cdekapi\Entities\DeliverypointsEntity;
use Webtolk\Cdekapi\Entities\IntakesEntity;
use Webtolk\Cdekapi\Entities\InternationalEntity;
use Webtolk\Cdekapi\Entities\LocationEntity;
use Webtolk\Cdekapi\Entities\OauthEntity;
use Webtolk\Cdekapi\Entities\OrdersEntity;
use Webtolk\Cdekapi\Entities\PassportEntity;
use Webtolk\Cdekapi\Entities\PaymentEntity;
use Webtolk\Cdekapi\Entities\PhotoDocumentEntity;
use Webtolk\Cdekapi\Entities\PrealertEntity;
use Webtolk\Cdekapi\Entities\PrintEntity;
use Webtolk\Cdekapi\Entities\ReverseEntity;
use Webtolk\Cdekapi\Entities\WebhooksEntity;
use Webtolk\Cdekapi\Interfaces\EntityInterface;
use function is_array;
use function is_file;
use function preg_replace;
use function str_replace;
use function strtolower;
use function trim;

/**
 * Основной фасад API СДЭК с магическими аксессорами к сущностям.
 *
 * @method  CalculatorEntity     calculator()     Возвращает обработчик сущности калькулятора.
 * @method  CheckEntity          check()          Возвращает обработчик сущности чеков.
 * @method  DeliveryEntity       delivery()       Возвращает обработчик сущности договоренностей о доставке.
 * @method  DeliverypointsEntity deliverypoints() Возвращает обработчик сущности офисов.
 * @method  IntakesEntity        intakes()        Возвращает обработчик сущности заявок на вызов курьера.
 * @method  InternationalEntity  international()  Возвращает обработчик сущности международных ограничений.
 * @method  LocationEntity       location()       Возвращает обработчик сущности локаций.
 * @method  OauthEntity          oauth()          Возвращает обработчик сущности авторизации.
 * @method  OrdersEntity         orders()         Возвращает обработчик сущности заказов.
 * @method  PassportEntity       passport()       Возвращает обработчик сущности паспортных данных.
 * @method  PaymentEntity        payment()        Возвращает обработчик сущности наложенных платежей.
 * @method  PhotoDocumentEntity  photoDocument()  Возвращает обработчик сущности фото документов.
 * @method  PrealertEntity       prealert()       Возвращает обработчик сущности преалертов.
 * @method  PrintEntity          print()          Возвращает обработчик сущности печатных форм.
 * @method  ReverseEntity        reverse()        Возвращает обработчик сущности реверса.
 * @method  WebhooksEntity       webhooks()       Возвращает обработчик сущности вебхуков.
 *
 * @since   1.3.0
 */
final class Cdek
{
	/**
	 * @var string $token_type Тип токена. По умолчанию `Bearer`
	 * @since 1.0.0
	 */
	public static string $token_type = 'Bearer';
	/**
	 * @var int $expires_in Срок действия токена
	 * @since 1.0.0
	 */
	public static int $expires_in;

	/**
	 * Хост боевой среды
	 * @var string $cdek_api_url
	 * @since 1.0.0
	 */
	public static string $cdek_api_url = 'https://api.cdek.ru/v2';
	/**
	 * Хост тестовой среды
	 * @var string $cdek_api_url_test
	 * @since 1.0.0
	 */
	public static string $cdek_api_url_test = 'https://api.edu.cdek.ru/v2';
	/**
	 * Параметры системного плагина WT Cdek
	 *
	 * @var array $plugin_params
	 * @since 1.0.0
	 */
	public static array $plugin_params = [];
	/**
	 * @var string $token
	 * @since 1.0.0
	 */
	protected static string $token;
	/**
	 * Идентификатор аккаунта
	 * @var string $client_id
	 * @since 1.0.0
	 */
	protected static string $client_id;
	/**
	 * $client_id
	 * @var string $client_secret
	 * @since 1.0.0
	 */
	protected static string $client_secret;
	/**
	 * Тестовый режим
	 * @var bool $test_mode
	 * @since 1.0.0
	 */
	protected static bool $test_mode = false;
	/**
	 * Транспортный помощник для API-запросов.
	 *
	 * @var    CdekRequest
	 * @since  1.2.1
	 */
	private CdekRequest $request;

	/**
	 * Карта классов сущностей, загруженная из реестра.
	 *
	 * @var    array<string, string>
	 * @since  1.2.1
	 */
	private array $entityMap = [];

	/**
	 * Кэш созданных экземпляров сущностей.
	 *
	 * @var    array<string, EntityInterface>
	 * @since  1.2.1
	 */
	private array $entities = [];


	/**
	 * @param   bool|null    $test_mode      Флаг тестового или боевого окружения СДЭК
	 * @param   string|null  $client_id      Аккаунт
	 * @param   string|null  $client_secret  Секрет
	 *
	 * @since 1.3.0
	 */
	public function __construct(?bool $test_mode = false, ?string $client_id = '', ?string $client_secret = '')
	{
		self::$client_id     = $client_id ?? '';
		self::$client_secret = $client_secret ?? '';
		self::$test_mode     = $test_mode ?? false;
		$this->request       = new CdekRequest(self::$test_mode, self::$client_id, self::$client_secret);

		$lang      = Factory::getApplication()->getLanguage();
		$extension = 'lib_webtolk_cdekapi';
		$base_dir  = JPATH_SITE;
		$lang->load($extension, $base_dir);

		$registryPath = __DIR__ . '/Entities/registry.php';
		if (is_file($registryPath))
		{
			$map = include $registryPath;
			if (is_array($map))
			{
				$this->entityMap = $map;
			}
		}
	}

	/**
	 * Проверяет, может ли библиотека выполнить REST-запрос к API СДЭК
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function canDoRequest(): bool
	{
		return $this->request->canDoRequest();
	}

	/**
	 * Метод для записи ошибок библиотеки в `lib_webtolk_cdekapi_cdek.log.php` в
	 * директории логов Joomla. Категория лога по умолчанию: `lib_webtolk_cdekapi_cdek`
	 *
	 * @param   string  $data      текст ошибки
	 * @param   string  $priority  приоритет лога Joomla
	 *
	 * @return void
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function saveToLog(string $data, string $priority = 'NOTICE'): void
	{
		Log::addLogger(
			[
				// Sets file name
				'text_file' => 'lib_webtolk_cdekapi_cdek.log.php',
			],
			// Sets all but DEBUG log level messages to be sent to the file
			Log::ALL & ~Log::DEBUG,
			['lib_webtolk_cdekapi_cdek']
		);
		$plugin_params = $this->getPluginParams();
		if ($plugin_params instanceof Registry && $plugin_params->get('show_library_errors', 0) == 1)
		{
			Factory::getApplication()->enqueueMessage($data, $priority);
		}
		$priority = 'Log::' . $priority;
		Log::add($data, $priority, 'lib_webtolk_cdekapi_cdek');

	}

	/**
	 * Возвращает параметры системного плагина WT Cdek
	 *
	 * @return  Registry|false
	 *
	 * @since 1.0.0
	 */
	private function getPluginParams()
	{
		return CdekRequest::getPluginParams();
	}

	/**
	 * Возвращает объект транспортного слоя запросов.
	 *
	 * @return  CdekRequest
	 *
	 * @since   1.2.1
	 */
	public function getRequest(): CdekRequest
	{
		return $this->request;
	}

	/**
	 * Прокси-доступ к сущностям, например `$cdek->orders()`.
	 *
	 * @param   string  $name       Имя вызываемого метода.
	 * @param   array   $arguments  Аргументы метода.
	 *
	 * @return  EntityInterface
	 *
	 * @since   1.2.1
	 */
	public function __call(string $name, array $arguments): EntityInterface
	{
		return $this->entity($name);
	}

	/**
	 * Возвращает экземпляр сущности по имени.
	 *
	 * @param   string  $name  Ключ сущности из реестра.
	 *
	 * @return  EntityInterface
	 *
	 * @since   1.2.1
	 */
	public function entity(string $name): EntityInterface
	{
		$key = $this->resolveEntityKey($name);

		if (!isset($this->entityMap[$key]))
		{
			throw new InvalidArgumentException('CDEK entity not found: ' . $name);
		}

		if (!isset($this->entities[$key]))
		{
			$className            = $this->entityMap[$key];
			$this->entities[$key] = new $className($this->request);
		}

		return $this->entities[$key];
	}

	/**
	 * Преобразует запрошенное имя сущности в ключ реестра.
	 *
	 * @param   string  $name  Запрошенное имя.
	 *
	 * @return  string
	 *
	 * @since   1.2.1
	 */
	private function resolveEntityKey(string $name): string
	{
		$name = trim($name);

		if (isset($this->entityMap[$name]))
		{
			return $name;
		}

		$kebab = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
		$kebab = str_replace('_', '-', $kebab);

		return $kebab;
	}

	/**
	 * @param   array  $request_options  Параметры запроса.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 * @deprecated  Будет удалено в 2.0.0, используйте deliverypoints()->getDeliveryPoints() вместо него.
	 */
	public function getDeliveryPoints(array $request_options = []): array
	{
		return $this->deliverypoints()->getDeliveryPoints($request_options);
	}

	/**
	 * Возвращает преднастроенный объект кэша библиотеки
	 * @return OutputController
	 *
	 * @since 1.0.0
	 */
	public function getCache(array $cache_options = []): OutputController
	{
		$jconfig = Factory::getContainer()->get('config');
		$options = [
			'defaultgroup' => 'wt_cdek',
			'caching'      => true,
			'cachebase'    => $jconfig->get('cache_path'),
			'storage'      => $jconfig->get('cache_handler'),
		];
		$options = array_merge($options, $cache_options);

		return Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController('output', $options);
	}

	/**
	 * @param   array  $request_options  Параметры запроса.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 * @deprecated  Будет удалено в 2.0.0, используйте location()->getRegions() вместо него.
	 */
	public function getLocationRegions(array $request_options = []): array
	{
		return $this->location()->getRegions($request_options);
	}

	/**
	 * @param   array  $request_options  Параметры запроса.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 * @deprecated  Будет удалено в 2.0.0, используйте location()->getCities() вместо него.
	 */
	public function getLocationCities(array $request_options = []): array
	{
		return $this->location()->getCities($request_options);
	}

	/**
	 * @param   int  $city_code  Код города СДЭК.
	 *
	 * @return  array
	 *
	 * @since   1.1.0
	 * @deprecated  Будет удалено в 2.0.0, используйте location()->getPostalCodes() вместо него.
	 */
	public function getLocationPostalCodes(int $city_code): array
	{
		return $this->location()->getPostalCodes($city_code);
	}

	/**
	 * @param   array  $request_options  Параметры запроса.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 * @deprecated  Будет удалено в 2.0.0, используйте calculator()->calculateTariff() вместо него.
	 */
	public function getCalculatorTariff(array $request_options = []): array
	{
		return $this->calculator()->calculateTariff($request_options);
	}

	/**
	 * @param   array  $request_options  Параметры запроса.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 * @deprecated  Будет удалено в 2.0.0, используйте calculator()->calculateTariffList() вместо него.
	 */
	public function getCalculatorTarifflist(array $request_options = []): array
	{
		return $this->calculator()->calculateTariffList($request_options);
	}

	/**
	 * @param   string  $url   URL для вебхуков.
	 * @param   string  $type  Тип события вебхука.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 * @deprecated  Будет удалено в 2.0.0, используйте webhooks()->subscribe() вместо него.
	 */
	public function subscribeToWebhook(string $url, string $type): array
	{
		return $this->webhooks()->subscribe($url, $type);
	}

	/**
	 * @param   array  $request_options  Параметры запроса.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 * @deprecated  Будет удалено в 2.0.0, используйте orders()->createOrder() вместо него.
	 */
	public function createOrder(array $request_options): array
	{
		return $this->orders()->createOrder($request_options);
	}

	/**
	 * Массив с тарифаи CDEK для типа "интернет-агазин"
	 * @return array[] Массив тарифов
	 *
	 * @since 1.0.0
	 */
	public function getTariffListShop(): array
	{
		return [
			[
				'code'                    => 7,
				'name'                    => 'Международный экспресс докуенты дверь-дверь',
				'mode'                    => 'Дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 5,
				'tariff_service'          => 'Международный экспресс',
				'tariff_description'      => 'Экспресс-доставка за/из-за границы докуентов и писе.',
			],
			[
				'code'                    => 8,
				'name'                    => 'Международный экспресс грузы дверь-дверь',
				'mode'                    => 'Дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Международный экспресс',
				'tariff_description'      => 'Экспресс-доставка за/из-за границы грузов и посылок до 30 кг.',
			],

			['code' => 136, 'name' => 'Посылка склад-склад', 'mode' => 'Склад-склад (С-С)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 50, 'tariff_service' => 'Посылка', 'tariff_description' => 'Услуга эконоичной доставки товаров для копаний, осуществляющих дистанционную торговлю.'],
			['code' => 137, 'name' => 'Посылка склад-дверь', 'mode' => 'Склад-дверь (С-Д)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 50, 'tariff_service' => 'Посылка', 'tariff_description' => 'Услуга эконоичной доставки товаров для копаний, осуществляющих дистанционную торговлю.'],
			['code' => 138, 'name' => 'Посылка дверь-склад', 'mode' => 'Дверь-склад (Д-С)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 50, 'tariff_service' => 'Посылка', 'tariff_description' => 'Услуга эконоичной доставки товаров для копаний, осуществляющих дистанционную торговлю.'],
			['code' => 139, 'name' => 'Посылка дверь-дверь', 'mode' => 'Дверь-дверь (Д-Д)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 50, 'tariff_service' => 'Посылка', 'tariff_description' => 'Услуга эконоичной доставки товаров для копаний, осуществляющих дистанционную торговлю.'],

			['code' => 231, 'name' => 'Эконоичная посылка дверь-дверь', 'mode' => 'Дверь-дверь (Д-Д)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 50, 'tariff_service' => 'Эконоичная посылка', 'tariff_description' => 'Услуга эконоичной назеной доставки товаров для копаний, осуществляющих дистанционную торговлю.'],
			['code' => 232, 'name' => 'Эконоичная посылка дверь-склад', 'mode' => 'Дверь-склад (Д-С)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 50, 'tariff_service' => 'Эконоичная посылка', 'tariff_description' => 'Услуга эконоичной назеной доставки товаров для копаний, осуществляющих дистанционную торговлю.'],
			['code' => 233, 'name' => 'Эконоичная посылка склад-дверь', 'mode' => 'Склад-дверь (С-Д)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 50, 'tariff_service' => 'Эконоичная посылка', 'tariff_description' => 'Услуга эконоичной назеной доставки товаров для копаний, осуществляющих дистанционную торговлю.'],
			['code' => 234, 'name' => 'Эконоичная посылка склад-склад', 'mode' => 'Склад-склад (С-С)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 50, 'tariff_service' => 'Эконоичная посылка', 'tariff_description' => 'Услуга эконоичной назеной доставки товаров для копаний, осуществляющих дистанционную торговлю.'],

			['code' => 291, 'name' => 'E-com Express склад-склад', 'mode' => 'Склад-склад (С-С)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 500, 'tariff_service' => 'E-com Express', 'tariff_description' => 'Саая быстрая экспресс-доставка в режие авиа... Только ЮЛ.'],
			['code' => 293, 'name' => 'E-com Express дверь-дверь', 'mode' => 'Дверь-дверь (Д-Д)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 500, 'tariff_service' => 'E-com Express', 'tariff_description' => 'Саая быстрая экспресс-доставка в режие авиа... Только ЮЛ.'],
			['code' => 294, 'name' => 'E-com Express склад-дверь', 'mode' => 'Склад-дверь (С-Д)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 500, 'tariff_service' => 'E-com Express', 'tariff_description' => 'Саая быстрая экспресс-доставка в режие авиа... Только ЮЛ.'],
			['code' => 295, 'name' => 'E-com Express дверь-склад', 'mode' => 'Дверь-склад (Д-С)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 500, 'tariff_service' => 'E-com Express', 'tariff_description' => 'Саая быстрая экспресс-доставка в режие авиа... Только ЮЛ.'],

			// В постаат — зависит от постаата (не число)
			['code' => 509, 'name' => 'E-com Express дверь-постаат', 'mode' => 'Дверь-постаат (Д-П)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_weight_note' => 'в зависиости от ограничений конкретного постаата', 'tariff_service' => 'E-com Express', 'tariff_description' => 'Саая быстрая экспресс-доставка в режие авиа... Только ЮЛ.'],
			['code' => 510, 'name' => 'E-com Express склад-постаат', 'mode' => 'Склад-постаат (С-П)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_weight_note' => 'в зависиости от ограничений конкретного постаата', 'tariff_service' => 'E-com Express', 'tariff_description' => 'Саая быстрая экспресс-доставка в режие авиа... Только ЮЛ.'],

			// Посылка/Эконоичная посылка в постаат — до 30 кг (М)
			['code' => 366, 'name' => 'Посылка дверь-постаат', 'mode' => 'Дверь-постаат (Д-П)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 30, 'tariff_service' => 'Посылка', 'tariff_description' => 'Услуга эконоичной доставки товаров для копаний, осуществляющих дистанционную торговлю.'],
			['code' => 368, 'name' => 'Посылка склад-постаат', 'mode' => 'Склад-постаат (С-П)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 30, 'tariff_service' => 'Посылка', 'tariff_description' => 'Услуга эконоичной доставки товаров для копаний, осуществляющих дистанционную торговлю.'],
			['code' => 378, 'name' => 'Эконоичная посылка склад-постаат', 'mode' => 'Склад-постаат (С-П)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 30, 'tariff_service' => 'Эконоичная посылка', 'tariff_description' => 'Услуга эконоичной назеной доставки товаров для копаний, осуществляющих дистанционную торговлю.'],

			// E-com Standard — ограничение не указано (оставляе null)
			['code' => 184, 'name' => 'E-com Standard дверь-дверь', 'mode' => 'Дверь-дверь (Д-Д)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_service' => 'E-com Standard', 'tariff_description' => 'Стандартная экспресс-доставка... Только ЮЛ. Доступно для "М" и "Доставка".'],
			['code' => 185, 'name' => 'E-com Standard склад-склад', 'mode' => 'Склад-склад (С-С)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_service' => 'E-com Standard', 'tariff_description' => 'Стандартная экспресс-доставка... Только ЮЛ. Доступно для "М" и "Доставка".'],
			['code' => 186, 'name' => 'E-com Standard склад-дверь', 'mode' => 'Склад-дверь (С-Д)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_service' => 'E-com Standard', 'tariff_description' => 'Стандартная экспресс-доставка... Только ЮЛ. Доступно для "М" и "Доставка".'],
			['code' => 187, 'name' => 'E-com Standard дверь-склад', 'mode' => 'Дверь-склад (Д-С)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_service' => 'E-com Standard', 'tariff_description' => 'Стандартная экспресс-доставка... Только ЮЛ. Доступно для "М" и "Доставка".'],
			['code' => 497, 'name' => 'E-com Standard дверь-постаат', 'mode' => 'Дверь-постаат (Д-П)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_service' => 'E-com Standard', 'tariff_description' => 'Стандартная экспресс-доставка... Только ЮЛ. Доступно для "М" и "Доставка".'],
			['code' => 498, 'name' => 'E-com Standard склад-постаат', 'mode' => 'Склад-постаат (С-П)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_service' => 'E-com Standard', 'tariff_description' => 'Стандартная экспресс-доставка... Только ЮЛ. Доступно для "М" и "Доставка".'],

			// Documents Express (еждународные докуенты) — ограничения зависят от направления
			['code' => 2261, 'name' => 'Documents Express', 'mode' => 'Дверь-дверь (Д-Д)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_weight_note' => 'ндивидуальные ограничения в зависиости от направления', 'tariff_service' => 'Documents Express', 'tariff_description' => 'ыстрая еждународная доставка докуентов'],
			['code' => 2262, 'name' => 'Documents Express', 'mode' => 'Дверь-склад (Д-С)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_weight_note' => 'ндивидуальные ограничения в зависиости от направления', 'tariff_service' => 'Documents Express', 'tariff_description' => 'ыстрая еждународная доставка докуентов'],
			['code' => 2263, 'name' => 'Documents Express', 'mode' => 'Склад-дверь (С-Д)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_weight_note' => 'ндивидуальные ограничения в зависиости от направления', 'tariff_service' => 'Documents Express', 'tariff_description' => 'ыстрая еждународная доставка докуентов'],
			['code' => 2264, 'name' => 'Documents Express', 'mode' => 'Склад-склад (С-С)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_weight_note' => 'ндивидуальные ограничения в зависиости от направления', 'tariff_service' => 'Documents Express', 'tariff_description' => 'ыстрая еждународная доставка докуентов'],
			['code' => 2266, 'name' => 'Documents Express', 'mode' => 'Дверь-постаат (Д-П)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_weight_note' => 'ндивидуальные ограничения в зависиости от направления', 'tariff_service' => 'Documents Express', 'tariff_description' => 'ыстрая еждународная доставка докуентов'],
			['code' => 2267, 'name' => 'Documents Express', 'mode' => 'Склад-постаат (С-П)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_weight_note' => 'ндивидуальные ограничения в зависиости от направления', 'tariff_service' => 'Documents Express', 'tariff_description' => 'ыстрая еждународная доставка докуентов'],

			// Эконоичный экспресс (шины) — ограничения индивидуальные
			['code' => 19, 'name' => 'Эконоичный экспресс дверь-дверь', 'mode' => 'Дверь-дверь (Д-Д)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_weight_note' => 'ндивидуальные ограничения в зависиости от направления. Максиальные габариты одной стороны - 300 с', 'tariff_service' => 'Эконоичный экспресс', 'tariff_description' => 'Эконоичная доставка шин. Требуется additional_order_types = "10".'],
			['code' => 2321, 'name' => 'Эконоичный экспресс дверь-склад', 'mode' => 'Дверь-склад (Д-С)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_weight_note' => 'ндивидуальные ограничения... Максиальная сторона 300 с', 'tariff_service' => 'Эконоичный экспресс', 'tariff_description' => 'Эконоичная доставка шин. Требуется additional_order_types = "10".'],
			['code' => 2322, 'name' => 'Эконоичный экспресс склад-дверь', 'mode' => 'Склад-дверь (С-Д)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_weight_note' => 'ндивидуальные ограничения... Максиальная сторона 300 с', 'tariff_service' => 'Эконоичный экспресс', 'tariff_description' => 'Эконоичная доставка шин. Требуется additional_order_types = "10".'],
			['code' => 2323, 'name' => 'Эконоичный экспресс склад-склад', 'mode' => 'Склад-склад (С-С)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_weight_note' => 'ндивидуальные ограничения... Максиальная сторона 300 с', 'tariff_service' => 'Эконоичный экспресс', 'tariff_description' => 'Эконоичная доставка шин. Требуется additional_order_types = "10".'],

			// Доставка день в день
			['code' => 2360, 'name' => 'Доставка день в день', 'mode' => 'Дверь-дверь (Д-Д)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 25, 'tariff_weight_note' => 'не более 50 с на одну из сторон', 'tariff_service' => 'Доставка день в день', 'tariff_description' => 'Забор и доставка курьеро в течение нескольких часов без заезда на склад.'],

			// Один офис (М)
			['code' => 2536, 'name' => 'Один офис (М)', 'mode' => 'Склад-склад (С-С)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 30, 'tariff_service' => 'Один офис (М)', 'tariff_description' => 'Отправка и получение посылок в одно офисе при совпадении офиса отправителя и получателя.'],

			// Фулфилент выдача (вес не указан)
			['code' => 358, 'name' => 'Фулфилент выдача', 'mode' => 'Склад-склад (С-С)', 'tariff_weight_limit_min' => null, 'tariff_weight_limit_max' => null, 'tariff_service' => 'Фулфилент выдача', 'tariff_description' => 'Саовывоз/саозабор со склада Фулфилента при совпадении ПВЗ.'],

			// E-com Express. (доп. коды)
			['code' => 2483, 'name' => 'E-com Express.', 'mode' => 'Дверь-дверь (Д-Д)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 500, 'tariff_service' => 'E-com Express.', 'tariff_description' => 'Саая быстрая экспресс-доставка в режие авиа... Только ЮЛ.'],
			['code' => 2485, 'name' => 'E-com Express.', 'mode' => 'Склад-склад (С-С)', 'tariff_weight_limit_min' => 0, 'tariff_weight_limit_max' => 500, 'tariff_service' => 'E-com Express.', 'tariff_description' => 'Саая быстрая экспресс-доставка в режие авиа... Только ЮЛ.'],
		];
	}

	/**
	 * Массив с тарифаи CDEK для типа "доставка"
	 * @return array массив тарифов
	 *
	 * @since 1.0.0
	 */
	public function getTariffListDostavka(): array
	{
		return [
			[
				'code'                    => 3,
				'name'                    => 'Супер-экспресс до 18',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 57,
				'name'                    => 'Супер-экспресс до 9',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 58,
				'name'                    => 'Супер-экспресс до 10',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 59,
				'name'                    => 'Супер-экспресс до 12',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 60,
				'name'                    => 'Супер-экспресс до 14',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 61,
				'name'                    => 'Супер-экспресс до 16',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],

			[
				'code'                    => 777,
				'name'                    => 'Супер-экспресс до 12',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 786,
				'name'                    => 'Супер-экспресс до 14',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 795,
				'name'                    => 'Супер-экспресс до 16',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 804,
				'name'                    => 'Супер-экспресс до 18',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],

			[
				'code'                    => 778,
				'name'                    => 'Супер-экспресс до 12',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 787,
				'name'                    => 'Супер-экспресс до 14',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 796,
				'name'                    => 'Супер-экспресс до 16',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 805,
				'name'                    => 'Супер-экспресс до 18',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],

			[
				'code'                    => 779,
				'name'                    => 'Супер-экспресс до 12',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 788,
				'name'                    => 'Супер-экспресс до 14',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 797,
				'name'                    => 'Супер-экспресс до 16',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],
			[
				'code'                    => 806,
				'name'                    => 'Супер-экспресс до 18',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],

			[
				'code'                    => 722,
				'name'                    => 'Супер-экспресс до 18',
				'mode'                    => 'склад-постаат (С-П)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за сутки).',
			],

			[
				'code'                    => 62,
				'name'                    => 'Магистральный экспресс склад-склад',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Эконоичная доставка',
				'tariff_description'      => 'ыстрая эконоичная доставка грузов.',
			],
			[
				'code'                    => 121,
				'name'                    => 'Магистральный экспресс дверь-дверь',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Эконоичная доставка',
				'tariff_description'      => 'ыстрая эконоичная доставка грузов.',
			],
			[
				'code'                    => 122,
				'name'                    => 'Магистральный экспресс склад-дверь',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Эконоичная доставка',
				'tariff_description'      => 'ыстрая эконоичная доставка грузов.',
			],
			[
				'code'                    => 123,
				'name'                    => 'Магистральный экспресс дверь-склад',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Эконоичная доставка',
				'tariff_description'      => 'ыстрая эконоичная доставка грузов.',
			],

			[
				'code'                    => 480,
				'name'                    => 'Экспресс дверь-дверь',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка докуентов и грузов по стандартны срока доставки. ез ограничений по весу',
			],
			[
				'code'                    => 481,
				'name'                    => 'Экспресс дверь-склад',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка докуентов и грузов по стандартны срока доставки. ез ограничений по весу',
			],
			[
				'code'                    => 482,
				'name'                    => 'Экспресс склад-дверь',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка докуентов и грузов по стандартны срока доставки. ез ограничений по весу',
			],
			[
				'code'                    => 483,
				'name'                    => 'Экспресс склад-склад',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка докуентов и грузов по стандартны срока доставки. ез ограничений по весу',
			],
			[
				'code'                    => 485,
				'name'                    => 'Экспресс дверь-постаат',
				'mode'                    => 'дверь-постаат (Д-П)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка докуентов и грузов по стандартны срока доставки. ез ограничений по весу',
			],
			[
				'code'                    => 486,
				'name'                    => 'Экспресс склад-постаат',
				'mode'                    => 'склад-постаат (С-П)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка докуентов и грузов по стандартны срока доставки. ез ограничений по весу',
			],
			[
				'code'                    => 605,
				'name'                    => 'Экспресс постаат-дверь',
				'mode'                    => 'постаат-дверь (П-Д)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка докуентов и грузов по стандартны срока доставки. ез ограничений по весу',
			],
			[
				'code'                    => 606,
				'name'                    => 'Экспресс постаат-склад',
				'mode'                    => 'постаат-склад (П-С)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка докуентов и грузов по стандартны срока доставки. ез ограничений по весу',
			],
			[
				'code'                    => 607,
				'name'                    => 'Экспресс постаат-постаат',
				'mode'                    => 'постаат-постаат (П-П)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка докуентов и грузов по стандартны срока доставки. ез ограничений по весу',
			],

			// Сборный груз
			[
				'code'                    => 748,
				'name'                    => 'Сборный груз дверь-дверь',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 70,
				'tariff_weight_limit_max' => 100,
				'tariff_service'          => 'Сборный груз',
				'tariff_description'      => 'Эконоичная назеная доставка сборных грузов',
			],
			[
				'code'                    => 749,
				'name'                    => 'Сборный груз дверь-склад',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 70,
				'tariff_weight_limit_max' => 100,
				'tariff_service'          => 'Сборный груз',
				'tariff_description'      => 'Эконоичная назеная доставка сборных грузов',
			],
			[
				'code'                    => 750,
				'name'                    => 'Сборный груз склад-дверь',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 70,
				'tariff_weight_limit_max' => 100,
				'tariff_service'          => 'Сборный груз',
				'tariff_description'      => 'Эконоичная назеная доставка сборных грузов',
			],
			[
				'code'                    => 751,
				'name'                    => 'Сборный груз склад-склад',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 70,
				'tariff_weight_limit_max' => 100,
				'tariff_service'          => 'Сборный груз',
				'tariff_description'      => 'Эконоичная назеная доставка сборных грузов',
			],

			[
				'code'                    => 676,
				'name'                    => 'Супер-экспресс до 10.00',
				'mode'                    => 'дверь-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 677,
				'name'                    => 'Супер-экспресс до 10.00',
				'mode'                    => 'дверь-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 678,
				'name'                    => 'Супер-экспресс до 10.00',
				'mode'                    => 'склад-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 679,
				'name'                    => 'Супер-экспресс до 10.00',
				'mode'                    => 'склад-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],

			[
				'code'                    => 686,
				'name'                    => 'Супер-экспресс до 12.00',
				'mode'                    => 'дверь-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 687,
				'name'                    => 'Супер-экспресс до 12.00',
				'mode'                    => 'дверь-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 688,
				'name'                    => 'Супер-экспресс до 12.00',
				'mode'                    => 'склад-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 689,
				'name'                    => 'Супер-экспресс до 12.00',
				'mode'                    => 'склад-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],

			[
				'code'                    => 696,
				'name'                    => 'Супер-экспресс до 14.00',
				'mode'                    => 'дверь-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 697,
				'name'                    => 'Супер-экспресс до 14.00',
				'mode'                    => 'дверь-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 698,
				'name'                    => 'Супер-экспресс до 14.00',
				'mode'                    => 'склад-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 699,
				'name'                    => 'Супер-экспресс до 14.00',
				'mode'                    => 'склад-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],

			[
				'code'                    => 706,
				'name'                    => 'Супер-экспресс до 16.00',
				'mode'                    => 'дверь-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 707,
				'name'                    => 'Супер-экспресс до 16.00',
				'mode'                    => 'дверь-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 708,
				'name'                    => 'Супер-экспресс до 16.00',
				'mode'                    => 'склад-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 709,
				'name'                    => 'Супер-экспресс до 16.00',
				'mode'                    => 'склад-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],

			[
				'code'                    => 716,
				'name'                    => 'Супер-экспресс до 18.00',
				'mode'                    => 'дверь-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 717,
				'name'                    => 'Супер-экспресс до 18.00',
				'mode'                    => 'дверь-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 718,
				'name'                    => 'Супер-экспресс до 18.00',
				'mode'                    => 'склад-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 719,
				'name'                    => 'Супер-экспресс до 18.00',
				'mode'                    => 'склад-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка докуентов и грузов «из рук в руки» к определенноу часу (доставка за 1-2 суток).',
			],

			[
				'code'                    => 533,
				'name'                    => 'СДЭК докуенты дверь-дверь',
				'mode'                    => 'Дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0.5,
				'tariff_service'          => 'СДЭК докуенты',
				'tariff_description'      => 'Экспресс доставка докуентов со спец. условие от 90 докуентов за 90 дней. Доступно только для заказов с типо "доставка".',
			],
			[
				'code'                    => 534,
				'name'                    => 'СДЭК докуенты дверь-склад',
				'mode'                    => 'Дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0.5,
				'tariff_service'          => 'СДЭК докуенты',
				'tariff_description'      => 'Экспресс доставка докуентов со спец. условие от 90 докуентов за 90 дней. Доступно только для заказов с типо "доставка".',
			],
			[
				'code'                    => 535,
				'name'                    => 'СДЭК докуенты склад-дверь',
				'mode'                    => 'Склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0.5,
				'tariff_service'          => 'СДЭК докуенты',
				'tariff_description'      => 'Экспресс доставка докуентов со спец. условие от 90 докуентов за 90 дней. Доступно только для заказов с типо "доставка".',
			],
			[
				'code'                    => 536,
				'name'                    => 'СДЭК докуенты склад-склад',
				'mode'                    => 'Склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0.5,
				'tariff_service'          => 'СДЭК докуенты',
				'tariff_description'      => 'Экспресс доставка докуентов со спец. условие от 90 докуентов за 90 дней. Доступно только для заказов с типо "доставка".',
			],

			[
				'code'                    => 2536,
				'name'                    => 'Один офис (М)',
				'mode'                    => 'Склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Один офис (М)',
				'tariff_description'      => 'Услуга «Один офис (М)» предоставляет возожность отправки и получения посылок в одно офисе, при условии, что офис отправителя и получателя совпадают.',
			],
			[
				'code'                    => 358,
				'name'                    => 'Фулфилент выдача',
				'mode'                    => 'Склад-склад (С-С)',
				'tariff_weight_limit_min' => null,
				'tariff_weight_limit_max' => null,
				'tariff_service'          => 'Фулфилент выдача',
				'tariff_description'      => 'Тариф предназначен для саовывоза/саозабора заказа со склада Фулфилента. При выборе данного тарифа ПВЗ должен совпадать с ПВЗ склада, на которо хранится груз и собран заказ.',
			],
		];
	}

	/**
	 * @param   string|null  $uuid         UUID заказа в СДЭК.
	 * @param   string|null  $cdek_number  Номер заказа СДЭК.
	 * @param   string|null  $im_number    Номер заказа в системе клиента.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 * @deprecated  Будет удалено в 2.0.0, используйте orders()->getOrderInfo() вместо него.
	 */
	public function getOrderInfo(?string $uuid = '', ?string $cdek_number = '', ?string $im_number = ''): array
	{
		return $this->orders()->getOrderInfo($uuid, $cdek_number, $im_number);
	}

	/**
	 * @return  array
	 *
	 * @since   1.0.0
	 * @deprecated  Будет удалено в 2.0.0, используйте calculator()->getAllTariffs() вместо него.
	 */
	public function getAlltariffs(): array
	{
		return $this->calculator()->getAllTariffs();
	}

	/**
	 *
	 * @param   string  $method          Метод REST API СДЭК
	 * @param   array   $data            массив данных запроса
	 * @param   string  $request_method  HTTP-метод: GET или POST
	 * @param   array   $curl_options    Дополнительные параметры CURL
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	public function getResponse(string $method = '', array $data = [], string $request_method = 'POST', array $curl_options = []): array
	{
		return $this->request->getResponse($method, $data, $request_method, $curl_options);
	}
}
