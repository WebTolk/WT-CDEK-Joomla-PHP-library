<?php
/**
 * Сущность API СДЭК: авторизация.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

defined('_JEXEC') or die;

final class OauthEntity extends AbstractEntity
{
	/**
	 * POST /v2/oauth/token
	 *
	 * Получение токена авторизации
	 *
	 * **Описание:**
	 * Взаимодействие с сервисом требует клиентской авторизации. Авторизация обеспечивается с применением
	 * протокола OAuth 2.0. Метод предназначен для получения токена авторизации.
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/auth/operation/getOAuthToken
	 *
	 * @param   array{
	 *             grant_type?: string,
	 *             client_id?: string,
	 *             client_secret?: string
	 *         }  $request_options  Параметры запроса.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function getOAuthToken(array $request_options = []): array
	{
		if (empty($request_options['grant_type']) || empty($request_options['client_id']) || empty($request_options['client_secret']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required options: grant_type, client_id, client_secret',
			];
		}

		return $this->request->getResponse('/oauth/token', $request_options, 'POST');
	}

}

