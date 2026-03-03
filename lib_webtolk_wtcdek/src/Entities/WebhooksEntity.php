<?php
/**
 * Сущность API СДЭК: вебхуки.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

use Symfony\Component\Uid\Uuid;
use function rawurlencode;

defined('_JEXEC') or die;

final class WebhooksEntity extends AbstractEntity
{
	/**
	 * Допустимые типы подписок на вебхуки.
	 *
	 * @var    array<int, string>
	 * @since  1.3.1
	 */
	private const ALLOWED_TYPES = [
		'ORDER_STATUS',
		'ORDER_MODIFIED',
		'PRINT_FORM',
		'RECEIPT',
		'PREALERT_CLOSED',
		'ACCOMPANYING_WAYBILL',
		'OFFICE_AVAILABILITY',
		'DELIV_PROBLEM',
		'DELIV_AGREEMENT',
		'COURIER_INFO',
	];

	/**
	 * GET /v2/webhooks
	 *
	 * Получение информации о подписках на вебхуки
	 *
	 * **Описание:**
	 * Метод предназначен для получения информации о всех активных подписках интернет-магазина на получение
	 * вебхуков.
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/webhook/operation/getAll
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function getAll(): array
	{
		return $this->request->getResponse('/webhooks', [], 'GET');
	}

	/**
	 * POST /v2/webhooks
	 *
	 * Добавление подписки на вебхуки
	 *
	 * **Описание:**
	 * Метод предназначен для подключения подписки на отправку на URL клиента событий, связанных с заказом.
	 * Существуют следующие типы подписок:
	 * - ORDER_STATUS - об изменении статуса заказа
	 * - ORDER_MODIFIED - получение информации об изменении заказа
	 * - PRINT_FORM - о готовности печатной формы
	 * - RECEIPT - получение информации о чеке
	 * - PREALERT_CLOSED - получение информации о закрытии преалерта
	 * - ACCOMPANYING_WAYBILL - получение информации о транспорте для СНТ
	 * - OFFICE_AVAILABILITY - получение информации об изменении доступности офиса
	 * - DELIV_PROBLEM - получение информации о проблемах доставки по заказу
	 * - DELIV_AGREEMENT - получение информации об изменении договоренности о доставке
	 * - COURIER_INFO - получение информации о курьере
	 * Если у клиента уже есть подписка с указанным типом, то будет создана еще одна подписка с таким же типом.
	 *
	 * В ответе метода возвращается информация о запросе со статусом выполнения:
	 * - ACCEPTED: запрос принят в обработку.
	 * - SUCCESSFUL: подписка успешно создана.
	 * - INVALID: запрос отклонен из-за ошибок в данных.
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/webhook/operation/create
	 *
	 * @param   string  $url   URL, на который отправляется событие.
	 * @param   string  $type  Тип вебхука.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function create(string $url, string $type): array
	{
		$url  = \trim($url);
		$type = \trim($type);

		if (empty($url) || empty($type))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required options: url, type',
			];
		}

		if (!\filter_var($url, \FILTER_VALIDATE_URL))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Invalid option value: url',
			];
		}

		if (!\in_array($type, self::ALLOWED_TYPES, true))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Invalid option value: type',
			];
		}

		return $this->request->getResponse('/webhooks', ['url' => $url, 'type' => $type], 'POST');
	}

	/**
	 * DELETE /v2/webhooks/{uuid}
	 *
	 * Удаление подписки по UUID
	 *
	 * **Описание:**
	 * Метод предназначен для удаления подписки на получение вебхуков
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/webhook/operation/deleteById
	 *
	 * @param   string  $uuid  UUID подписки на вебхуки.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function deleteByUuid(string $uuid): array
	{
		$uuid = \trim($uuid);

		if (empty($uuid))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: uuid',
			];
		}

		if (!Uuid::isValid($uuid))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Invalid option value: uuid',
			];
		}

		return $this->request->getResponse('/webhooks/' . rawurlencode($uuid), [], 'DELETE');
	}

	/**
	 * GET /v2/webhooks/{uuid}
	 *
	 * Получение информации о подписке по UUID
	 *
	 * **Описание:**
	 * Метод предназначен для получения информации о подписке клиента на вебхуки по UUID
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/webhook/operation/getById
	 *
	 * @param   string  $uuid  UUID подписки на вебхуки.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function getByUuid(string $uuid): array
	{
		$uuid = \trim($uuid);

		if (empty($uuid))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: uuid',
			];
		}

		if (!Uuid::isValid($uuid))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Invalid option value: uuid',
			];
		}

		return $this->request->getResponse('/webhooks/' . rawurlencode($uuid), [], 'GET');
	}

}
