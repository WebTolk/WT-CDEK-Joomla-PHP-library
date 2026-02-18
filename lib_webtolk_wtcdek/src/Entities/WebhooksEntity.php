<?php
/**
 * WebhooksEntity API entity.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

use function rawurlencode;

defined('_JEXEC') or die;

final class WebhooksEntity extends AbstractEntity
{
	/**
	 * Legacy-compatible webhook subscription method.
	 *
	 * @param   string  $url   Webhook URL.
	 * @param   string  $type  Webhook type.
	 *
	 * @return  array
	 *
	 * @since  1.3.0
	 */
	public function subscribe(string $url, string $type): array
	{
		if (empty($url) || empty($type))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'There is no $url or $type. Specify it, please.',
			];
		}

		return $this->request->getResponse('/webhooks', ['url' => $url, 'type' => $type], 'POST');
	}

	/**
	 * GET /v2/webhooks
	 *
	 * Получение информации о подписках на вебхуки
	 *
	 * **Описание:**
	 * Метод предназначен для получения информации о всех активных подписках интернет-магазина на получение
	 * вебхуков.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/webhook/operation/getAll
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getAll(array $data = []): array
	{
		return $this->request->getResponse('/webhooks', $data, 'GET');
	}

	/**
	 * POST /v2/webhooks
	 *
	 * Добавление подписки на вебхуки
	 *
	 * **Описание:**
	 * Метод предназначен для подключения подписки на отправку на URL клиента событий, связанных с заказом.
	 * Существуют следующие типы подписок:
	 * - об изменении статуса заказа
	 * - о готовности печатной формы
	 * - получение информации о закрытии преалерта
	 * - получение информации об изменении доступности офиса
	 * - получение информации об изменении заказа
	 * - получение информации о транспорте для СНТ
	 * - получение информации об изменении договоренности о доставке
	 * - получение информации о проблемах доставки по заказу
	 * - получение информации о курьере
	 * Если у клиента уже есть подписка с указанным типом, то будет создана еще одна подписка с таким же типом.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/webhook/operation/create
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function create(array $data = []): array
	{
		if (empty($data['url']) || empty($data['type']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required options: url, type',
			];
		}

		return $this->request->getResponse('/webhooks', $data, 'POST');
	}

	/**
	 * DELETE /v2/webhooks/{uuid}
	 *
	 * Удаление подписки по UUID
	 *
	 * **Описание:**
	 * Метод предназначен для удаления подписки на получение вебхуков
	 *
	 * Source: https://apidoc.cdek.ru/#tag/webhook/operation/deleteById
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function deleteById(string $uuid, array $request_options = []): array
	{
		return $this->request->getResponse('/webhooks/' . rawurlencode($uuid), $request_options, 'DELETE');
	}

	/**
	 * GET /v2/webhooks/{uuid}
	 *
	 * Получение информации о подписке по UUID
	 *
	 * **Описание:**
	 * Метод предназначен для получения информации о подписке клиента на вебхуки по UUID
	 *
	 * Source: https://apidoc.cdek.ru/#tag/webhook/operation/getById
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getById(string $uuid, array $request_options = []): array
	{
		return $this->request->getResponse('/webhooks/' . rawurlencode($uuid), $request_options, 'GET');
	}

}

