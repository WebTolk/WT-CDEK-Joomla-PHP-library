<?php
/**
 * OauthEntity API entity.
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
	 * Source: https://apidoc.cdek.ru/#tag/auth/operation/getOAuthToken
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getOAuthToken(array $data = []): array
	{
		if (empty($data['grant_type']) || empty($data['client_id']) || empty($data['client_secret']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required options: grant_type, client_id, client_secret',
			];
		}

		return $this->request->getResponse('/oauth/token', $data, 'POST');
	}

}

