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
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Http\Response;
use Joomla\Registry\Registry;
use Webtolk\Cdekapi\Interfaces\EntityInterface;
use function is_array;
use function is_file;
use function preg_replace;
use function str_replace;
use function strtolower;
use function trim;

/**
 * Main CDEK API facade with magic accessors to entity handlers.
 *
 * @method  \Webtolk\Cdekapi\Entities\CalculatorEntity     calculator()     Returns calculator entity handler.
 * @method  \Webtolk\Cdekapi\Entities\CheckEntity          check()          Returns check entity handler.
 * @method  \Webtolk\Cdekapi\Entities\DeliveryEntity       delivery()       Returns delivery entity handler.
 * @method  \Webtolk\Cdekapi\Entities\DeliverypointsEntity deliverypoints() Returns delivery points entity handler.
 * @method  \Webtolk\Cdekapi\Entities\IntakesEntity        intakes()        Returns intakes entity handler.
 * @method  \Webtolk\Cdekapi\Entities\InternationalEntity  international()  Returns international entity handler.
 * @method  \Webtolk\Cdekapi\Entities\LocationEntity       location()       Returns location entity handler.
 * @method  \Webtolk\Cdekapi\Entities\OauthEntity          oauth()          Returns oauth entity handler.
 * @method  \Webtolk\Cdekapi\Entities\OrdersEntity         orders()         Returns orders entity handler.
 * @method  \Webtolk\Cdekapi\Entities\PassportEntity       passport()       Returns passport entity handler.
 * @method  \Webtolk\Cdekapi\Entities\PaymentEntity        payment()        Returns payment entity handler.
 * @method  \Webtolk\Cdekapi\Entities\PhotoDocumentEntity  photoDocument()  Returns photo document entity handler.
 * @method  \Webtolk\Cdekapi\Entities\PrealertEntity       prealert()       Returns prealert entity handler.
 * @method  \Webtolk\Cdekapi\Entities\PrintEntity          print()          Returns print entity handler.
 * @method  \Webtolk\Cdekapi\Entities\ReverseEntity        reverse()        Returns reverse entity handler.
 * @method  \Webtolk\Cdekapi\Entities\WebhooksEntity       webhooks()       Returns webhooks entity handler.
 *
 * @since   1.3.1
 */
final class Cdek
{
	/**
	 * @var string $token_type Token type. Default 'Bearer'
	 * @since 1.0.0
	 */
	public static string $token_type = 'Bearer';
	/**
	 * @var int $expires_in Token expires time
	 * @since 1.0.0
	 */
	public static int $expires_in;

	/**
	 * Production host
	 * @var string $cdek_api_url
	 * @since 1.0.0
	 */
	public static string $cdek_api_url = 'https://api.cdek.ru/v2';
	/**
	 * Testing host
	 * @var string $cdek_api_url_test
	 * @since 1.0.0
	 */
	public static string $cdek_api_url_test = 'https://api.edu.cdek.ru/v2';
	/**
	 * System - WT Cdek plugin params
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
	 * Account id
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
	 * Test mode
	 * @var bool $test_mode
	 * @since 1.0.0
	 */
	protected static bool $test_mode = false;
	/**
	 * Request helper for API calls.
	 *
	 * @var    CdekRequest
	 * @since  1.2.1
	 */
	private CdekRequest $request;

	/**
	 * Entity class map loaded from registry.
	 *
	 * @var    array<string, string>
	 * @since  1.2.1
	 */
	private array $entityMap = [];

	/**
	 * Created entity instances cache.
	 *
	 * @var    array<string, EntityInterface>
	 * @since  1.2.1
	 */
	private array $entities = [];


	/**
	 * @param   bool|null    $test_mode      Flag for test or production CDEK enviroment
	 * @param   string|null  $client_id      Account
	 * @param   string|null  $client_secret  Secret
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
	 * Check if CDEK library can do a request to CDEK via REST API
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function canDoRequest(): bool
	{
		return $this->request->canDoRequest();
	}

	/**
	 * Function for to log library errors in lib_webtolk_cdekapi_cdek.log.php in
	 * Joomla log path. Default Log category lib_webtolk_cdekapi_cdek
	 *
	 * @param   string  $data      error message
	 * @param   string  $priority  Joomla Log priority
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
	 * Get plugin System - WT Cdek params
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
	 * Returns request transport object.
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
	 * Proxy for entity access, e.g. $cdek->orders().
	 *
	 * @param   string  $name       Called method name.
	 * @param   array   $arguments  Method arguments.
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
	 * Returns entity instance by name.
	 *
	 * @param   string  $name  Entity key from registry.
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
	 * Resolves requested entity name to registry key.
	 *
	 * @param   string  $name  Requested name.
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
	 * Метод предназначен для получения списка действующих офисов СДЭК.
	 * Если одновреенно указаны параетры city_code, postal_code, fias_guid, то для определения города всех стран присутствия СДЭК приоритет отдается city_code, зате fias_guid.
	 *
	 * @param   array  $request_options         Массив с описанныи ниже опцияи. Все необязтальные.
	 *                                          <ul>
	 *                                          <li>int|null     $postal_code       Почтовый индекс города, для которого необходи список офисов</li>
	 *                                          <li>int|null     $city_code         Код населенного пункта СДЭК (етод "Список населенных пунктов")</li>
	 *                                          <li>string|null  $type              Тип офиса, ожет приниать значения:
	 *                                          <ul>
	 *                                          <li>"PVZ" - для отображения складов СДЭК;</li>
	 *                                          <li>"POSTAMAT" - для отображения постаатов СДЭК;</li>
	 *                                          <li>"ALL" - для отображения всех ПВЗ независио от их типа.</li>
	 *                                          <li>При отсутствии параетра приниается значение по уолчанию "ALL".</li>
	 *                                          </ul>
	 *                                          </li>
	 *                                          <li>string|null  $country_code      Код страны в форате ISO_3166-1_alpha-2 (с. “Общероссийский классификатор стран ира”)</li>
	 *                                          <li>int|null     $region_code       Код региона по базе СДЭК</li>
	 *                                          <li>bool|null    $have_cashless     Наличие теринала оплаты, ожет приниать значения: «1», «true» - есть; «0», «false» - нет.</li>
	 *                                          <li>bool|null    $have_cash         Есть прие наличных, ожет приниать значения: «1», «true» - есть;  «0», «false» - нет.</li>
	 *                                          <li>bool|null    $allowed_cod       Разрешен наложенный платеж, ожет приниать значения: «1», «true» - да;  «0», «false» - нет.</li>
	 *                                          <li>bool|null    $is_dressing_room  Наличие приерочной, ожет приниать значения: «1», «true» - есть;  «0», «false» - нет.</li>
	 *                                          <li>int|null     $weight_max        Максиальный вес в кг, который ожет принять офис</li>
	 *                                          (значения больше 0 - передаются офисы, которые приниают этот вес;
	 *                                          0 - офисы с нулевы весо не передаются;
	 *                                          значение не указано - все офисы).
	 *                                          <li>int|null     $weight_min        Миниальный вес в кг, который приниает офис (при переданно значении будут выводиться офисы с иниальны весо до указанного значения)</li>
	 *                                          <li>string       $lang              Локализация офиса. По уолчанию "rus".</li>
	 *                                          <li>bool|null    $take_only         Является ли офис только пункто выдачи, ожет приниать значения: «1», «true» - да;  «0», «false» - нет.</li>
	 *                                          <li>bool|null    $is_handout        Является пункто выдачи, ожет приниать значения: «1», «true» - да; «0», «false» - нет.</li>
	 *                                          <li>bool|null    $is_reception      Есть ли в офисе при заказов, ожет приниать значения: «1», «true» - да; «0», «false» - нет.</li>
	 *                                          <li>string|null  $fias_guid         Код города ФАС. Тип UUID.</li>
	 *                                          </ul>
	 *
	 * @return array|object
	 * @see       https://api-docs.cdek.ru/36982648.html
	 * @since     1.0.0
	 */
	public function getDeliveryPoints(array $request_options = []): array
	{
		return $this->deliverypoints()->getDeliveryPoints($request_options);
	}

	/**
	 * Return the library pre-configured cache object
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
	 * Список регионов
	 * Метод предназначен для получения детальной инфорации о регионах.
	 * Список регионов ожет быть ограничен характеристикаи, задаваеыи пользователе.
	 * В список параетров запроса не добавлены параетры, поеченные устаревшии.
	 * Приер ответа:
	 *
	 * [
	 * {
	 * "country_code": "TR",
	 * "region": "Ыгдыр",
	 * "country": "Турция"
	 * },
	 * {
	 * "country_code": "CN",
	 * "region": "айак Алашань",
	 * "country": "Китай (КНР)"
	 * },
	 * {
	 * "country_code": "RU",
	 * "region": "Тверская",
	 * "region_code": 50,
	 * "country": "Россия"
	 * }
	 * ]
	 *
	 * @param   array  $request_options  Массив с описанныи ниже опцияи. Все необязтальные.
	 *                                   - array|null   $country_codes    Массив кодов стран в форате  ISO_3166-1_alpha-2
	 *                                   - int|null     $size             Ограничение выборки результата. По уолчанию 1000. Обязателен, если указан page!
	 *                                   - int|null     $page             Ноер страницы выборки результата. По уолчанию 0
	 *                                   - string|null  $lang             Локализация. По уолчанию "rus"
	 *
	 * @return array|object
	 * @see       https://api-docs.cdek.ru/33829418.html
	 * @since     1.0.0
	 */
	public function getLocationRegions(array $request_options = []): array
	{
		return $this->location()->getRegions($request_options);
	}

	/**
	 * Список населенных пунктов
	 * Метод предназначен для получения детальной инфорации о населенных пунктах.
	 * Список населенных пунктов ожет быть ограничен характеристикаи, задаваеыи пользователе.
	 * В список параетров запроса не добавлены параетры, поеченные устаревшии.
	 *
	 *
	 * @param   array  $request_options  Массив с описанныи ниже опцияи. Все необязтальные.
	 *                                   <ul>
	 *                                   <li>array|null   $country_codes    Массив кодов стран в форате  ISO_3166-1_alpha-2</li>
	 *                                   <li>int|null     $region_code      Код региона СДЭК</li>
	 *                                   <li>string|null  $fias_guid        Уникальный идентификатор ФАС населенного пункта</li>
	 *                                   <li>string|null  $postal_code      Почтовый индекс</li>
	 *                                   <li>int|null     $code             Код населенного пункта СДЭК</li>
	 *                                   <li>string|null  $city             Название населенного пункта. Должно соответствовать полностью</li>
	 *                                   <li>int|null     $size             Ограничение выборки результата. По уолчанию 500. Обязателен, если указан page!</li>
	 *                                   <li>int|null     $page             Ноер страницы выборки результата. По уолчанию 0</li>
	 *                                   <li>string|null  $lang             Локализация. По уолчанию "rus"</li>
	 *                                   </ul>
	 *
	 * @return array
	 * @see       https://api-docs.cdek.ru/33829437.html
	 * @since     1.0.0
	 */
	public function getLocationCities(array $request_options = []): array
	{
		return $this->location()->getCities($request_options);
	}

	/**
	 * Метод предназначен для получения списка почтовых индексов.
	 * (используется весто етода "Список населнных пунктов")
	 *
	 * Запрос на получение списка населенных пунктов
	 *
	 * @param   int  $city_code  Код города CDEK
	 *
	 * @return array|string[]
	 *
	 * @since 1.1.0
	 * @see   https://api-docs.cdek.ru/133171036.html
	 */
	public function getLocationPostalCodes(int $city_code): array
	{
		return $this->location()->getPostalCodes($city_code);
	}

	/**
	 * Калькулятор. Расчет по коду тарифа.
	 * Метод используется для расчета стоиости и сроков доставки по коду тарифа.
	 *
	 * Приер запроса:
	 * {
	 * "type": "2",
	 * "date": "2020-11-03T11:49:32+0700",
	 * "currency": "1",
	 * "tariff_code": "11",
	 * "from_location": {
	 * "code": 270
	 * },
	 * "to_location": {
	 * "code": 44
	 * },
	 * "services": [
	 * {
	 * "code": "CARTON_BOX_XS",
	 * "parameter": "2"
	 * }
	 * ],
	 * "packages": [
	 * {
	 * "height": 10,
	 * "length": 10,
	 * "weight": 4000,
	 * "width": 10
	 * }
	 * ]
	 * }
	 *
	 * Приер ответа:
	 *
	 *{
	 * "period_min": 2,
	 * "currency": "RUB",
	 * "delivery_sum": 1040.0,
	 * "weight_calc": 4000,
	 * "services": [
	 * {
	 * "code": "CARTON_BOX_XS",
	 * "sum": 100.0
	 * }
	 * ],
	 * "period_max": 2,
	 * "total_sum": 1140.0
	 * }
	 *
	 * @param   array  $request_options                                 Массив с описанныи ниже опцияи.
	 *                                                                  - array|null   $date                          Дата и врея планируеой передачи заказа вида 2020-11-03T11:49:32+0700. По уолчанию - текущая
	 *                                                                  - int|null     $type                          Тип заказа (для проверки доступности тарифа и дополнительных услуг по типу заказа):
	 *                                                                  1 - "интернет-агазин"
	 *                                                                  2 - "доставка"
	 *                                                                  По уолчанию - 1
	 *                                                                  - int|null     $currency                      Валюта, в которой необходио произвести расчет (подробнее с. приложение 1)
	 *                                                                  По уолчанию - валюта договора. https://api-docs.cdek.ru/63345430.html#id-%D0%9A%D0%B0%D0%BB%D1%8C%D0%BA%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80.%D0%A0%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BF%D0%BE%D0%BA%D0%BE%D0%B4%D1%83%D1%82%D0%B0%D1%80%D0%B8%D1%84%D0%B0-calc_currency1
	 *                                                                  - string       $tariff_code                   Обязательный параетр. Код тарифа (подробнее с. приложение 2) https://api-docs.cdek.ru/63345430.html#id-%D0%9A%D0%B0%D0%BB%D1%8C%D0%BA%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80.%D0%A0%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BF%D0%BE%D0%BA%D0%BE%D0%B4%D1%83%D1%82%D0%B0%D1%80%D0%B8%D1%84%D0%B0-calc_tariff1
	 *                                                                  - array        $from_location                 Обязательный паратер. Адрес отправления.
	 *                                                                  дентификация города производится по следующеу алгориту в порядке приоритетности:
	 *                                                                  1. По уникальноу коду города СДЭК. Значения передаются в атрибутах from_location.code и to_location.code.
	 *                                                                  2. По почтовоу индексу города. Значения передаются в атрибутах from_location.postal_code и to_location.postal_code. В качестве уточняющих параетров огут быть переданы код страны и наиенование города.
	 *                                                                  3. По строке адреса. Значения передаются в атрибутах from_location.address и to_location.address.
	 *                                                                  - int          $from_location['code']          Код населенного пункта СДЭК (етод "Список населенных пунктов")
	 *                                                                  - string       $from_location['postal_code']  Почтовый индекс
	 *                                                                  - string       $from_location['country_code'] Код страны в форате  ISO_3166-1_alpha-2
	 *                                                                  - string       $from_location['city']         Название города
	 *                                                                  - string       $from_location['address']      Полная строка адреса
	 *                                                                  - array        $to_location                   Обязательный паратер. Адрес отправления.
	 *                                                                  дентификация города производится по следующеу алгориту в порядке приоритетности:
	 *                                                                  1. По уникальноу коду города СДЭК. Значения передаются в атрибутах from_location.code и to_location.code.
	 *                                                                  2. По почтовоу индексу города. Значения передаются в атрибутах from_location.postal_code и to_location.postal_code. В качестве уточняющих параетров огут быть переданы код страны и наиенование города.
	 *                                                                  3. По строке адреса. Значения передаются в атрибутах from_location.address и to_location.address.
	 *                                                                  - int          $to_location['code']          Код населенного пункта СДЭК (етод "Список населенных пунктов")
	 *                                                                  - string       $to_location['postal_code']   Почтовый индекс
	 *                                                                  - string       $to_location['country_code']  Код страны в форате  ISO_3166-1_alpha-2
	 *                                                                  - string       $to_location['city']          Название города
	 *                                                                  - string       $to_location['address']       Полная строка адреса
	 *                                                                  - array        $services                     Дополнительные услуги
	 *                                                                  - string       $services['code']             Обязательный параетр, если передаются доп.услуги. Тип дополнительной услуги, код из справочника доп. услуг (подробнее с. приложение 3). https://api-docs.cdek.ru/63345430.html#id-%D0%9A%D0%B0%D0%BB%D1%8C%D0%BA%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80.%D0%A0%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BF%D0%BE%D0%BA%D0%BE%D0%B4%D1%83%D1%82%D0%B0%D1%80%D0%B8%D1%84%D0%B0-calc_service1
	 *                                                                  - string       $services['parameter']        Параетр дополнительной услуги:
	 *                                                                  1. количество для услуг PACKAGE_1, COURIER_PACKAGE_A2, SECURE_PACKAGE_A2, SECURE_PACKAGE_A3, SECURE_PACKAGE_A4, SECURE_PACKAGE_A5, CARTON_BOX_XS, CARTON_BOX_S, CARTON_BOX_M, CARTON_BOX_L, CARTON_BOX_500GR, CARTON_BOX_1KG, Фото докуентовCARTON_BOX_2KG, CARTON_BOX_3KG, CARTON_BOX_5KG, CARTON_BOX_10KG, CARTON_BOX_15KG, CARTON_BOX_20KG, CARTON_BOX_30KG, CARTON_FILLER (для всех типов заказа)
	 *                                                                  2. объявленная стоиость заказа для услуги INSURANCE (только для заказов с типо "доставка")
	 *                                                                  3. длина для услуг BUBBLE_WRAP, WASTE_PAPER
	 *                                                                  4. количество фотографий для услуги PHOTO_DOCUMENT
	 *                                                                  - array         $packages                   Обязательный параетр. Список инфорации по еста (упаковка)
	 *                                                                  - int           $packages['weight']         Обязательный параетр. Общий вес (в граах)
	 *                                                                  - int           $packages['length']         Габариты упаковки. Длина (в сантиетрах)
	 *                                                                  - int           $packages['width']          Габариты упаковки. Ширина (в сантиетрах)
	 *                                                                  - int           $packages['height']         Габариты упаковки. Высота (в сантиетрах)
	 *
	 * @return array
	 * @see      https://api-docs.cdek.ru/63345430.html
	 * @since    1.0.0
	 */
	public function getCalculatorTariff(array $request_options = []): array
	{
		return $this->calculator()->calculateTariff($request_options);
	}

	/**
	 * Калькулятор. Расчет по все доступны тарифа.
	 * Метод используется клиентаи для расчета стоиости и сроков доставки по все доступны тарифа.
	 *
	 * Приер запроса:
	 * {
	 * "type": "2",
	 * "date": "2020-11-03T11:49:32+0700",
	 * "currency": "1",
	 * "tariff_code": "11",
	 * "from_location": {
	 * "code": 270
	 * },
	 * "to_location": {
	 * "code": 44
	 * },
	 * "services": [
	 * {
	 * "code": "CARTON_BOX_XS",
	 * "parameter": "2"
	 * }
	 * ],
	 * "packages": [
	 * {
	 * "height": 10,
	 * "length": 10,
	 * "weight": 4000,
	 * "width": 10
	 * }
	 * ]
	 * }
	 *
	 * Приер ответа:
	 *
	 *{
	 * "period_min": 2,
	 * "currency": "RUB",
	 * "delivery_sum": 1040.0,
	 * "weight_calc": 4000,
	 * "services": [
	 * {
	 * "code": "CARTON_BOX_XS",
	 * "sum": 100.0
	 * }
	 * ],
	 * "period_max": 2,
	 * "total_sum": 1140.0
	 * }
	 *
	 * @param   array  $request_options                                 Массив с описанныи ниже опцияи.
	 *                                                                  - array|null   $date                          Дата и врея планируеой передачи заказа вида 2020-11-03T11:49:32+0700. По уолчанию - текущая
	 *                                                                  - int|null     $type                          Тип заказа (для проверки доступности тарифа и дополнительных услуг по типу заказа):
	 *                                                                  1 - "интернет-агазин"
	 *                                                                  2 - "доставка"
	 *                                                                  По уолчанию - 1
	 *                                                                  - int|null     $currency                      Валюта, в которой необходио произвести расчет (подробнее с. приложение 1)
	 *                                                                  По уолчанию - валюта договора. https://api-docs.cdek.ru/63345430.html#id-%D0%9A%D0%B0%D0%BB%D1%8C%D0%BA%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80.%D0%A0%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BF%D0%BE%D0%BA%D0%BE%D0%B4%D1%83%D1%82%D0%B0%D1%80%D0%B8%D1%84%D0%B0-calc_currency1
	 *                                                                  - string       $tariff_code                   Обязательный параетр. Код тарифа (подробнее с. приложение 2) https://api-docs.cdek.ru/63345430.html#id-%D0%9A%D0%B0%D0%BB%D1%8C%D0%BA%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80.%D0%A0%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BF%D0%BE%D0%BA%D0%BE%D0%B4%D1%83%D1%82%D0%B0%D1%80%D0%B8%D1%84%D0%B0-calc_tariff1
	 *                                                                  - array        $from_location                 Обязательный паратер. Адрес отправления.
	 *                                                                  дентификация города производится по следующеу алгориту в порядке приоритетности:
	 *                                                                  1. По уникальноу коду города СДЭК. Значения передаются в атрибутах from_location.code и to_location.code.
	 *                                                                  2. По почтовоу индексу города. Значения передаются в атрибутах from_location.postal_code и to_location.postal_code. В качестве уточняющих параетров огут быть переданы код страны и наиенование города.
	 *                                                                  3. По строке адреса. Значения передаются в атрибутах from_location.address и to_location.address.
	 *                                                                  - int          $from_location['code']          Код населенного пункта СДЭК (етод "Список населенных пунктов")
	 *                                                                  - string       $from_location['postal_code']  Почтовый индекс
	 *                                                                  - string       $from_location['country_code'] Код страны в форате  ISO_3166-1_alpha-2
	 *                                                                  - string       $from_location['city']         Название города
	 *                                                                  - string       $from_location['address']      Полная строка адреса
	 *                                                                  - array        $to_location                   Обязательный паратер. Адрес отправления.
	 *                                                                  дентификация города производится по следующеу алгориту в порядке приоритетности:
	 *                                                                  1. По уникальноу коду города СДЭК. Значения передаются в атрибутах from_location.code и to_location.code.
	 *                                                                  2. По почтовоу индексу города. Значения передаются в атрибутах from_location.postal_code и to_location.postal_code. В качестве уточняющих параетров огут быть переданы код страны и наиенование города.
	 *                                                                  3. По строке адреса. Значения передаются в атрибутах from_location.address и to_location.address.
	 *                                                                  - int          $to_location['code']          Код населенного пункта СДЭК (етод "Список населенных пунктов")
	 *                                                                  - string       $to_location['postal_code']   Почтовый индекс
	 *                                                                  - string       $to_location['country_code']  Код страны в форате  ISO_3166-1_alpha-2
	 *                                                                  - string       $to_location['city']          Название города
	 *                                                                  - string       $to_location['address']       Полная строка адреса
	 *                                                                  - array        $services                     Дополнительные услуги
	 *                                                                  - string       $services['code']             Обязательный параетр, если передаются доп.услуги. Тип дополнительной услуги, код из справочника доп. услуг (подробнее с. приложение 3). https://api-docs.cdek.ru/63345430.html#id-%D0%9A%D0%B0%D0%BB%D1%8C%D0%BA%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80.%D0%A0%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BF%D0%BE%D0%BA%D0%BE%D0%B4%D1%83%D1%82%D0%B0%D1%80%D0%B8%D1%84%D0%B0-calc_service1
	 *                                                                  - string       $services['parameter']        Параетр дополнительной услуги:
	 *                                                                  1. количество для услуг PACKAGE_1, COURIER_PACKAGE_A2, SECURE_PACKAGE_A2, SECURE_PACKAGE_A3, SECURE_PACKAGE_A4, SECURE_PACKAGE_A5, CARTON_BOX_XS, CARTON_BOX_S, CARTON_BOX_M, CARTON_BOX_L, CARTON_BOX_500GR, CARTON_BOX_1KG, Фото докуентовCARTON_BOX_2KG, CARTON_BOX_3KG, CARTON_BOX_5KG, CARTON_BOX_10KG, CARTON_BOX_15KG, CARTON_BOX_20KG, CARTON_BOX_30KG, CARTON_FILLER (для всех типов заказа)
	 *                                                                  2. объявленная стоиость заказа для услуги INSURANCE (только для заказов с типо "доставка")
	 *                                                                  3. длина для услуг BUBBLE_WRAP, WASTE_PAPER
	 *                                                                  4. количество фотографий для услуги PHOTO_DOCUMENT
	 *                                                                  - array         $packages                   Обязательный параетр. Список инфорации по еста (упаковка)
	 *                                                                  - int           $packages['weight']         Обязательный параетр. Общий вес (в граах)
	 *                                                                  - int           $packages['length']         Габариты упаковки. Длина (в сантиетрах)
	 *                                                                  - int           $packages['width']          Габариты упаковки. Ширина (в сантиетрах)
	 *                                                                  - int           $packages['height']         Габариты упаковки. Высота (в сантиетрах)
	 *
	 * @return array|object
	 * @see       https://api-docs.cdek.ru/63345519.html
	 * @since     1.0.0
	 */
	public function getCalculatorTarifflist(array $request_options = []): array
	{
		return $this->calculator()->calculateTariffList($request_options);
	}

	/**
	 * Подписка на вебхуки (Webhooks).
	 * Методы предназначены для управления подпиской на получение вебхуков на URL клиента.
	 * Так как тестовый аккаунт СДЭК является общи для всех клиентов, для тестирования вебхуков необходио использовать только боевой URL СДЭК.
	 * В запросе на добавление подписки укажите свой тестовый URL, куда будут приходить вебхуки. После завершения тестирования поеняйте его на свой боевой URL.
	 * Если у клиента уже есть подписка с указанны типо, то старый url перезатирается на новый.
	 * Приер запроса:
	 * {
	 *      "url":"https://webhook.site",
	 *      "type":"ORDER_STATUS"
	 * }
	 *
	 * Приер ответа:
	 *
	 *{
	 *  "entity": {
	 *          "uuid": "73c65d02-51a9-4423-8ee8-cc662ec3eb85"
	 *      },
	 *  "requests": [
	 *      {
	 *          "request_uuid": "72753031-0e1b-4f1d-abcc-b0bb0bd6ab2f",
	 *          "type": "CREATE",
	 *          "state": "SUCCESSFUL",
	 *          "date_time": "2020-02-10T12:14:57+0700",
	 *          "errors": [],
	 *          "warnings": []
	 *      }
	 *  ]
	 * }
	 *
	 * @param   string  $url   URL, на который клиент хочет получать вебхуки
	 * @param   string  $type  Тип события:
	 *                         - ORDER_STATUS - событие по статуса
	 *                         - PRINT_FORM - готовность печатной форы
	 *                         - DOWNLOAD_PHOTO  - получение фото докуентов по заказа
	 *
	 * @return array|object
	 * @see       https://api-docs.cdek.ru/29934408.html
	 * @since     1.0.0
	 */
	public function subscribeToWebhook(string $url, string $type): array
	{
		return $this->webhooks()->subscribe($url, $type);
	}

	/**
	 * Запрос на регистрацию заказа.
	 *
	 * Приер запроса ("интернет-агазин"):
	 * {
	 *      "number" : "ddOererre7450813980068",
	 *      "comment" : "Новый заказ",
	 *      "delivery_recipient_cost" : {
	 *      "value" : 50
	 * },
	 * "delivery_recipient_cost_adv" :
	 *  [
	 *      {
	 *          "sum" : 3000,
	 *          "threshold" : 200
	 *      }
	 *  ],
	 * "from_location" : {
	 *       "code" : "44",
	 *      "fias_guid" : "",
	 *      "postal_code" : "",
	 *      "longitude" : "",
	 *      "latitude" : "",
	 *       "country_code" : "",
	 *      "region" : "",
	 *      "sub_region" : "",
	 *      "city" : "Москва",
	 *      "kladr_code" : "",
	 *      "address" : "пр. Ленинградский, д.4"
	 * },
	 * "to_location" : {
	 *      "code" : "270",
	 *       "fias_guid" : "",
	 *      "postal_code" : "",
	 *       "longitude" : "",
	 *      "latitude" : "",
	 *      "country_code" : "",
	 *      "region" : "",
	 *      "sub_region" : "",
	 *      "city" : "Новосибирск",
	 *      "kladr_code" : "",
	 *      "address" : "ул. люхера, 32"
	 * },
	 * "packages" :
	 * [
	 *  {
	 *    "number" : "bar-001",
	 *    "comment" : "Упаковка",
	 *    "height" : 10,
	 *    "items" :
	 *      [
	 *          {
	 *            "ware_key" : "00055",
	 *           "payment" : {
	 *                  "value" : 3000
	 *               },
	 *           "name" : "Товар",
	 *           "cost" : 300,
	 *           "amount" : 2,
	 *           "weight" : 700,
	 *           "url" : "www.item.ru"
	 *          }
	 *      ],
	 *   "length" : 10,
	 *   "weight" : 4000,
	 *   "width" : 10
	 *   }
	 *  ],
	 * "recipient" : {
	 *      "name" : "ванов ван",
	 *      "phones" :
	 *      [
	 *              {
	 *              "number" : "+79134637228"
	 *          }
	 *      ]
	 *  },
	 * "sender" : {
	 *      "name" : "Петров Петр"
	 *  },
	 * "services" : [
	 *      {
	 *          "code" : "SECURE_PACKAGE_A2"
	 *      }
	 *  ],
	 * "tariff_code" : 139
	 * }
	 *
	 * Приер ответа ("интернет-агазин"):
	 *
	 *{
	 *  "entity": {
	 *          "uuid": "73c65d02-51a9-4423-8ee8-cc662ec3eb85"
	 *      },
	 *  "requests": [
	 *      {
	 *          "request_uuid": "72753031-0e1b-4f1d-abcc-b0bb0bd6ab2f",
	 *          "type": "CREATE",
	 *          "state": "SUCCESSFUL",
	 *          "date_time": "2020-02-10T12:14:57+0700",
	 *          "errors": [],
	 *          "warnings": []
	 *      }
	 *  ]
	 * }
	 *
	 * Приер ответа
	 *
	 * {
	 *      "entity": {
	 *          "uuid": "72753031-4b5f-4084-9b09-c50b84a23da6"
	 *          },
	 *      "requests": [
	 *          {
	 *              "request_uuid": "72753031-5148-4a19-b233-e1eea7b10882",
	 *              "type": "CREATE",
	 *              "state": "ACCEPTED",
	 *              "date_time": "2020-02-10T11:10:34+0700",
	 *              "errors": [],
	 *              "warnings": []
	 *          }
	 *      ]
	 * }
	 *
	 * @param   array  $request_options  Массив параетров, описанных ниже
	 *
	 * @return array|object
	 * @see       https://api-docs.cdek.ru/29923926.html
	 * @since     1.0.0
	 */
	public function createOrder(array $request_options): array
	{
		return $this->orders()->createOrder($request_options);
	}

	/**
	 * Массив с тарифаи CDEK для типа "интернет-агазин"
	 * @return array[] Tariff list array
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
	 * @return array tariff list array
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
	 * @param   string  $uuid         идентификатор заказа в С СДЭК, по котороу необходиа инфорация
	 * @param   string  $cdek_number  ноер заказа СДЭК, по котороу необходиа инфорация
	 * @param   string  $im_number    ноер заказа в С Клиента, по котороу необходиа инфорация
	 *
	 * @return mixed|object
	 *
	 * @since     1.0.0
	 * @see       https://api-docs.cdek.ru/29923975.html
	 */
	public function getOrderInfo(?string $uuid = '', ?string $cdek_number = '', ?string $im_number = ''): array
	{
		return $this->orders()->getOrderInfo($uuid, $cdek_number, $im_number);
	}

	public function getAlltariffs(): array
	{
		return $this->calculator()->getAllTariffs();
	}

	/**
	 * @param $response_data Response object
	 *
	 * @return array
	 *
	 * @since      1.0.0
	 * @link       https://web-tolk.ru
	 */
	private function responseHandler(Response $response, $method_name = ''): array
	{
		return $this->request->responseHandler($response, (string) $method_name);
	}

	/**
	 * Грузи $token_data из кэша. Если просрочен - вызывае авторизацию заново.
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function loadTokenData(): void
	{
		if (!empty(self::$token) && !empty(self::$token_type) && !empty(self::$expires_in))
		{
			return;
		}

		$cache      = $this->getCache();
		$token_data = $cache->get('wt_cdek');

		/**
		 * Если есть файл кэша с данныи токена, иначе авторизация
		 */

		if ($token_data)
		{
			$token_data = json_decode($token_data);
		}
		else
		{
			/** @var array $response */
			$response = $this->authorize();
			if (isset($response['error_code']))
			{
				$this->saveToLog($response['error_code'] . ' - ' . $response['error_message'], 'ERROR');

				return;

			}
			else
			{
				$this->loadTokenData();
			}
		}

		$date = Date::getInstance('now')->toUnix();
		/**
		 * Если текущая дата больше или равна вреени окончания действия токена - получае новый.
		 */

		if ($token_data->token_end_time <= $date)
		{
			$cache->remove('wt_cdek');
			$this->authorize();
			$this->loadTokenData();
		}
		else
		{

			$this->setToken($token_data->token);
			$this->setTokenType($token_data->token_type);
		}
	}

	/**
	 * Получение токена
	 * Форат ответа JSON
	 * {
	 *    "access_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzY29wZSI6WyJvcmRlcjphbGw...",
	 *    "token_type": "bearer",
	 *    "expires_in": 3599,
	 *    "scope": "order:all payment:all",
	 *    "jti": "9adca50a-..."
	 *    }
	 *
	 * По истечении этого вреени или при получении HTTP ошибки с кодо 401,
	 * ва нужно повторить процедуру получения access_token.
	 * В ино случае API будет отвечать с HTTP кодо 401 (unauthorized).
	 *
	 * @return mixed
	 * @since      1.0.0
	 * @link       https://web-tolk.ru
	 *
	 */
	private function authorize(): array
	{
		$authorize_data = [
			'grant_type'    => 'client_credentials',
			'client_id'     => self::$client_id,
			'client_secret' => self::$client_secret,
		];

		try
		{
			$response = $this->getResponse('/oauth/token', $authorize_data, 'POST');
			if (!array_key_exists('access_token', $response))
			{
				$this->saveToLog(Text::_('PKG_LIB_WTCDEK_ERROR_AUTHORIZE_NO_TOKEN'), 'ERROR');

				$error_array = [
					'error_code'    => 401,
					'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_AUTHORIZE_NO_TOKEN')
				];

				return $error_array;
			}
			$this->setToken($response['access_token']);

			if (array_key_exists('token_type', $response) && !empty($response['token_type']))
			{
				$this->setTokenType($response['token_type']);
			}
			else
			{
				$this->setTokenType('Bearer');
			}

			/**
			 * Set token expires period. 3600 by default
			 * @see https://api-docs.cdek.ru/29923918.html
			 */
			if (!$response['expires_in'])
			{
				$this->setTokenExpiresIn(3600);
			}
			else
			{
				$this->setTokenExpiresIn($response['expires_in']);
			}

			/**
			 * Сохраняе токен в кэше. Жизнь кэша - 3600 секунд по уолчанию
			 * или же значение, равное $response_body->expires_in
			 */
			$this->storeTokenData([
				'token'      => $response['access_token'],
				'token_type' => $response['token_type'],
				'expires_in' => $response['expires_in'],
			]);

			return $response;

		}
		catch (CdekClientException $e)
		{
			throw new CdekClientException(Text::_('PKG_LIB_WTCDEK_ERROR_AUTHORIZE'), 500, $e);
		}

	}

	/**
	 *
	 * @param   string  $method          CDEK REST API method
	 * @param   array   $data            array of data for Moodle REST API
	 * @param   string  $request_method  HTTP method: GET or POST
	 * @param   array   $curl_options    Additional options for CURL
	 * @param   string  $environment     test or production
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	public function getResponse(string $method = '', array $data = [], string $request_method = 'POST', array $curl_options = []): array
	{
		return $this->request->getResponse($method, $data, $request_method, $curl_options);
	}

	/**
	 * Set token from Cdek API response to self::$token
	 *
	 * @param   string  $token  token from Cdek API reponse
	 *
	 *
	 * @since 1.0.0
	 * @retun void
	 */
	public function setToken(string $token): void
	{
		self::$token = $token;
	}

	/**
	 * Set token type from Cdek API response to self::$token_type
	 *
	 * @param   string  $token_type  Token type from Cdek API response
	 *
	 *
	 * @since 1.0.0
	 * @retun void
	 */
	public function setTokenType(string $token_type): void
	{
		self::$token_type = $token_type;
	}

	/**
	 * Set token expires period (in seconds) from Cdek API response to self::$token_expires_in
	 *
	 * @param   int  $token_expires_in
	 *
	 *
	 * @since 1.0.0
	 * @retun void
	 */
	public function setTokenExpiresIn(int $token_expires_in): void
	{
		self::$expires_in = $token_expires_in;
	}

	/**
	 * Stores token data to Joomla Cache
	 *
	 * @param   array  $tokenData  Access token, token type, token expires in (seconds), token start time in Unix format
	 *
	 *
	 * @since 1.0.0
	 * @retun void
	 */
	public function storeTokenData(array $tokenData): void
	{
		$options = [];

		// 3600 seconds token lifetime by default - 6 minutes
		if ($tokenData['expires_in'])
		{
			$options['lifetime'] = (int) $tokenData['expires_in'] / 60;
		}
		else
		{
			$options['lifetime'] = 1;
		}

		/**
		 * Указывае врея окончания действия токена.
		 *
		 */
		$date                        = Date::getInstance('now +' . $options['lifetime'] . ' minutes')->toUnix();
		$tokenData['token_end_time'] = $date;
		$cache                       = $this->getCache($options);
		$cache->store(json_encode($tokenData), 'wt_cdek');

	}

}
