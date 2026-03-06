<?php
/**
 * @package       WT Cdek library package
 * @version       1.3.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         1.0.0
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi;

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Webtolk\Cdekapi\Traits\CacheTrait;
use Webtolk\Cdekapi\Traits\LogTrait;
use function array_filter;
use function array_key_exists;
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
 * РЈРїСЂР°РІР»СЏРµС‚ С‚СЂР°РЅСЃРїРѕСЂС‚РѕРј API РЎР”Р­Рљ, Р°РІС‚РѕСЂРёР·Р°С†РёРµР№ Рё Р¶РёР·РЅРµРЅРЅС‹Рј С†РёРєР»РѕРј С‚РѕРєРµРЅР°.
 *
 * @since 1.3.0
 */
final class CdekRequest
{
    use CacheTrait;
    use LogTrait;

    /**
     * Р‘Р°Р·РѕРІС‹Р№ URL API Р±РѕРµРІРѕР№ СЃСЂРµРґС‹.
     *
     * @var    string
     * @since 1.3.0
     */
    private const CDEK_API_URL = 'https://api.cdek.ru/v2';

    /**
     * Р‘Р°Р·РѕРІС‹Р№ URL API С‚РµСЃС‚РѕРІРѕР№ СЃСЂРµРґС‹.
     *
     * @var    string
     * @since 1.3.0
     */
    private const CDEK_API_URL_TEST = 'https://api.edu.cdek.ru/v2';

    /**
     * РџСѓР±Р»РёС‡РЅС‹Р№ РёРґРµРЅС‚РёС„РёРєР°С‚РѕСЂ С‚РµСЃС‚РѕРІРѕРіРѕ Р°РєРєР°СѓРЅС‚Р° РёР· Р»РѕРєР°Р»СЊРЅРѕР№ РґРѕРєСѓРјРµРЅС‚Р°С†РёРё API РЎР”Р­Рљ.
     *
     * @var    string
     * @since 1.3.0
     */
    private const CDEK_TEST_CLIENT_ID = 'wqGwiQx0gg8mLtiEKsUinjVSICCjtTEP';

    /**
     * РџСѓР±Р»РёС‡РЅС‹Р№ СЃРµРєСЂРµС‚ С‚РµСЃС‚РѕРІРѕРіРѕ Р°РєРєР°СѓРЅС‚Р° РёР· Р»РѕРєР°Р»СЊРЅРѕР№ РґРѕРєСѓРјРµРЅС‚Р°С†РёРё API РЎР”Р­Рљ.
     *
     * @var    string
     * @since 1.3.0
     */
    private const CDEK_TEST_CLIENT_SECRET = 'RmAmgvSgSl1yirlz9QupbzOJVqhCxcP5';

    /**
     * РљСЌС€РёСЂРѕРІР°РЅРЅС‹Рµ РїР°СЂР°РјРµС‚СЂС‹ РїР»Р°РіРёРЅР°.
     *
     * @var    array<string, mixed>
     * @since 1.3.0
     */
    private static array $pluginParams = [];

    /**
     * РўРѕРєРµРЅ РґРѕСЃС‚СѓРїР°.
     *
     * @var    string
     * @since 1.3.0
     */
    private string $token = '';

    /**
     * РўРёРї С‚РѕРєРµРЅР°.
     *
     * @var    string
     * @since 1.3.0
     */
    private string $tokenType = 'Bearer';

    /**
     * РЎСЂРѕРє РґРµР№СЃС‚РІРёСЏ С‚РѕРєРµРЅР° РІ СЃРµРєСѓРЅРґР°С….
     *
     * @var    int
     * @since 1.3.0
     */
    private int $expiresIn = 0;

    /**
     * Р¤Р»Р°Рі С‚РµСЃС‚РѕРІРѕРіРѕ СЂРµР¶РёРјР°.
     *
     * @var    bool
     * @since 1.3.0
     */
    private bool $testMode = false;

    /**
     * РРґРµРЅС‚РёС„РёРєР°С‚РѕСЂ Р°РєРєР°СѓРЅС‚Р° РєР»РёРµРЅС‚Р°.
     *
     * @var    string
     * @since 1.3.0
     */
    private string $clientId = '';

    /**
     * РЎРµРєСЂРµС‚ РєР»РёРµРЅС‚Р°.
     *
     * @var    string
     * @since 1.3.0
     */
    private string $clientSecret = '';

    /**
     * @param   bool|null    $test_mode      Р¤Р»Р°Рі С‚РµСЃС‚РѕРІРѕРіРѕ СЂРµР¶РёРјР°.
     * @param   string|null  $client_id      РРґРµРЅС‚РёС„РёРєР°С‚РѕСЂ Р°РєРєР°СѓРЅС‚Р°.
     * @param   string|null  $client_secret  РЎРµРєСЂРµС‚РЅС‹Р№ РєР»СЋС‡.
     * @since 1.3.0
     */
    public function __construct(?bool $test_mode = false, ?string $client_id = '', ?string $client_secret = '')
    {
        $this->testMode     = (bool) ($test_mode ?? false);
        $this->clientId     = (string) ($client_id ?? '');
        $this->clientSecret = (string) ($client_secret ?? '');
        $this->applyDefaultTestCredentials();
    }

    /**
     * Р’С‹РїРѕР»РЅСЏРµС‚ Р·Р°РїСЂРѕСЃ Рє API РЎР”Р­Рљ.
     *
     * @param   string  $method          РџСѓС‚СЊ РјРµС‚РѕРґР° API.
     * @param   array   $data            РџР°СЂР°РјРµС‚СЂС‹ Р·Р°РїСЂРѕСЃР°.
     * @param   string  $request_method  HTTP-РјРµС‚РѕРґ.
     * @param   array   $curl_options    Р”РѕРїРѕР»РЅРёС‚РµР»СЊРЅС‹Рµ РїР°СЂР°РјРµС‚СЂС‹ CURL.
     *
     * @return  array
     *
     * @since 1.3.0
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
     * РџСЂРѕРІРµСЂСЏРµС‚ РІРѕР·РјРѕР¶РЅРѕСЃС‚СЊ РІС‹РїРѕР»РЅРµРЅРёСЏ Р·Р°РїСЂРѕСЃР° Рє API.
     *
     * @return  bool
     *
     * @since 1.3.0
     */
    public function canDoRequest(): bool
    {
        $this->applyDefaultTestCredentials();

        if (!empty($this->clientId) && !empty($this->clientSecret))
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

        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * РџСЂРёРјРµРЅСЏРµС‚ РїСѓР±Р»РёС‡РЅС‹Рµ С‚РµСЃС‚РѕРІС‹Рµ СѓС‡РµС‚РЅС‹Рµ РґР°РЅРЅС‹Рµ РЎР”Р­Рљ РёР· РґРѕРєСѓРјРµРЅС‚Р°С†РёРё, РµСЃР»Рё РІРєР»СЋС‡РµРЅ С‚РµСЃС‚РѕРІС‹Р№ СЂРµР¶РёРј
     * Рё РїРѕР»СЊР·РѕРІР°С‚РµР»СЊСЃРєРёРµ СѓС‡РµС‚РЅС‹Рµ РґР°РЅРЅС‹Рµ РЅРµ Р·Р°РґР°РЅС‹.
     *
     * @return  void
     *
     * @since 1.3.0
     */
    private function applyDefaultTestCredentials(): void
    {
        if (!$this->testMode)
        {
            return;
        }

        if (!empty($this->clientId) && !empty($this->clientSecret))
        {
            return;
        }

        $this->clientId     = self::CDEK_TEST_CLIENT_ID;
        $this->clientSecret = self::CDEK_TEST_CLIENT_SECRET;
    }

    /**
     * Р’РѕР·РІСЂР°С‰Р°РµС‚ РїР°СЂР°РјРµС‚СЂС‹ РїР»Р°РіРёРЅР° РёР»Рё `false`, РµСЃР»Рё РїР»Р°РіРёРЅ РѕС‚РєР»СЋС‡РµРЅ.
     *
     * @return  Registry|false
     *
     * @since 1.3.0
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
     * Р’РѕР·РІСЂР°С‰Р°РµС‚ Р±Р°Р·РѕРІС‹Р№ URI API РґР»СЏ РІС‹Р±СЂР°РЅРЅРѕРіРѕ РѕРєСЂСѓР¶РµРЅРёСЏ.
     *
     * @return  Uri
     *
     * @since 1.3.0
     */
    private function getCdekHost(): Uri
    {
        return new Uri($this->testMode ? self::CDEK_API_URL_TEST : self::CDEK_API_URL);
    }

    /**
     * Р—Р°РіСЂСѓР¶Р°РµС‚ РґР°РЅРЅС‹Рµ С‚РѕРєРµРЅР° РёР· РєСЌС€Р° Рё РїСЂРё РЅРµРѕР±С…РѕРґРёРјРѕСЃС‚Рё РѕР±РЅРѕРІР»СЏРµС‚ РёС….
     *
     * @return  bool
     *
     * @since 1.3.0
     */
    public function loadTokenData(): bool
    {
        if (!empty($this->token) && !empty($this->tokenType) && $this->expiresIn > 0)
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

        return !empty($this->token);
    }


    /**
     * РђРІС‚РѕСЂРёР·СѓРµС‚СЃСЏ РІ API РЎР”Р­Рљ Рё СЃРѕС…СЂР°РЅСЏРµС‚ РґР°РЅРЅС‹Рµ С‚РѕРєРµРЅР°.
     *
     * @return  array
     *
     * @since 1.3.0
     */
    public function authorize(): array
    {
        $authorizeData = [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        $response = $this->getResponse('/oauth/token', $authorizeData, 'POST');

        if (array_key_exists('error_code', $response))
        {
            return $response;
        }

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
     * РЎРѕС…СЂР°РЅСЏРµС‚ РґР°РЅРЅС‹Рµ С‚РѕРєРµРЅР° РІ РєСЌС€ Joomla.
     *
     * @param   array  $tokenData
     *
     * @return  void
     *
     * @since 1.3.0
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
     * РћР±СЂР°Р±Р°С‚С‹РІР°РµС‚ РѕС‚РІРµС‚ Рё РїСЂРёРІРѕРґРёС‚ РѕС€РёР±РєРё Рє РµРґРёРЅРѕРјСѓ С„РѕСЂРјР°С‚Сѓ.
     *
     * @param   ResponseInterface  $response    HTTP-РѕС‚РІРµС‚.
     * @param   string             $methodName  РРјСЏ РјРµС‚РѕРґР° API.
     *
     * @return  array
     *
     * @since 1.3.0
     */
    private function mapResponse(ResponseInterface $response, string $methodName = ''): array
    {
        $errorArray   = [
            'error_code'    => Text::_('PKG_LIB_WTCDEK_ERROR_RESPONSEHANDLER_NO_CODE'),
            'error_message' => Text::_('PKG_LIB_WTCDEK_ERROR_RESPONSEHANDLER_NO_ERROR_DESC'),
        ];
        $errorMessage = '';
        $statusCode   = $response->getStatusCode();
        $responseBody = (string)$response->getBody();
        $body = json_decode($responseBody, true);

        if (!is_array($body))
        {
            $body = (new Registry($responseBody))->toArray();
        }

        if ($statusCode >= 400 && $statusCode < 500)
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
                $errorMessage = $responseBody;
            }

            $errorMessage = Text::sprintf(
                Text::_('PKG_LIB_WTCDEK_ERROR_RESPONSEHANDLER_ERROR_400'),
                (!empty($methodName) ? 'REST API method: ' . $methodName . ':' : ''),
                $errorMessage
            );
            $this->saveToLog($errorMessage, 'ERROR');

            return [
                'error_code'    => (int) $statusCode,
                'error_message' => $errorMessage,
            ];
        }

        if ($statusCode >= 500)
        {
            $this->saveToLog(
                'Error while trying to calculate delivery cost via Cdek. Cdek РћС‚РІРµС‚ API: ' . print_r($body, true),
                'ERROR'
            );
            $errorArray['error_code']    = (int) $statusCode;
            $errorArray['error_message'] = Text::_('PKG_LIB_WTCDEK_ERROR_RESPONSEHANDLER_ERROR_500', print_r($body, true));

            return $errorArray;
        }

        return $body;
    }

    /**
     * РџСЂРµРѕР±СЂР°Р·СѓРµС‚ СЃС‚СЂСѓРєС‚СѓСЂСѓ РѕС€РёР±РѕРє API РІ С‡РёС‚Р°РµРјСѓСЋ СЃС‚СЂРѕРєСѓ.
     *
     * @param   mixed  $errors  Р”Р°РЅРЅС‹Рµ РѕС€РёР±РєРё.
     *
     * @return  string
     *
     * @since 1.3.0
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

        return implode(', ', array_filter($parts, static fn($part) => !empty($part)));
    }

    /**
     * Р’РѕР·РІСЂР°С‰Р°РµС‚ РёРґРµРЅС‚РёС„РёРєР°С‚РѕСЂ Р°РєРєР°СѓРЅС‚Р°.
     *
     * @return  string
     *
     * @since 1.3.0
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Р’РѕР·РІСЂР°С‰Р°РµС‚ СЃРµРєСЂРµС‚ Р°РєРєР°СѓРЅС‚Р°.
     *
     * @return  string
     *
     * @since 1.3.0
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * РЈСЃС‚Р°РЅР°РІР»РёРІР°РµС‚ Р·РЅР°С‡РµРЅРёРµ С‚РѕРєРµРЅР° РґРѕСЃС‚СѓРїР°.
     *
     * @param   string  $token  РўРѕРєРµРЅ РґРѕСЃС‚СѓРїР°.
     *
     * @return  void
     *
     * @since 1.3.0
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * РЈСЃС‚Р°РЅР°РІР»РёРІР°РµС‚ С‚РёРї С‚РѕРєРµРЅР°.
     *
     * @param   string  $tokenType  РўРёРї С‚РѕРєРµРЅР°.
     *
     * @return  void
     *
     * @since 1.3.0
     */
    public function setTokenType(string $tokenType): void
    {
        $this->tokenType = $tokenType;
    }

    /**
     * РЈСЃС‚Р°РЅР°РІР»РёРІР°РµС‚ СЃСЂРѕРє Р¶РёР·РЅРё С‚РѕРєРµРЅР° РІ СЃРµРєСѓРЅРґР°С….
     *
     * @param   int  $expiresIn  РЎСЂРѕРє Р¶РёР·РЅРё С‚РѕРєРµРЅР° РІ СЃРµРєСѓРЅРґР°С….
     *
     * @return  void
     *
     * @since 1.3.0
     */
    public function setTokenExpiresIn(int $expiresIn): void
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * Р’РѕР·РІСЂР°С‰Р°РµС‚ С„Р»Р°Рі С‚РµСЃС‚РѕРІРѕРіРѕ СЂРµР¶РёРјР°.
     *
     * @return  bool
     *
     * @since 1.3.0
     */
    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    /**
     * РћР±СЂР°Р±Р°С‚С‹РІР°РµС‚ РѕС‚РІРµС‚ Рё РїСЂРёРІРѕРґРёС‚ РѕС€РёР±РєРё Рє РµРґРёРЅРѕРјСѓ С„РѕСЂРјР°С‚Сѓ.
     *
     * @param   ResponseInterface  $response    HTTP-РѕС‚РІРµС‚.
     * @param   string    $methodName  РРјСЏ РјРµС‚РѕРґР° API.
     *
     * @return  array
     *
     * @since 1.3.0
     */
    public function responseHandler(ResponseInterface $response, string $methodName = ''): array
    {
        return $this->mapResponse($response, $methodName);
    }
}
