<?php
/**
 * Транспортный помощник для клиента API СДЭК.
 *
 * @package    WT Cdek library package
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version    1.3.0
 * @license    GNU General Public License version 3 or later. Only for *.php files!
 * @link       https://web-tolk.ru
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi;

defined('_JEXEC') or die;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\OutputController;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Http\Response;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;
use Throwable;
use function array_filter;
use function array_key_exists;
use function array_merge;
use function defined;
use function implode;
use function is_array;
use function is_scalar;
use function is_string;
use function json_decode;
use function json_encode;
use function print_r;
use function strtolower;
use function strtoupper;
use function trim;

/**
 * Управляет транспортом API СДЭК, авторизацией и жизненным циклом токена.
 *
 * @since  1.2.1
 */
final class CdekRequest
{
	/**
	 * Базовый URL API боевой среды.
	 *
	 * @var    string
	 * @since  1.2.1
	 */
	private const CDEK_API_URL = 'https://api.cdek.ru/v2';

	/**
	 * Базовый URL API тестовой среды.
	 *
	 * @var    string
	 * @since  1.2.1
	 */
	private const CDEK_API_URL_TEST = 'https://api.edu.cdek.ru/v2';

	/**
	 * Публичный идентификатор тестового аккаунта из локальной документации API СДЭК.
	 *
	 * @var    string
	 * @since  1.3.1
	 */
	private const CDEK_TEST_CLIENT_ID = 'wqGwiQx0gg8mLtiEKsUinjVSICCjtTEP';

	/**
	 * Публичный секрет тестового аккаунта из локальной документации API СДЭК.
	 *
	 * @var    string
	 * @since  1.3.1
	 */
	private const CDEK_TEST_CLIENT_SECRET = 'RmAmgvSgSl1yirlz9QupbzOJVqhCxcP5';

	/**
	 * Кэшированные параметры плагина.
	 *
	 * @var    array<string, mixed>
	 * @since  1.2.1
	 */
	private static array $pluginParams = [];

	/**
	 * Токен доступа.
	 *
	 * @var    string
	 * @since  1.2.1
	 */
	private string $token = '';

	/**
	 * Тип токена.
	 *
	 * @var    string
	 * @since  1.2.1
	 */
	private string $tokenType = 'Bearer';

	/**
	 * Срок действия токена в секундах.
	 *
	 * @var    int
	 * @since  1.2.1
	 */
	private int $expiresIn = 0;

	/**
	 * Флаг тестового режима.
	 *
	 * @var    bool
	 * @since  1.2.1
	 */
	private bool $testMode = false;

	/**
	 * Идентификатор аккаунта клиента.
	 *
	 * @var    string
	 * @since  1.2.1
	 */
	private string $clientId = '';

	/**
	 * Секрет клиента.
	 *
	 * @var    string
	 * @since  1.2.1
	 */
	private string $clientSecret = '';

	/**
	 * @param   bool|null    $test_mode      Флаг тестового режима.
	 * @param   string|null  $client_id      Идентификатор аккаунта.
	 * @param   string|null  $client_secret  Секретный ключ.
	 */
	public function __construct(?bool $test_mode = false, ?string $client_id = '', ?string $client_secret = '')
	{
		$this->testMode     = (bool) ($test_mode ?? false);
		$this->clientId     = (string) ($client_id ?? '');
		$this->clientSecret = (string) ($client_secret ?? '');
		$this->applyDefaultTestCredentials();
	}

	/**
	 * Выполняет запрос к API СДЭК.
	 *
	 * @param   string  $method          Путь метода API.
	 * @param   array   $data            Параметры запроса.
	 * @param   string  $request_method  HTTP-метод.
	 * @param   array   $curl_options    Дополнительные параметры curl.
	 *
	 * @return  array
	 *
	 * @since   1.2.1
	 */
	public function getResponse(string $method = '', array $data = [], string $request_method = 'POST', array $curl_options = []): array
	{
		if (!$this->canDoRequest())
		{
			return [
				'error_code'    => 400,
				'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_GETRESPONSE_CANT_DO_REQUEST'),
			];
		}

		$requestUri = $this->getCdekHost();
		$requestUri->setPath($requestUri->getPath() . $method);

		$options = new Registry();

		if (!empty($curl_options))
		{
			$options->set('transport.curl', $curl_options);
		}

		$headers = ['charset' => 'UTF-8'];

		if ($method === '/oauth/token')
		{
			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
		}
		else
		{
			if (strtoupper($request_method) !== 'GET')
			{
				$headers['Content-Type'] = 'application/json';
				$data                    = json_encode($data);
			}

			if (!$this->loadTokenData())
			{
				return [
					'error_code'    => 401,
					'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_AUTHORIZE'),
				];
			}
		}

		if (!empty($this->token))
		{
			$headers['Authorization'] = $this->tokenType . ' ' . $this->token;
		}

		$http = (new HttpFactory())->getHttp($options, ['curl', 'stream']);

		try
		{
			$requestMethod = strtoupper($request_method);

			if ($requestMethod === 'GET')
			{
				if (!empty($data))
				{
					$requestUri->setQuery($data);
				}

				$response = $http->get($requestUri, $headers, 30);
			}
			elseif ($requestMethod === 'DELETE')
			{
				if (is_array($data) && !empty($data))
				{
					$requestUri->setQuery($data);
				}

				$response = $http->delete($requestUri, $headers, 30);
			}
			else
			{
				$methodName = strtolower($requestMethod);
				$response   = $http->$methodName($requestUri, $data, $headers, 30);
			}

			return $this->mapResponse($response, $method);
		}
		catch (Throwable $e)
		{
			$this->saveToLog($e->getCode() . ' ' . $e->getMessage(), 'ERROR');

			return [
				'error_code'    => (int) $e->getCode(),
				'error_message' => $e->getCode() . ' ' . $e->getMessage(),
			];
		}
	}

	/**
	 * Проверяет возможность выполнения запроса к API.
	 *
	 * @return  bool
	 *
	 * @since   1.2.1
	 */
	public function canDoRequest(): bool
	{
		$this->applyDefaultTestCredentials();

		if ($this->clientId !== '' && $this->clientSecret !== '')
		{
			return true;
		}

		$pluginParams = self::getPluginParams();

		if ($pluginParams === false)
		{
			return false;
		}

		$this->clientId     = trim((string) $pluginParams->get('client_id', ''));
		$this->clientSecret = trim((string) $pluginParams->get('client_secret', ''));
		$this->testMode     = (bool) $pluginParams->get('test_mode', $this->testMode);
		$this->applyDefaultTestCredentials();

		return $this->clientId !== '' && $this->clientSecret !== '';
	}

	/**
	 * Применяет публичные тестовые учетные данные СДЭК из документации, если включен тестовый режим
	 * и пользовательские учетные данные не заданы.
	 *
	 * @return  void
	 *
	 * @since   1.3.1
	 */
	private function applyDefaultTestCredentials(): void
	{
		if (!$this->testMode)
		{
			return;
		}

		if ($this->clientId !== '' && $this->clientSecret !== '')
		{
			return;
		}

		$this->clientId     = self::CDEK_TEST_CLIENT_ID;
		$this->clientSecret = self::CDEK_TEST_CLIENT_SECRET;
	}

	/**
	 * Возвращает параметры плагина или `false`, если плагин отключен.
	 *
	 * @return  Registry|false
	 *
	 * @since   1.2.1
	 */
	public static function getPluginParams()
	{
		if (!self::$pluginParams)
		{
			if (!PluginHelper::isEnabled('system', 'wtcdek'))
			{
				return false;
			}

			$plugin             = PluginHelper::getPlugin('system', 'wtcdek');
			self::$pluginParams = (new Registry())->loadString($plugin->params)->toArray();
		}

		return new Registry(self::$pluginParams);
	}

	/**
	 * Возвращает базовый URI API для выбранного окружения.
	 *
	 * @return  Uri
	 *
	 * @since   1.2.1
	 */
	private function getCdekHost(): Uri
	{
		return new Uri($this->testMode ? self::CDEK_API_URL_TEST : self::CDEK_API_URL);
	}

	/**
	 * Загружает данные токена из кэша и при необходимости обновляет их.
	 *
	 * @return  bool
	 *
	 * @since   1.2.1
	 */
	public function loadTokenData(): bool
	{
		if ($this->token !== '' && $this->tokenType !== '' && $this->expiresIn > 0)
		{
			return true;
		}

		$cache     = $this->getCache();
		$tokenData = $cache->get('wt_cdek');

		if (!empty($tokenData))
		{
			$tokenData = json_decode($tokenData);
		}
		else
		{
			$response = $this->authorize();

			if (isset($response['error_code']))
			{
				$this->saveToLog($response['error_code'] . ' - ' . $response['error_message'], 'ERROR');

				return false;
			}

			return $this->loadTokenData();
		}

		$date = (new Date())->toUnix();

		if (isset($tokenData->token_end_time) && $tokenData->token_end_time <= $date)
		{
			$cache->remove('wt_cdek');
			$this->authorize();

			return $this->loadTokenData();
		}

		$this->token     = (string) ($tokenData->token ?? '');
		$this->tokenType = (string) ($tokenData->token_type ?? 'Bearer');
		$this->expiresIn = (int) ($tokenData->expires_in ?? 3600);

		return $this->token !== '';
	}

	/**
	 * Возвращает настроенный контроллер кэша.
	 *
	 * @param   array  $cache_options
	 *
	 * @return  OutputController
	 *
	 * @since   1.2.1
	 */
	public function getCache(array $cache_options = []): OutputController
	{
		$config  = Factory::getContainer()->get('config');
		$options = [
			'defaultgroup' => 'wt_cdek',
			'caching'      => true,
			'cachebase'    => $config->get('cache_path'),
			'storage'      => $config->get('cache_handler'),
		];
		$options = array_merge($options, $cache_options);

		return Factory::getContainer()
			->get(CacheControllerFactoryInterface::class)
			->createCacheController('output', $options);
	}

	/**
	 * Авторизуется в API СДЭК и сохраняет данные токена.
	 *
	 * @return  array
	 *
	 * @since   1.2.1
	 */
	public function authorize(): array
	{
		$authorizeData = [
			'grant_type'    => 'client_credentials',
			'client_id'     => $this->clientId,
			'client_secret' => $this->clientSecret,
		];

		$response = $this->getResponse('/oauth/token', $authorizeData, 'POST');

		if (!array_key_exists('access_token', $response))
		{
			$error = [
				'error_code'    => 401,
				'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_AUTHORIZE_NO_TOKEN'),
			];
			$this->saveToLog($error['error_message'], 'ERROR');

			return $error;
		}

		$this->token     = (string) $response['access_token'];
		$this->tokenType = (string) ($response['token_type'] ?? 'Bearer');
		$this->expiresIn = (int) ($response['expires_in'] ?? 3600);

		$this->storeTokenData([
			'token'      => $this->token,
			'token_type' => $this->tokenType,
			'expires_in' => $this->expiresIn,
		]);

		return $response;
	}

	/**
	 * Записывает сообщение в лог библиотеки.
	 *
	 * @param   string  $data      Текст сообщения.
	 * @param   string  $priority  Уровень приоритета Joomla.
	 *
	 * @return  void
	 *
	 * @since   1.2.1
	 */
	public function saveToLog(string $data, string $priority = 'NOTICE'): void
	{
		Log::addLogger(
			['text_file' => 'lib_webtolk_cdekapi_cdek.log.php'],
			Log::ALL & ~Log::DEBUG,
			['lib_webtolk_cdekapi_cdek']
		);

		$params = self::getPluginParams();

		if ($params instanceof Registry && (int) $params->get('show_library_errors', 0) === 1)
		{
			Factory::getApplication()->enqueueMessage($data, $priority);
		}

		Log::add($data, 'Log::' . $priority, 'lib_webtolk_cdekapi_cdek');
	}

	/**
	 * Сохраняет данные токена в кэш Joomla.
	 *
	 * @param   array  $tokenData
	 *
	 * @return  void
	 *
	 * @since   1.2.1
	 */
	public function storeTokenData(array $tokenData): void
	{
		$lifetime                    = !empty($tokenData['expires_in']) ? (int) $tokenData['expires_in'] / 60 : 1;
		$date                        = Date::getInstance('now +' . $lifetime . ' minutes')->toUnix();
		$tokenData['token_end_time'] = $date;

		$cache = $this->getCache(['lifetime' => $lifetime]);
		$cache->store(json_encode($tokenData), 'wt_cdek');
	}

	/**
	 * Обрабатывает ответ и приводит ошибки к единому формату.
	 *
	 * @param   Response  $response    HTTP-ответ.
	 * @param   string    $methodName  Имя метода API.
	 *
	 * @return  array
	 *
	 * @since   1.2.1
	 */
	private function mapResponse(Response $response, string $methodName = ''): array
	{
		$errorArray   = [
			'error_code'    => Text::_('PKG_LIB_WTCDEK_ERROR_RESPONSEHANDLER_NO_CODE'),
			'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_RESPONSEHANDLER_NO_ERROR_DESC'),
		];
		$errorMessage = '';
		$body         = (new Registry($response->body))->toArray();

		if ($response->code >= 400 && $response->code < 500)
		{
			if (
				(array_key_exists('errors', $body) && !empty($body['errors']))
				|| (array_key_exists('error', $body) && !empty($body['error']))
				|| (
					(array_key_exists('requests', $body) && !empty($body['requests']))
					&& (array_key_exists('errors', $body['requests'][0]) || !empty($body['requests'][0]['errors']))
				)
			)
			{
				if (array_key_exists('errors', $body) && !empty($body['errors']))
				{
					$errorsArray = $body['errors'][0];
				}
				elseif (array_key_exists('error', $body) && !empty($body['error']))
				{
					$errorsArray = $body;
				}
				else
				{
					$errorsArray = $body['requests'][0]['errors'];
				}

				$errorMessage .= $this->normalizeErrors($errorsArray);
			}
			else
			{
				$errorMessage = (string) $response->body;
			}

			$errorMessage = Text::sprintf(
				Text::_('PKG_LIB_WTCDEK_ERROR_RESPONSEHANDLER_ERROR_400'),
				($methodName !== '' ? 'REST API method: ' . $methodName . ':' : ''),
				$errorMessage
			);
			$this->saveToLog($errorMessage, 'ERROR');

			return [
				'error_code'    => (int) $response->code,
				'error_message' => $errorMessage,
			];
		}

		if ($response->code >= 500)
		{
			$this->saveToLog(
				'Error while trying to calculate delivery cost via Cdek. Cdek Ответ API: ' . print_r($body, true),
				'ERROR'
			);
			$errorArray['error_code']    = (int) $response->code;
			$errorArray['error_message'] = Text::_('PKG_LIB_WTCDEK_ERROR_RESPONSEHANDLER_ERROR_500', print_r($body, true));

			return $errorArray;
		}

		return $body;
	}

	/**
	 * Преобразует структуру ошибок API в читаемую строку.
	 *
	 * @param   mixed  $errors  Данные ошибки.
	 *
	 * @return  string
	 *
	 * @since   1.3.0
	 */
	private function normalizeErrors($errors): string
	{
		if (!is_array($errors))
		{
			return (string) $errors;
		}

		$parts = [];

		foreach ($errors as $key => $value)
		{
			if (is_array($value))
			{
				$parts[] = $this->normalizeErrors($value);
			}
			elseif (is_scalar($value))
			{
				$parts[] = is_string($key) ? $key . ': ' . (string) $value : (string) $value;
			}
		}

		return implode(', ', array_filter($parts, static fn($part) => $part !== ''));
	}

	/**
	 * Возвращает идентификатор аккаунта.
	 *
	 * @return  string
	 *
	 * @since   1.2.1
	 */
	public function getClientId(): string
	{
		return $this->clientId;
	}

	/**
	 * Возвращает секрет аккаунта.
	 *
	 * @return  string
	 *
	 * @since   1.2.1
	 */
	public function getClientSecret(): string
	{
		return $this->clientSecret;
	}

	/**
	 * Устанавливает значение токена доступа.
	 *
	 * @param   string  $token  Токен доступа.
	 *
	 * @return  void
	 *
	 * @since   1.3.1
	 */
	public function setToken(string $token): void
	{
		$this->token = $token;
	}

	/**
	 * Устанавливает тип токена.
	 *
	 * @param   string  $tokenType  Тип токена.
	 *
	 * @return  void
	 *
	 * @since   1.3.1
	 */
	public function setTokenType(string $tokenType): void
	{
		$this->tokenType = $tokenType;
	}

	/**
	 * Устанавливает срок жизни токена в секундах.
	 *
	 * @param   int  $expiresIn  Срок жизни токена в секундах.
	 *
	 * @return  void
	 *
	 * @since   1.3.1
	 */
	public function setTokenExpiresIn(int $expiresIn): void
	{
		$this->expiresIn = $expiresIn;
	}

	/**
	 * Возвращает флаг тестового режима.
	 *
	 * @return  bool
	 *
	 * @since   1.2.1
	 */
	public function isTestMode(): bool
	{
		return $this->testMode;
	}

	/**
	 * Обрабатывает ответ и приводит ошибки к единому формату.
	 *
	 * @param   Response  $response    HTTP-ответ.
	 * @param   string    $methodName  Имя метода API.
	 *
	 * @return  array
	 *
	 * @since   1.2.1
	 */
	public function responseHandler(Response $response, string $methodName = ''): array
	{
		return $this->mapResponse($response, $methodName);
	}
}
