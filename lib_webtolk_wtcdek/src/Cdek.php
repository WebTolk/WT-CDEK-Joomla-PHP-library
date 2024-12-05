<?php
/**
 * Library to connect to CDEK service.
 * @package    WT Cdek library package
 * @author     Sergey Tolkachyov
 * @copyright   Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version     1.2.0
 * @license     GNU General Public License version 3 or later. Only for *.php files!
 * @link       https://web-tolk.ru
 */

declare(strict_types = 1);

namespace Webtolk\Cdekapi;
defined('_JEXEC') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\OutputController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Http\HttpFactory;
use Joomla\Http\Response;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;
use Webtolk\Cdekapi\CdekClientException;


final class Cdek
{
	/**
	 * @var string $token_type  Token type. Default 'Bearer'
	 * @since 1.0.0
	 */
	public static string $token_type = 'Bearer';
	/**
	 * @var int $expires_in  Token expires time
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
	 * System - WT Cdek plugin params
	 *
	 * @var array $plugin_params
	 * @since 1.0.0
	 */
	public static array $plugin_params = [];


	/**
	 * @param   bool|null    $test_mode Flag for test or production CDEK enviroment
	 * @param   string|null  $client_id Account
	 * @param   string|null  $client_secret Secret
	 */
	public function __construct(?bool $test_mode = false, ?string $client_id = '', ?string $client_secret = '')
	{
		self::$client_id     = $client_id ?? $client_id;
		self::$client_secret = $client_secret ?? $client_secret;
		self::$test_mode     = $test_mode ?? $test_mode;

		$lang         = Factory::getApplication()->getLanguage();
		$extension    = 'lib_webtolk_cdekapi';
		$base_dir     = JPATH_SITE;
		$lang->load($extension, $base_dir);
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
		/**
		 * Check if the library system plugin is enabled and credentials data are filled
		 */
		if (!$this->canDoRequest())
		{
			return [
				'error_code'    => 400,
				'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_GETRESPONSE_CANT_DO_REQUEST')
			];
		}

		// Setup host
		$request_uri = (self::$test_mode) ? self::$cdek_api_url_test : self::$cdek_api_url;
		$request_uri = new Uri($request_uri.$method);

		$options = new Registry();

		if(count($curl_options) > 0)
		{
			$options->set('transport.curl',$curl_options);
		}
		$headers = [
			'charset'       => 'UTF-8',
		];

		if($method == '/oauth/token')
		{
			// Тут мы получаем токен
			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
		} else {
			// А тут он уже есть
			if($request_method == 'POST')
			{
				$headers['Content-Type'] = 'application/json';
				$data = json_encode($data);
			}

			$this->loadTokenData();
		}

		if (!empty(self::$token))
		{
			$headers['Authorization'] = self::$token_type . ' ' . self::$token;
		}

		$http = (new HttpFactory)->getHttp($options, ['curl', 'stream']);
		try
		{
			if ($request_method != 'GET')
			{
				$request_method = strtolower($request_method);
				// $url, $data, $headers, $timeout
				$response = $http->$request_method($request_uri, $data, $headers, 30);
			}
			else
			{
				if (!empty($data))
				{
					$request_uri->setQuery($data);
				}

				// $url, $headers, $timeout
				$response = $http->get($request_uri, $headers, 30);
			}
			// Check the errors and make a human friendly message if errors are exists
			return $this->responseHandler($response, $method);

		} catch (CdekClientException $e){

			$this->saveToLog($e->getCode().' '.$e->getMessage(), 'error');
			return [
				'error_code'    => $e->getCode(),
				'error_message' => $e->getCode().' '.$e->getMessage()
			];
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
		if(self::$client_id && self::$client_secret)
		{
			return true;
		}

		$plugin_params = $this->getPluginParams();
		if (!$plugin_params->get('client_id','') || !$plugin_params->get('client_secret',''))
		{
			$this->saveToLog('There is no credentials found. Check theirs in plugin System - WT Cdek', 'WARNING');
			return false;
		}
		self::$client_id = trim($plugin_params->get('client_id'));
		self::$client_secret = trim($plugin_params->get('client_secret'));

		return true;
	}


	/**
	 * @param $response_data Response object
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 * @link       https://web-tolk.ru
	 */
	private  function responseHandler(Response $response, $method_name = ''): array
	{

		$error_array   = [
			'error_code'    => Text::_('PKG_LIB_WTCDEK_ERROR_RESPONSEHANDLER_NO_CODE'),
			'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_RESPONSEHANDLER_NO_ERROR_DESC')
		];
		$error_message = '';

		// We need in array here
		$body = (new Registry($response->body))->toArray();

		if ($response->code >= 400 && $response->code < 500)
		{
			// API работает. Ошибка отдается в json
			if (
				(array_key_exists('errors', $body) && !empty($body['errors']))
				||
				(array_key_exists('error', $body) && !empty($body['error']))
				||
				(
					(array_key_exists('requests', $body) && !empty($body['requests']))
					&&
					(array_key_exists('errors', $body['requests'][0]) || !empty($body['requests'][0]['errors']))
				)
			)
			{
				if ((array_key_exists('errors', $body) || !empty($body['errors'])))
				{
					$errors_array = $body['errors'][0];
				} else if(array_key_exists('error', $body) || !empty($body['error'])){
					$errors_array = $body;
				}else if(
					(array_key_exists('requests', $body) || !empty($body['requests'])) &&
					(array_key_exists('errors', $body['requests'][0]) || !empty($body['requests']['errors'][0]))
				) {
					$errors_array = $body['requests'][0]['errors'];
				}

				$error_message .= implode(', ', $errors_array);

			} else {
				$error_message = (string) $response->body;
			}

			$error_message = Text::sprintf(Text::_('PKG_LIB_WTCDEK_ERROR_RESPONSEHANDLER_ERROR_400'),
				(!empty($method_name) ? 'REST API method: '.$method_name.'.' . ':' : ''),
				$error_message
			);

			$this->saveToLog($error_message, 'ERROR');

			return [
				'error_code'    => 400,
				'error_message' => $error_message
			];
		}
		elseif ($response->code >= 500)
		{
			// API не работает, сервер лёг. В $response->body отдаётся HTML
			$this->saveToLog('Error while trying to calculate delivery cost via Cdek. Cdek API response: ' . print_r($body, true), 'ERROR');
			$error_array['error_code']    = $response->code;
			$error_array['error_message'] = Text::_('PKG_LIB_WTCDEK_ERROR_RESPONSEHANDLER_ERROR_500', print_r($body, true));
			return  $error_array;
		}

		return $body;
	}

	/**
	 * Грузим $token_data из кэша. Если просрочен - вызываем авторизацию заново.
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private  function loadTokenData(): void
	{
		if (!empty(self::$token) && !empty(self::$token_type) && !empty(self::$expires_in))
		{
			return;
		}

		$cache   = $this->getCache();
		$token_data = $cache->get('wt_cdek');

		/**
		 * Если есть файл кэша с данными токена, иначе авторизация
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
		 * Если текущая дата больше или равна времени окончания действия токена - получаем новый.
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
	 * Формат ответа JSON
	 * {
	 *    "access_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzY29wZSI6WyJvcmRlcjphbGw...",
	 *    "token_type": "bearer",
	 *    "expires_in": 3599,
	 *    "scope": "order:all payment:all",
	 *    "jti": "9adca50a-..."
	 *    }
	 *
	 * По истечении этого времени или при получении HTTP ошибки с кодом 401,
	 * вам нужно повторить процедуру получения access_token.
	 * В ином случае API будет отвечать с HTTP кодом 401 (unauthorized).
	 *
	 * @return mixed
	 * @since 1.0.0
	 * @link       https://web-tolk.ru
	 *
	 */
	private  function authorize(): array
	{
			$authorize_data = [
				'grant_type'    => 'client_credentials',
				'client_id'     => self::$client_id,
				'client_secret' => self::$client_secret,
			];

			try
			{
				$response      = $this->getResponse('/oauth/token', $authorize_data,'POST');
				if(!array_key_exists('access_token',$response))
				{
					$this->saveToLog(Text::_('PKG_LIB_WTCDEK_ERROR_AUTHORIZE_NO_TOKEN'),'ERROR');

					$error_array = [
						'error_code'    => 401,
						'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_AUTHORIZE_NO_TOKEN')
					];

					return $error_array;
				}
				$this->setToken($response['access_token']);

				if(array_key_exists('token_type', $response) && !empty($response['token_type']))
				{
					$this->setTokenType($response['token_type']);
				} else {
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
				 * Сохраняем токен в кэше. Жизнь кэша - 3600 секунд по умолчанию
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
	 * Function for to log library errors in lib_webtolk_cdekapi_cdek.log.php in
	 * Joomla log path. Default Log category lib_webtolk_cdekapi_cdek
	 *
	 * @param   string  $data      error message
	 * @param   string  $priority  Joomla Log priority
	 *
	 * @return void
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
		if($plugin_params->get('show_library_errors', 0) == 1)
		{
			Factory::getApplication()->enqueueMessage($data, $priority);
		}
		$priority = 'Log::' . $priority;
		Log::add($data, $priority, 'lib_webtolk_cdekapi_cdek');

	}

	/**
	 * Get plugin Syste - Wt cdek params
	 *
	 * @since 1.0.0
	 */
	private function getPluginParams() : Registry
	{
		if(!self::$plugin_params)
		{
			if (!PluginHelper::isEnabled('system', 'wtcdek'))
			{
				$this->saveToLog('Plugin System - WT Cdek is disabled', 'WARNING');
			}

			$plugin = PluginHelper::getPlugin('system', 'wtcdek');
			self::$plugin_params = (new Registry())->loadString($plugin->params)->toArray();
		}
		return new Registry(self::$plugin_params);
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
		 * Указываем время окончания действия токена.
		 *
		 */
		$date                        = Date::getInstance('now +' . $options['lifetime'] . ' minutes')->toUnix();
		$tokenData['token_end_time'] = $date;
		$cache                       = $this->getCache($options);
		$cache->store(json_encode($tokenData), 'wt_cdek');

	}


	/**
	 * Return the library pre-configured cache object
	 * @return OutputController
	 *
	 * @since 1.0.0
	 */
	public function getCache(array $cache_options = []) : OutputController
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
	 * Метод предназначен для получения списка действующих офисов СДЭК.
	 * Если одновременно указаны параметры city_code, postal_code, fias_guid, то для определения города всех стран присутствия СДЭК приоритет отдается city_code, затем fias_guid.
	 *
	 * @param   array  $request_options         Массив с описанными ниже опциями. Все необязтальные.
	 *                                          <ul>
	 *                                          <li>int|null     $postal_code       Почтовый индекс города, для которого необходим список офисов</li>
	 *                                          <li>int|null     $city_code         Код населенного пункта СДЭК (метод "Список населенных пунктов")</li>
	 *                                          <li>string|null  $type              Тип офиса, может принимать значения:
	 *                                          <ul>
	 *                                          <li>"PVZ" - для отображения складов СДЭК;</li>
	 *                                          <li>"POSTAMAT" - для отображения постаматов СДЭК;</li>
	 *                                          <li>"ALL" - для отображения всех ПВЗ независимо от их типа.</li>
	 *                                          <li>При отсутствии параметра принимается значение по умолчанию "ALL".</li>
	 *                                          </ul>
	 *                                          </li>
	 *                                          <li>string|null  $country_code      Код страны в формате ISO_3166-1_alpha-2 (см. “Общероссийский классификатор стран мира”)</li>
	 *                                          <li>int|null     $region_code       Код региона по базе СДЭК</li>
	 *                                          <li>bool|null    $have_cashless     Наличие терминала оплаты, может принимать значения: «1», «true» - есть; «0», «false» - нет.</li>
	 *                                          <li>bool|null    $have_cash         Есть прием наличных, может принимать значения: «1», «true» - есть;  «0», «false» - нет.</li>
	 *                                          <li>bool|null    $allowed_cod       Разрешен наложенный платеж, может принимать значения: «1», «true» - да;  «0», «false» - нет.</li>
	 *                                          <li>bool|null    $is_dressing_room  Наличие примерочной, может принимать значения: «1», «true» - есть;  «0», «false» - нет.</li>
	 *                                          <li>int|null     $weight_max        Максимальный вес в кг, который может принять офис</li>
	 *                                          (значения больше 0 - передаются офисы, которые принимают этот вес;
	 *                                          0 - офисы с нулевым весом не передаются;
	 *                                          значение не указано - все офисы).
	 *                                          <li>int|null     $weight_min        Минимальный вес в кг, который принимает офис (при переданном значении будут выводиться офисы с минимальным весом до указанного значения)</li>
	 *                                          <li>string       $lang              Локализация офиса. По умолчанию "rus".</li>
	 *                                          <li>bool|null    $take_only         Является ли офис только пунктом выдачи, может принимать значения: «1», «true» - да;  «0», «false» - нет.</li>
	 *                                          <li>bool|null    $is_handout        Является пунктом выдачи, может принимать значения: «1», «true» - да; «0», «false» - нет.</li>
	 *                                          <li>bool|null    $is_reception      Есть ли в офисе приём заказов, может принимать значения: «1», «true» - да; «0», «false» - нет.</li>
	 *                                          <li>string|null  $fias_guid         Код города ФИАС. Тип UUID.</li>
	 *                                          </ul>
	 *
	 * @return array|object
	 * @see       https://api-docs.cdek.ru/36982648.html
	 * @since 1.0.0
	 */
	public function getDeliveryPoints(array $request_options = []) : array
	{
		$options = [
			'postal_code'      => '',
			'city_code'       => '',
			'type'             => 'ALL',
			'country_code'     => '',
			'region_code'      => '',
			'have_cashless'    => '',
			'have_cash'        => '',
			'allowed_cod'      => '',
			'is_dressing_room' => '',
			'weight_max'       => '',
			'weight_min'       => '',
			'lang'             => 'rus',
			'take_only'        => '',
			'is_handout'       => '',
			'is_reception'     => '',
			'fias_guid'        => ''
		];

		$options = array_filter(array_merge($options, $request_options));

		$cache = $this->getCache();
		$cache_key = 'deliverypoints_'.md5(implode(',', $options));
		if (!$cache->contains($cache_key))
		{
			$deliverypoints = $this->getResponse('/deliverypoints', $options, 'GET');
			$cache->store($deliverypoints,$cache_key);
			return $deliverypoints;
		}

		return $cache->get($cache_key);

	}



	/**
	 * Список регионов
	 * Метод предназначен для получения детальной информации о регионах.
	 * Список регионов может быть ограничен характеристиками, задаваемыми пользователем.
	 * В список параметров запроса не добавлены параметры, помеченные устаревшими.
	 * Пример ответа:
	 *
	 * [
	 * {
	 * "country_code": "TR",
	 * "region": "Ыгдыр",
	 * "country": "Турция"
	 * },
	 * {
	 * "country_code": "CN",
	 * "region": "аймак Алашань",
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
	 * @param   array  $request_options  Массив с описанными ниже опциями. Все необязтальные.
	 *                                   - array|null   $country_codes    Массив кодов стран в формате  ISO_3166-1_alpha-2
	 *                                   - int|null     $size             Ограничение выборки результата. По умолчанию 1000. Обязателен, если указан page!
	 *                                   - int|null     $page             Номер страницы выборки результата. По умолчанию 0
	 *                                   - string|null  $lang             Локализация. По умолчанию "rus"
	 *
	 * @return array|object
	 * @see       https://api-docs.cdek.ru/33829418.html
	 * @since 1.0.0
	 */
	public function getLocationRegions(array $request_options = []) : array
	{

		$options = [
			'country_codes' => [],
			'size'          => 1000,
			'page'          => 0,
			'lang'          => 'rus'
		];
		$options = array_filter(array_merge($options, $request_options));
		if(!empty($options['country_codes']))
		{
			$options['country_codes'] = \implode(',', $options['country_codes']);
		}
		return $this->getResponse('/location/regions', $options, 'GET');
	}

	/**
	 * Список населенных пунктов
	 * Метод предназначен для получения детальной информации о населенных пунктах.
	 * Список населенных пунктов может быть ограничен характеристиками, задаваемыми пользователем.
	 * В список параметров запроса не добавлены параметры, помеченные устаревшими.
	 *
	 *
	 * @param   array  $request_options  Массив с описанными ниже опциями. Все необязтальные.
	 *  <ul>
	 *      <li>array|null   $country_codes    Массив кодов стран в формате  ISO_3166-1_alpha-2</li>
	 *      <li>int|null     $region_code      Код региона СДЭК</li>
	 *      <li>string|null  $fias_guid        Уникальный идентификатор ФИАС населенного пункта</li>
	 *      <li>string|null  $postal_code      Почтовый индекс</li>
	 *      <li>int|null     $code             Код населенного пункта СДЭК</li>
	 *      <li>string|null  $city             Название населенного пункта. Должно соответствовать полностью</li>
	 *      <li>int|null     $size             Ограничение выборки результата. По умолчанию 500. Обязателен, если указан page!</li>
	 *      <li>int|null     $page             Номер страницы выборки результата. По умолчанию 0</li>
	 *      <li>string|null  $lang             Локализация. По умолчанию "rus"</li>
	 *  </ul>
	 *
	 * @return array
	 * @see       https://api-docs.cdek.ru/33829437.html
	 * @since 1.0.0
	 */
	public function getLocationCities(array $request_options = []) : array
	{
		$options = [
			'country_codes' => [],
			'size'          => 500,
			'page'          => 0,
			'lang'          => 'rus'
		];
		$options = array_filter(array_merge($options, $request_options));
		if(array_key_exists('city', $options) && !empty($options['city']))
		{
			$options['city'] = urlencode($options['city']);
		}
		if(!empty($options['country_codes']))
		{
			$options['country_codes'] = \implode(',', $options['country_codes']);
		}
		return $this->getResponse('/location/cities', $options, 'GET');
	}


	/**
	 * Метод предназначен для получения списка почтовых индексов.
	 * (используется вместо метода "Список населённых пунктов")
	 *
	 * Запрос на получение списка населенных пунктов
	 *
	 * @param   int  $city_code Код города CDEK
	 *
	 * @return array|string[]
	 *
	 * @since 1.1.0
	 * @see https://api-docs.cdek.ru/133171036.html
	 */
	public function getLocationPostalCodes(int $city_code) : array
	{
		$options = [
			'code' => $city_code
		];
		return $this->getResponse('/location/postalcodes', $options, 'GET');
	}

	/**
	 * Калькулятор. Расчет по коду тарифа.
	 * Метод используется для расчета стоимости и сроков доставки по коду тарифа.
	 *
	 * Пример запроса:
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
	 * Пример ответа:
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
	 * @param   array  $request_options                                 Массив с описанными ниже опциями.
	 *                                                                  - array|null   $date                          Дата и время планируемой передачи заказа вида 2020-11-03T11:49:32+0700. По умолчанию - текущая
	 *                                                                  - int|null     $type                          Тип заказа (для проверки доступности тарифа и дополнительных услуг по типу заказа):
	 *                                                                  1 - "интернет-магазин"
	 *                                                                  2 - "доставка"
	 *                                                                  По умолчанию - 1
	 *                                                                  - int|null     $currency                      Валюта, в которой необходимо произвести расчет (подробнее см. приложение 1)
	 *                                                                  По умолчанию - валюта договора. https://api-docs.cdek.ru/63345430.html#id-%D0%9A%D0%B0%D0%BB%D1%8C%D0%BA%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80.%D0%A0%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BF%D0%BE%D0%BA%D0%BE%D0%B4%D1%83%D1%82%D0%B0%D1%80%D0%B8%D1%84%D0%B0-calc_currency1
	 *                                                                  - string       $tariff_code                   Обязательный параметр. Код тарифа (подробнее см. приложение 2) https://api-docs.cdek.ru/63345430.html#id-%D0%9A%D0%B0%D0%BB%D1%8C%D0%BA%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80.%D0%A0%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BF%D0%BE%D0%BA%D0%BE%D0%B4%D1%83%D1%82%D0%B0%D1%80%D0%B8%D1%84%D0%B0-calc_tariff1
	 *                                                                  - array        $from_location                 Обязательный парамтер. Адрес отправления.
	 *                                                                  Идентификация города производится по следующему алгоритму в порядке приоритетности:
	 *                                                                  1. По уникальному коду города СДЭК. Значения передаются в атрибутах from_location.code и to_location.code.
	 *                                                                  2. По почтовому индексу города. Значения передаются в атрибутах from_location.postal_code и to_location.postal_code. В качестве уточняющих параметров могут быть переданы код страны и наименование города.
	 *                                                                  3. По строке адреса. Значения передаются в атрибутах from_location.address и to_location.address.
	 *                                                                  - int          $from_location['code']          Код населенного пункта СДЭК (метод "Список населенных пунктов")
	 *                                                                  - string       $from_location['postal_code']  Почтовый индекс
	 *                                                                  - string       $from_location['country_code'] Код страны в формате  ISO_3166-1_alpha-2
	 *                                                                  - string       $from_location['city']         Название города
	 *                                                                  - string       $from_location['address']      Полная строка адреса
	 *                                                                  - array        $to_location                   Обязательный парамтер. Адрес отправления.
	 *                                                                  Идентификация города производится по следующему алгоритму в порядке приоритетности:
	 *                                                                  1. По уникальному коду города СДЭК. Значения передаются в атрибутах from_location.code и to_location.code.
	 *                                                                  2. По почтовому индексу города. Значения передаются в атрибутах from_location.postal_code и to_location.postal_code. В качестве уточняющих параметров могут быть переданы код страны и наименование города.
	 *                                                                  3. По строке адреса. Значения передаются в атрибутах from_location.address и to_location.address.
	 *                                                                  - int          $to_location['code']          Код населенного пункта СДЭК (метод "Список населенных пунктов")
	 *                                                                  - string       $to_location['postal_code']   Почтовый индекс
	 *                                                                  - string       $to_location['country_code']  Код страны в формате  ISO_3166-1_alpha-2
	 *                                                                  - string       $to_location['city']          Название города
	 *                                                                  - string       $to_location['address']       Полная строка адреса
	 *                                                                  - array        $services                     Дополнительные услуги
	 *                                                                  - string       $services['code']             Обязательный параметр, если передаются доп.услуги. Тип дополнительной услуги, код из справочника доп. услуг (подробнее см. приложение 3). https://api-docs.cdek.ru/63345430.html#id-%D0%9A%D0%B0%D0%BB%D1%8C%D0%BA%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80.%D0%A0%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BF%D0%BE%D0%BA%D0%BE%D0%B4%D1%83%D1%82%D0%B0%D1%80%D0%B8%D1%84%D0%B0-calc_service1
	 *                                                                  - string       $services['parameter']        Параметр дополнительной услуги:
	 *                                                                  1. количество для услуг PACKAGE_1, COURIER_PACKAGE_A2, SECURE_PACKAGE_A2, SECURE_PACKAGE_A3, SECURE_PACKAGE_A4, SECURE_PACKAGE_A5, CARTON_BOX_XS, CARTON_BOX_S, CARTON_BOX_M, CARTON_BOX_L, CARTON_BOX_500GR, CARTON_BOX_1KG, Фото документовCARTON_BOX_2KG, CARTON_BOX_3KG, CARTON_BOX_5KG, CARTON_BOX_10KG, CARTON_BOX_15KG, CARTON_BOX_20KG, CARTON_BOX_30KG, CARTON_FILLER (для всех типов заказа)
	 *                                                                  2. объявленная стоимость заказа для услуги INSURANCE (только для заказов с типом "доставка")
	 *                                                                  3. длина для услуг BUBBLE_WRAP, WASTE_PAPER
	 *                                                                  4. количество фотографий для услуги PHOTO_DOCUMENT
	 *                                                                  - array         $packages                   Обязательный параметр. Список информации по местам (упаковкам)
	 *                                                                  - int           $packages['weight']         Обязательный параметр. Общий вес (в граммах)
	 *                                                                  - int           $packages['length']         Габариты упаковки. Длина (в сантиметрах)
	 *                                                                  - int           $packages['width']          Габариты упаковки. Ширина (в сантиметрах)
	 *                                                                  - int           $packages['height']         Габариты упаковки. Высота (в сантиметрах)
	 *
	 * @return array
	 * @see      https://api-docs.cdek.ru/63345430.html
	 * @since 1.0.0
	 */
	public function getCalculatorTariff(array $request_options = []) : array
	{
		$request_options = array_filter($request_options);

		// tariff_code - обязательный параметр
		if (!$request_options['tariff_code'] || empty($request_options['tariff_code']))
		{
			$this->saveToLog(Text::_('PKG_LIB_WTCDEK_ERROR_GETCALCULATORTARIFF_NO_TARIFF_CODE'), 'ERROR');
			$error_array = array(
				'error_code'    => '500',
				'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_GETCALCULATORTARIFF_NO_TARIFF_CODE')
			);

			return $error_array;
		}

		// from_location - обязательный параметр
		if (
			(!$request_options['from_location'] || empty($request_options['from_location'])) || (
				(!$request_options['from_location']['code'] || empty($request_options['from_location']['code'])) &&
				(!$request_options['from_location']['postal_code'] || empty($request_options['from_location']['postal_code'])) &&
				(!$request_options['from_location']['country_code'] || empty($request_options['from_location']['country_code'])) &&
				(!$request_options['from_location']['city'] || empty($request_options['from_location']['city'])) &&
				(!$request_options['from_location']['address'] || empty($request_options['from_location']['address']))
			)
		)
		{
			$this->saveToLog(Text::_('PKG_LIB_WTCDEK_ERROR_GETCALCULATORTARIFF_FROM_LOCATION'), 'ERROR');
			$error_array = array(
				'error_code'    => '500',
				'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_GETCALCULATORTARIFF_FROM_LOCATION')
			);

			return $error_array;
		}

		// to_location - обязательный параметр
		if (
			(!$request_options['to_location'] || empty($request_options['to_location'])) || (
				(!$request_options['to_location']['code'] || empty($request_options['to_location']['code'])) &&
				(!$request_options['to_location']['postal_code'] || empty($request_options['to_location']['postal_code'])) &&
				(!$request_options['to_location']['country_code'] || empty($request_options['to_location']['country_code'])) &&
				(!$request_options['to_location']['city'] || empty($request_options['to_location']['city'])) &&
				(!$request_options['to_location']['address'] || empty($request_options['to_location']['address']))
			)
		)
		{
			$this->saveToLog(Text::_('PKG_LIB_WTCDEK_ERROR_GETCALCULATORTARIFF_TO_LOCATION'), 'ERROR');
			$error_array = [
				'error_code'    => '500',
				'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_GETCALCULATORTARIFF_TO_LOCATION')
			];

			return $error_array;
		}

		// packages - обязательный параметр
		if (!$request_options['packages'] || empty($request_options['packages']))
		{
			$this->saveToLog(Text::_('PKG_LIB_WTCDEK_ERROR_GETCALCULATORTARIFF_NO_PACKAGES'), 'ERROR');
			$error_array = [
				'error_code'    => '500',
				'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_GETCALCULATORTARIFF_NO_PACKAGES')
			];

			return $error_array;
		}
		else // weight - обязательный параметр в каждом package
		{
			foreach ($request_options['packages'] as $package)
			{
				if (!$package['weight'] || empty($package['weight']))
				{
					$this->saveToLog(Text::_('PKG_LIB_WTCDEK_ERROR_GETCALCULATORTARIFF_NO_PACKAGES_WEIGHT'), 'ERROR');
					$error_array = [
						'error_code'    => '500',
						'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_GETCALCULATORTARIFF_NO_PACKAGES_WEIGHT')
					];

					return $error_array;
				}
			}
		}

		return $this->getResponse('/calculator/tariff', $request_options, 'POST');

	}

	/**
	 * Калькулятор. Расчет по всем доступным тарифам.
	 * Метод используется клиентами для расчета стоимости и сроков доставки по всем доступным тарифам.
	 *
	 * Пример запроса:
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
	 * Пример ответа:
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
	 * @param   array  $request_options                                 Массив с описанными ниже опциями.
	 *                                                                  - array|null   $date                          Дата и время планируемой передачи заказа вида 2020-11-03T11:49:32+0700. По умолчанию - текущая
	 *                                                                  - int|null     $type                          Тип заказа (для проверки доступности тарифа и дополнительных услуг по типу заказа):
	 *                                                                  1 - "интернет-магазин"
	 *                                                                  2 - "доставка"
	 *                                                                  По умолчанию - 1
	 *                                                                  - int|null     $currency                      Валюта, в которой необходимо произвести расчет (подробнее см. приложение 1)
	 *                                                                  По умолчанию - валюта договора. https://api-docs.cdek.ru/63345430.html#id-%D0%9A%D0%B0%D0%BB%D1%8C%D0%BA%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80.%D0%A0%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BF%D0%BE%D0%BA%D0%BE%D0%B4%D1%83%D1%82%D0%B0%D1%80%D0%B8%D1%84%D0%B0-calc_currency1
	 *                                                                  - string       $tariff_code                   Обязательный параметр. Код тарифа (подробнее см. приложение 2) https://api-docs.cdek.ru/63345430.html#id-%D0%9A%D0%B0%D0%BB%D1%8C%D0%BA%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80.%D0%A0%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BF%D0%BE%D0%BA%D0%BE%D0%B4%D1%83%D1%82%D0%B0%D1%80%D0%B8%D1%84%D0%B0-calc_tariff1
	 *                                                                  - array        $from_location                 Обязательный парамтер. Адрес отправления.
	 *                                                                  Идентификация города производится по следующему алгоритму в порядке приоритетности:
	 *                                                                  1. По уникальному коду города СДЭК. Значения передаются в атрибутах from_location.code и to_location.code.
	 *                                                                  2. По почтовому индексу города. Значения передаются в атрибутах from_location.postal_code и to_location.postal_code. В качестве уточняющих параметров могут быть переданы код страны и наименование города.
	 *                                                                  3. По строке адреса. Значения передаются в атрибутах from_location.address и to_location.address.
	 *                                                                  - int          $from_location['code']          Код населенного пункта СДЭК (метод "Список населенных пунктов")
	 *                                                                  - string       $from_location['postal_code']  Почтовый индекс
	 *                                                                  - string       $from_location['country_code'] Код страны в формате  ISO_3166-1_alpha-2
	 *                                                                  - string       $from_location['city']         Название города
	 *                                                                  - string       $from_location['address']      Полная строка адреса
	 *                                                                  - array        $to_location                   Обязательный парамтер. Адрес отправления.
	 *                                                                  Идентификация города производится по следующему алгоритму в порядке приоритетности:
	 *                                                                  1. По уникальному коду города СДЭК. Значения передаются в атрибутах from_location.code и to_location.code.
	 *                                                                  2. По почтовому индексу города. Значения передаются в атрибутах from_location.postal_code и to_location.postal_code. В качестве уточняющих параметров могут быть переданы код страны и наименование города.
	 *                                                                  3. По строке адреса. Значения передаются в атрибутах from_location.address и to_location.address.
	 *                                                                  - int          $to_location['code']          Код населенного пункта СДЭК (метод "Список населенных пунктов")
	 *                                                                  - string       $to_location['postal_code']   Почтовый индекс
	 *                                                                  - string       $to_location['country_code']  Код страны в формате  ISO_3166-1_alpha-2
	 *                                                                  - string       $to_location['city']          Название города
	 *                                                                  - string       $to_location['address']       Полная строка адреса
	 *                                                                  - array        $services                     Дополнительные услуги
	 *                                                                  - string       $services['code']             Обязательный параметр, если передаются доп.услуги. Тип дополнительной услуги, код из справочника доп. услуг (подробнее см. приложение 3). https://api-docs.cdek.ru/63345430.html#id-%D0%9A%D0%B0%D0%BB%D1%8C%D0%BA%D1%83%D0%BB%D1%8F%D1%82%D0%BE%D1%80.%D0%A0%D0%B0%D1%81%D1%87%D0%B5%D1%82%D0%BF%D0%BE%D0%BA%D0%BE%D0%B4%D1%83%D1%82%D0%B0%D1%80%D0%B8%D1%84%D0%B0-calc_service1
	 *                                                                  - string       $services['parameter']        Параметр дополнительной услуги:
	 *                                                                  1. количество для услуг PACKAGE_1, COURIER_PACKAGE_A2, SECURE_PACKAGE_A2, SECURE_PACKAGE_A3, SECURE_PACKAGE_A4, SECURE_PACKAGE_A5, CARTON_BOX_XS, CARTON_BOX_S, CARTON_BOX_M, CARTON_BOX_L, CARTON_BOX_500GR, CARTON_BOX_1KG, Фото документовCARTON_BOX_2KG, CARTON_BOX_3KG, CARTON_BOX_5KG, CARTON_BOX_10KG, CARTON_BOX_15KG, CARTON_BOX_20KG, CARTON_BOX_30KG, CARTON_FILLER (для всех типов заказа)
	 *                                                                  2. объявленная стоимость заказа для услуги INSURANCE (только для заказов с типом "доставка")
	 *                                                                  3. длина для услуг BUBBLE_WRAP, WASTE_PAPER
	 *                                                                  4. количество фотографий для услуги PHOTO_DOCUMENT
	 *                                                                  - array         $packages                   Обязательный параметр. Список информации по местам (упаковкам)
	 *                                                                  - int           $packages['weight']         Обязательный параметр. Общий вес (в граммах)
	 *                                                                  - int           $packages['length']         Габариты упаковки. Длина (в сантиметрах)
	 *                                                                  - int           $packages['width']          Габариты упаковки. Ширина (в сантиметрах)
	 *                                                                  - int           $packages['height']         Габариты упаковки. Высота (в сантиметрах)
	 *
	 * @return array|object
	 * @see       https://api-docs.cdek.ru/63345519.html
	 * @since 1.0.0
	 */
	public function getCalculatorTarifflist(array $request_options = []):array
	{

		// from_location - обязательный параметр
		if (
			(!$request_options['from_location'] || empty($request_options['from_location'])) || (
				(!$request_options['from_location']['code'] || empty($request_options['from_location']['code'])) &&
				(!$request_options['from_location']['postal_code'] || empty($request_options['from_location']['postal_code'])) &&
				(!$request_options['from_location']['country_code'] || empty($request_options['from_location']['country_code'])) &&
				(!$request_options['from_location']['city'] || empty($request_options['from_location']['city'])) &&
				(!$request_options['from_location']['address'] || empty($request_options['from_location']['address']))
			)
		)
		{
			$this->saveToLog('Cdek->getCalculatorTarifflist. There is no from_location. Specify it, please.', 'ERROR');
			$error_array = [
				'error_code'    => '500',
				'error_message' => 'Cdek->getCalculatorTarifflist. There is no from_location. Specify it, please.'
			];

			return $error_array;
		}

		// to_location - обязательный параметр
		if (
			(!$request_options['to_location'] || empty($request_options['to_location'])) || (
				(!$request_options['to_location']['code'] || empty($request_options['to_location']['code'])) &&
				(!$request_options['to_location']['postal_code'] || empty($request_options['to_location']['postal_code'])) &&
				(!$request_options['to_location']['country_code'] || empty($request_options['to_location']['country_code'])) &&
				(!$request_options['to_location']['city'] || empty($request_options['to_location']['city'])) &&
				(!$request_options['to_location']['address'] || empty($request_options['to_location']['address']))
			)
		)
		{
			$this->saveToLog('Cdek->getCalculatorTarifflist. There is no to_location. Specify it, please.', 'ERROR');
			$error_array = [
				'error_code'    => '500',
				'error_message' => 'Cdek->getCalculatorTarifflist. There is no to_location. Specify it, please.'
			];

			return $error_array;
		}

		// packages - обязательный параметр
		if (!$request_options['packages'] || empty($request_options['packages']))
		{
			$this->saveToLog('Cdek->getCalculatorTarifflist. There is no packages array in request options array. Specify it, please.', 'ERROR');
			$error_array = [
				'error_code'    => '500',
				'error_message' => 'Cdek->getCalculatorTarifflist. There is no packages array in request options array. Specify it, please.'
			];

			return $error_array;
		}
		else // weight - обязательный параметр в каждом package
		{
			foreach ($request_options['packages'] as $package)
			{
				if (!$package['weight'] || empty($package['weight']))
				{
					$this->saveToLog('Cdek::getCalculatorTarifflist. There is no weight specified in one of your packages in request options array. Specify it, please.', 'ERROR');
					$error_array = array(
						'error_code'    => '500',
						'error_message' => 'Cdek::getCalculatorTarifflist. There is no weight specified in one of your packages in request options array. Specify it, please.'
					);

					return $error_array;
				}
			}
		}
		// we must to json_encode our data for POST requests
		return $this->getResponse('/calculator/tarifflist', $request_options, 'POST');

	}

	/**
	 * Подписка на вебхуки (Webhooks).
	 * Методы предназначены для управления подпиской на получение вебхуков на URL клиента.
	 * Так как тестовый аккаунт СДЭК является общим для всех клиентов, для тестирования вебхуков необходимо использовать только боевой URL СДЭК.
	 * В запросе на добавление подписки укажите свой тестовый URL, куда будут приходить вебхуки. После завершения тестирования поменяйте его на свой боевой URL.
	 * Если у клиента уже есть подписка с указанным типом, то старый url перезатирается на новый.
	 * Пример запроса:
	 * {
	 *      "url":"https://webhook.site",
	 *      "type":"ORDER_STATUS"
	 * }
	 *
	 * Пример ответа:
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
	 *                         - ORDER_STATUS - событие по статусам
	 *                         - PRINT_FORM - готовность печатной формы
	 *                         - DOWNLOAD_PHOTO  - получение фото документов по заказам
	 *
	 * @return array|object
	 * @see       https://api-docs.cdek.ru/29934408.html
	 * @since 1.0.0
	 */
	public function subscribeToWebhook(string $url, string $type):array
	{
		if (empty($url) || empty($type))
		{
			$this->saveToLog('Cdek::' . __FUNCTION__ . ': There is no $url or $type. Specify it, please.', 'ERROR');
			$error_array = [
				'error_code'    => '500',
				'error_message' => 'Cdek::' . __FUNCTION__ . ': There is no $url or $type. Specify it, please.'
			];

			return $error_array;
		}
		$request_options = [
			'url'  => $url,
			'type' => $type
		];
		// we must to json_encode our data for POST requests
		return $this->getResponse('/webhooks', $request_options, 'POST');
	}

	/**
	 * Запрос на регистрацию заказа.
	 *
	 * Пример запроса ("интернет-магазин"):
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
	 *      "address" : "ул. Блюхера, 32"
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
	 *      "name" : "Иванов Иван",
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
	 * Пример ответа ("интернет-магазин"):
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
	 * Пример ответа
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
	 * @param   array  $request_options  Массив параметров, описанных ниже
	 *
	 * @return array|object
	 * @see       https://api-docs.cdek.ru/29923926.html
	 * @since 1.0.0
	 */
	public function createOrder(array $request_options) : array
	{
		$required_fields = [
			'tariff_code',
			'recipient',
			'packages'
		];
		foreach ($required_fields as $requiredField){
			if (!array_key_exists($requiredField, $request_options))
			{
				$this->saveToLog('Cdek::' . __FUNCTION__ . ': There is no '.$requiredField.' in $request_options. Specify it, please.', 'ERROR');
				$error_array = [
					'error_code'    => '500',
					'error_message' => 'Cdek ' . __FUNCTION__ . ': There is no '.$requiredField.' $request_options. Specify it, please.'
				];

				return $error_array;
			}
		}

		return $this->getResponse('/orders', $request_options, 'POST');
	}

	/**
	 * Массив с тарифами CDEK для типа "интернет-магазин"
	 * @return array[] Tariff list array
	 *
	 * @since 1.0.0
	 */
	public function getTariffListShop(): array
	{
		/**
		 * - tariff code
		 * - tariff name
		 * - tariff mode
		 * - tariff_weight_limit
		 * - tariff_service
		 * - tariff_description
		 */
		return $tariff_shop = [
			[
				'code'                    => 7,
				'name'                    => 'Международный экспресс документы дверь-дверь',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 5,
				'tariff_service'          => 'Международный экспресс',
				'tariff_description'      => 'Экспресс-доставка за/из-за границы документов и писем',
			],
			[
				'code'                    => 8,
				'name'                    => 'Международный экспресс грузы дверь-дверь',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Международный экспресс',
				'tariff_description'      => 'Экспресс-доставка за/из-за границы грузов и посылок до 30 кг.',
			],
			[
				'code'                    => 136,
				'name'                    => 'Посылка склад-склад',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Посылка',
				'tariff_description'      => 'Услуга экономичной доставки товаров по России для компаний, осуществляющих дистанционную торговлю.',
			],
			[
				'code'                    => 137,
				'name'                    => 'Посылка склад-дверь',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Посылка',
				'tariff_description'      => 'Услуга экономичной доставки товаров по России для компаний, осуществляющих дистанционную торговлю.',
			],
			[
				'code'                    => 138,
				'name'                    => 'Посылка дверь-склад',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Посылка',
				'tariff_description'      => 'Услуга экономичной доставки товаров по России для компаний, осуществляющих дистанционную торговлю.',
			],
			[
				'code'                    => 139,
				'name'                    => 'Посылка дверь-дверь',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Посылка',
				'tariff_description'      => 'Услуга экономичной доставки товаров по России для компаний, осуществляющих дистанционную торговлю.',
			],
			[
				'code'                    => 231,
				'name'                    => 'Экономичная посылка дверь-дверь',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 50,
				'tariff_service'          => 'Экономичная посылка',
				'tariff_description'      => 'Услуга экономичной наземной доставки товаров по России для компаний, осуществляющих дистанционную торговлю. Услуга действует по направлениям из Москвы в подразделения СДЭК, находящиеся за Уралом и в Крым.',
			],
			[
				'code'                    => 232,
				'name'                    => 'Экономичная посылка дверь-склад',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 50,
				'tariff_service'          => 'Экономичная посылка',
				'tariff_description'      => 'Услуга экономичной наземной доставки товаров по России для компаний, осуществляющих дистанционную торговлю. Услуга действует по направлениям из Москвы в подразделения СДЭК, находящиеся за Уралом и в Крым.',
			],
			[
				'code'                    => 233,
				'name'                    => 'Экономичная посылка склад-дверь',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 50,
				'tariff_service'          => 'Экономичная посылка',
				'tariff_description'      => 'Услуга экономичной наземной доставки товаров по России для компаний, осуществляющих дистанционную торговлю. Услуга действует по направлениям из Москвы в подразделения СДЭК, находящиеся за Уралом и в Крым.',
			],
			[
				'code'                    => 234,
				'name'                    => 'Экономичная посылка склад-склад',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 50,
				'tariff_service'          => 'Экономичная посылка',
				'tariff_description'      => 'Услуга экономичной наземной доставки товаров по России для компаний, осуществляющих дистанционную торговлю. Услуга действует по направлениям из Москвы в подразделения СДЭК, находящиеся за Уралом и в Крым.',
			],
			[
				'code'                    => 291,
				'name'                    => 'E-com Express склад-склад',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 500,
				'tariff_service'          => 'E-com Express',
				'tariff_description'      => 'Самая быстрая экспресс-доставка в режиме авиа. Сервис по доставке товаров из-за рубежа с услугами по таможенному оформлению (услуги для компаний дистанционной торговли). Тариф доступен только для клиентов с типом Юридическое лицо.',
			],
			[
				'code'                    => 293,
				'name'                    => 'E-com Express дверь-дверь',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 500,
				'tariff_service'          => 'E-com Express',
				'tariff_description'      => 'Самая быстрая экспресс-доставка в режиме авиа. Сервис по доставке товаров из-за рубежа с услугами по таможенному оформлению (услуги для компаний дистанционной торговли). Тариф доступен только для клиентов с типом Юридическое лицо.',
			],
			[
				'code'                    => 294,
				'name'                    => 'E-com Express склад-дверь',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 500,
				'tariff_service'          => 'E-com Express',
				'tariff_description'      => 'Самая быстрая экспресс-доставка в режиме авиа. Сервис по доставке товаров из-за рубежа с услугами по таможенному оформлению (услуги для компаний дистанционной торговли). Тариф доступен только для клиентов с типом Юридическое лицо.',
			],
			[
				'code'                    => 295,
				'name'                    => 'E-com Express дверь-склад',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 500,
				'tariff_service'          => 'E-com Express',
				'tariff_description'      => 'Самая быстрая экспресс-доставка в режиме авиа. Сервис по доставке товаров из-за рубежа с услугами по таможенному оформлению (услуги для компаний дистанционной торговли). Тариф доступен только для клиентов с типом Юридическое лицо.',
			],
			[
				'code'                    => 509,
				'name'                    => 'E-com Express дверь-постамат',
				'mode'                    => 'Дверь-постамат (Д-П)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 500,
				'tariff_service'          => 'E-com Express',
				'tariff_description'      => 'Самая быстрая экспресс-доставка в режиме авиа. Сервис по доставке товаров из-за рубежа с услугами по таможенному оформлению (услуги для компаний дистанционной торговли). Тариф доступен только для клиентов с типом Юридическое лицо.',
			],
			[
				'code'                    => 510,
				'name'                    => 'E-com Express склад-постамат',
				'mode'                    => 'Склад-постамат (С-П)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 500,
				'tariff_service'          => 'E-com Express',
				'tariff_description'      => 'Самая быстрая экспресс-доставка в режиме авиа. Сервис по доставке товаров из-за рубежа с услугами по таможенному оформлению (услуги для компаний дистанционной торговли). Тариф доступен только для клиентов с типом Юридическое лицо.',
			],
			[
				'code'                    => 366,
				'name'                    => 'Посылка дверь-постамат',
				'mode'                    => 'дверь-постамат (Д-П)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Посылка',
				'tariff_description'      => 'Услуга экономичной доставки товаров по России для компаний, осуществляющих дистанционную торговлю.',
			],
			[
				'code'                    => 368,
				'name'                    => 'Посылка склад-постамат',
				'mode'                    => 'склад-постамат (С-П)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Посылка',
				'tariff_description'      => 'Услуга экономичной доставки товаров по России для компаний, осуществляющих дистанционную торговлю.',
			],
			[
				'code'                    => 378,
				'name'                    => 'Экономичная посылка склад-постамат',
				'mode'                    => 'склад-постамат (С-П)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 50,
				'tariff_service'          => 'Экономичная посылка',
				'tariff_description'      => 'Услуга экономичной наземной доставки товаров по России для компаний, осуществляющих дистанционную торговлю. Услуга действует по направлениям из Москвы в подразделения СДЭК, находящиеся за Уралом и в Крым',
			],
			[
				'code'                    => 184,
				'name'                    => 'E-com Standard дверь-дверь',
				'mode'                    => 'Дверь-дверь (Д-Д)	',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 50,
				'tariff_service'          => 'E-com Standard',
				'tariff_description'      => 'Стандартная экспресс-доставка. Сервис по доставке товаров из-за рубежа с услугами по таможенному оформлению (услуги для компаний дистанционной торговли). Тариф доступен только для клиентов с типом Юридическое лицо.Доступно для заказов с типом "ИМ" и "Доставка".',
			],[
				'code'                    => 185,
				'name'                    => 'E-com Standard склад-склад',
				'mode'                    => 'Склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 50,
				'tariff_service'          => 'E-com Standard',
				'tariff_description'      => 'Стандартная экспресс-доставка. Сервис по доставке товаров из-за рубежа с услугами по таможенному оформлению (услуги для компаний дистанционной торговли). Тариф доступен только для клиентов с типом Юридическое лицо.Доступно для заказов с типом "ИМ" и "Доставка".',
			],[
				'code'                    => 186,
				'name'                    => 'E-com Standard склад-дверь',
				'mode'                    => 'Склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 50,
				'tariff_service'          => 'E-com Standard',
				'tariff_description'      => 'Стандартная экспресс-доставка. Сервис по доставке товаров из-за рубежа с услугами по таможенному оформлению (услуги для компаний дистанционной торговли). Тариф доступен только для клиентов с типом Юридическое лицо.Доступно для заказов с типом "ИМ" и "Доставка".',
			],[
				'code'                    => 187,
				'name'                    => 'E-com Standard дверь-склад',
				'mode'                    => 'Дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 50,
				'tariff_service'          => 'E-com Standard',
				'tariff_description'      => 'Стандартная экспресс-доставка. Сервис по доставке товаров из-за рубежа с услугами по таможенному оформлению (услуги для компаний дистанционной торговли). Тариф доступен только для клиентов с типом Юридическое лицо.Доступно для заказов с типом "ИМ" и "Доставка".',
			],[
				'code'                    => 497,
				'name'                    => 'E-com Standard дверь-постамат',
				'mode'                    => 'Дверь-постамат (Д-П)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 50,
				'tariff_service'          => 'E-com Standard',
				'tariff_description'      => 'Стандартная экспресс-доставка. Сервис по доставке товаров из-за рубежа с услугами по таможенному оформлению (услуги для компаний дистанционной торговли). Тариф доступен только для клиентов с типом Юридическое лицо.Доступно для заказов с типом "ИМ" и "Доставка".',
			],[
				'code'                    => 498,
				'name'                    => 'E-com Standard склад-постамат',
				'mode'                    => 'Склад-постамат (С-П)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 50,
				'tariff_service'          => 'E-com Standard',
				'tariff_description'      => 'Стандартная экспресс-доставка. Сервис по доставке товаров из-за рубежа с услугами по таможенному оформлению (услуги для компаний дистанционной торговли). Тариф доступен только для клиентов с типом Юридическое лицо.Доступно для заказов с типом "ИМ" и "Доставка".',
			],[
				'code'                    => 19,
				'name'                    => 'Экономичный экспресс дверь-дверь',
				'mode'                    => 'Дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0,
				'tariff_service'          => 'Экономичный экспресс',
				'tariff_description'      => 'Индивидуальные ограничения в зависимости от направления.  Максимальные габариты одной стороны - 300 см. Экономичная доставка шин. Необходимо передавать дополнительный тип заказа "10" в поле additional_order_types.',
			],[
				'code'                    => 2321,
				'name'                    => 'Экономичный экспресс дверь-склад',
				'mode'                    => 'Дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0,
				'tariff_service'          => 'Экономичный экспресс',
				'tariff_description'      => 'Индивидуальные ограничения в зависимости от направления.  Максимальные габариты одной стороны - 300 см. Экономичная доставка шин. Необходимо передавать дополнительный тип заказа "10" в поле additional_order_types.',
			],[
				'code'                    => 2322,
				'name'                    => 'Экономичный экспресс склад-дверь',
				'mode'                    => 'Склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0,
				'tariff_service'          => 'Экономичный экспресс',
				'tariff_description'      => 'Индивидуальные ограничения в зависимости от направления.  Максимальные габариты одной стороны - 300 см. Экономичная доставка шин. Необходимо передавать дополнительный тип заказа "10" в поле additional_order_types.',
			],[
				'code'                    => 2323,
				'name'                    => 'Экономичный экспресс склад-склад',
				'mode'                    => 'Склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0,
				'tariff_service'          => 'Экономичный экспресс',
				'tariff_description'      => 'Индивидуальные ограничения в зависимости от направления.  Максимальные габариты одной стороны - 300 см. Экономичная доставка шин. Необходимо передавать дополнительный тип заказа "10" в поле additional_order_types.',
			]
		];
	}

	/**
	 * Массив с тарифами CDEK для типа "доставка"
	 * @return array tariff list array
	 *
	 * @since 1.0.0
	 */
	public function getTariffListDostavka(): array
	{
		return $tariff_dostavka = [
			[
				'code'                    => 3,
				'name'                    => 'Супер-экспресс до 18',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу.',
			],
			[
				'code'                    => 57,
				'name'                    => 'Супер-экспресс до 9',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 58,
				'name'                    => 'Супер-экспресс до 10',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 59,
				'name'                    => 'Супер-экспресс до 12',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 60,
				'name'                    => 'Супер-экспресс до 14',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 61,
				'name'                    => 'Супер-экспресс до 16',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 777,
				'name'                    => 'Супер-экспресс до 12',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 786,
				'name'                    => 'Супер-экспресс до 14',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 795,
				'name'                    => 'Супер-экспресс до 16',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 804,
				'name'                    => 'Супер-экспресс до 18',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 778,
				'name'                    => 'Супер-экспресс до 12',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 787,
				'name'                    => 'Супер-экспресс до 14',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 796,
				'name'                    => 'Супер-экспресс до 16',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 805,
				'name'                    => 'Супер-экспресс до 18',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 779,
				'name'                    => 'Супер-экспресс до 12',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 788,
				'name'                    => 'Супер-экспресс до 14',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 797,
				'name'                    => 'Супер-экспресс до 16',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 806,
				'name'                    => 'Супер-экспресс до 18',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за сутки).',
			],
			[
				'code'                    => 62,
				'name'                    => 'Магистральный экспресс склад-склад',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Экономичная доставка',
				'tariff_description'      => 'Быстрая экономичная доставка грузов по России',
			],
			[
				'code'                    => 63,
				'name'                    => 'Магистральный супер-экспресс склад-склад',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Экономичная доставка',
				'tariff_description'      => 'Быстрая экономичная доставка грузов по России',
			],
			[
				'code'                    => 121,
				'name'                    => 'Магистральный экспресс дверь-дверь',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Экономичная доставка',
				'tariff_description'      => 'Быстрая экономичная доставка грузов по России',
			],
			[
				'code'                    => 122,
				'name'                    => 'Магистральный экспресс склад-дверь',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Экономичная доставка',
				'tariff_description'      => 'Быстрая экономичная доставка грузов по России',
			],
			[
				'code'                    => 123,
				'name'                    => 'Магистральный экспресс дверь-склад',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Экономичная доставка',
				'tariff_description'      => 'Быстрая экономичная доставка грузов по России',
			],
			[
				'code'                    => 124,
				'name'                    => 'Магистральный супер-экспресс дверь-дверь',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Экономичная доставка',
				'tariff_description'      => 'Быстрая экономичная доставка грузов к определенному часу',
			],
			[
				'code'                    => 125,
				'name'                    => 'Магистральный супер-экспресс склад-дверь',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Экономичная доставка',
				'tariff_description'      => 'Быстрая экономичная доставка грузов к определенному часу',
			],
			[
				'code'                    => 126,
				'name'                    => 'Магистральный супер-экспресс дверь-склад',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Экономичная доставка',
				'tariff_description'      => 'Быстрая экономичная доставка грузов к определенному часу',
			],
			[
				'code'                    => 480,
				'name'                    => 'Экспресс дверь-дверь',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка документов и грузов по стандартным срокам доставки внутри страны (Россия, Белоруссия, Армения, Киргизия, Казахстан). Также действует по направлениям между странами таможенного союза (Россия, Белоруссия, Армения, Киргизия, Казахстан).Без ограничений по весу',
			],
			[
				'code'                    => 481,
				'name'                    => 'Экспресс дверь-склад',
				'mode'                    => 'дверь-склад (Д-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка документов и грузов по стандартным срокам доставки внутри страны (Россия, Белоруссия, Армения, Киргизия, Казахстан). Также действует по направлениям между странами таможенного союза (Россия, Белоруссия, Армения, Киргизия, Казахстан).Без ограничений по весу',
			],
			[
				'code'                    => 482,
				'name'                    => 'Экспресс склад-дверь',
				'mode'                    => 'склад-дверь (С-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка документов и грузов по стандартным срокам доставки внутри страны (Россия, Белоруссия, Армения, Киргизия, Казахстан). Также действует по направлениям между странами таможенного союза (Россия, Белоруссия, Армения, Киргизия, Казахстан).Без ограничений по весу',
			],
			[
				'code'                    => 483,
				'name'                    => 'Экспресс склад-склад',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка документов и грузов по стандартным срокам доставки внутри страны (Россия, Белоруссия, Армения, Киргизия, Казахстан). Также действует по направлениям между странами таможенного союза (Россия, Белоруссия, Армения, Киргизия, Казахстан).Без ограничений по весу',
			],
			[
				'code'                    => 485,
				'name'                    => 'Экспресс дверь-постамат',
				'mode'                    => 'дверь-постамат (Д-П)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка документов и грузов по стандартным срокам доставки внутри страны (Россия, Белоруссия, Армения, Киргизия, Казахстан). Также действует по направлениям между странами таможенного союза (Россия, Белоруссия, Армения, Киргизия, Казахстан).Без ограничений по весу',
			],
			[
				'code'                    => 486,
				'name'                    => 'Экспресс склад-постамат',
				'mode'                    => 'склад-постамат (С-П)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 0,
				'tariff_service'          => 'Экспресс',
				'tariff_description'      => 'Классическая экспресс-доставка документов и грузов по стандартным срокам доставки внутри страны (Россия, Белоруссия, Армения, Киргизия, Казахстан). Также действует по направлениям между странами таможенного союза (Россия, Белоруссия, Армения, Киргизия, Казахстан).Без ограничений по весу',
			],
			[
				'code'                    => 751,
				'name'                    => 'Сборный груз склад-склад',
				'mode'                    => 'склад-склад (С-С)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 20000,
				'tariff_service'          => 'Сборный груз',
				'tariff_description'      => 'Экономичная наземная доставка сборных грузов',
			],
			[
				'code'                    => 66,
				'name'                    => 'Доставка за 4 часа внутри города пешие',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 15,
				'tariff_service'          => 'Блиц-экспресс',
				'tariff_description'      => 'Доставка заказов от 0 до 15 кг пешими курьерами день в день по Москве и Санкт-Петербургу',
			],
			[
				'code'                    => 67,
				'name'                    => 'Доставка за 4 часа МСК-МО МО-МСК пешие',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 15,
				'tariff_service'          => 'Блиц-экспресс',
				'tariff_description'      => 'Доставка заказов от 0 до 15 кг пешими курьерами день в день по Москве и Московской области (до 10 км от МКАД)',
			],
			[
				'code'                    => 68,
				'name'                    => 'Доставка за 4 часа внутри города авто',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 15,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Блиц-экспресс',
				'tariff_description'      => 'Доставка заказов от 15 кг до 30 кг пешими курьерами день в день по Москве и Санкт-Петербургу ',
			],
			[
				'code'                    => 69,
				'name'                    => 'Доставка за 4 часа МСК-МО МО-МСК авто',
				'mode'                    => 'дверь-дверь (Д-Д)',
				'tariff_weight_limit_min' => 15,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Блиц-экспресс',
				'tariff_description'      => 'Доставка заказов от 15 кг до 30 кг пешими курьерами день в день по Москве и Московской области (до 10 км от МКАД)',
			],
			[
				'code'                    => 676,
				'name'                    => 'Супер-экспресс до 10.00',
				'mode'                    => 'дверь-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 677,
				'name'                    => 'Супер-экспресс до 10.00 ',
				'mode'                    => 'дверь-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 678,
				'name'                    => 'Супер-экспресс до 10.00 ',
				'mode'                    => 'склад-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 679,
				'name'                    => 'Супер-экспресс до 10.00 ',
				'mode'                    => 'склад-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 686,
				'name'                    => 'Супер-экспресс до 12.00 ',
				'mode'                    => 'дверь-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 687,
				'name'                    => 'Супер-экспресс до 12.00 ',
				'mode'                    => 'дверь-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 688,
				'name'                    => 'Супер-экспресс до 12.00 ',
				'mode'                    => 'склад-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 689,
				'name'                    => 'Супер-экспресс до 12.00 ',
				'mode'                    => 'склад-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 696,
				'name'                    => 'Супер-экспресс до 14.00 ',
				'mode'                    => 'дверь-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 697,
				'name'                    => 'Супер-экспресс до 14.00 ',
				'mode'                    => 'дверь-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 698,
				'name'                    => 'Супер-экспресс до 14.00 ',
				'mode'                    => 'склад-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 699,
				'name'                    => 'Супер-экспресс до 14.00 ',
				'mode'                    => 'склад-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 706,
				'name'                    => 'Супер-экспресс до 16.00 ',
				'mode'                    => 'дверь-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 707,
				'name'                    => 'Супер-экспресс до 16.00 ',
				'mode'                    => 'дверь-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 708,
				'name'                    => 'Супер-экспресс до 16.00 ',
				'mode'                    => 'склад-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 709,
				'name'                    => 'Супер-экспресс до 16.00 ',
				'mode'                    => 'склад-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 716,
				'name'                    => 'Супер-экспресс до 18.00 ',
				'mode'                    => 'дверь-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 717,
				'name'                    => 'Супер-экспресс до 18.00 ',
				'mode'                    => 'дверь-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 718,
				'name'                    => 'Супер-экспресс до 18.00 ',
				'mode'                    => 'склад-дверь',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
			[
				'code'                    => 719,
				'name'                    => 'Супер-экспресс до 18.00 ',
				'mode'                    => 'склад-склад',
				'tariff_weight_limit_min' => 0,
				'tariff_weight_limit_max' => 30,
				'tariff_service'          => 'Срочная доставка',
				'tariff_description'      => 'Срочная доставка документов и грузов «из рук в руки» по России к определенному часу (доставка за 1-2 суток).',
			],
		];
	}


	/**
	 * @param   string $uuid        идентификатор заказа в ИС СДЭК, по которому необходима информация
	 * @param   string $cdek_number номер заказа СДЭК, по которому необходима информация
	 * @param   string $im_number   номер заказа в ИС Клиента, по которому необходима информация
	 *
	 * @return mixed|object
	 *
	 * @since 1.0.0
	 * @see       https://api-docs.cdek.ru/29923975.html
	 */
	public function getOrderInfo(?string $uuid = '', ?string $cdek_number = '', ?string $im_number = ''):array
	{

		if(empty($uuid) && empty($cdek_number) && empty($im_number))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'There is no order UUID or Cdek order number or Joomla side order id specified'
			];
		}
		if (!empty($uuid))
		{
			$result = $this->getResponse('/orders/'.$uuid, [], 'GET');
		}

		if (!empty($cdek_number))
		{
			$result = $this->getResponse('/orders', ['cdek_number' => $cdek_number], 'GET');
		}

		if (!empty($im_number))
		{
			$result = $this->getResponse('/orders', ['im_number' => $im_number], 'GET');
		}
		return $result;
	}

}