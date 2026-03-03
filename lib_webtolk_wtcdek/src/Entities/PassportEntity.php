<?php
/**
 * Сущность API СДЭК: паспортные данные.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

use Symfony\Component\Uid\Uuid;

defined('_JEXEC') or die;

final class PassportEntity extends AbstractEntity
{
	/**
	 * GET /passport
	 *
	 * Получение информации о паспортных данных
	 *
	 * **Описание:**
	 * Метод используется для получения информации о паспортных данных (сообщает о готовности передавать заказы
	 * на таможню) по международным заказу/заказам.
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/passport/operation/get_5
	 *
	 * @param   array{
	 *             cdek_number?: string|int,
	 *             order_uuid?: string,
	 *             client?: 'SENDER'|'RECEIVER'|'ALL'
	 *         }  $request_options  Параметры запроса.
	 *                              - cdek_number: номер заказа СДЭК.
	 *                              - order_uuid: UUID заказа.
	 *                              - client: сторона, для которой запрашиваются паспортные данные.
	 *                                Допустимые значения: SENDER, RECEIVER, ALL.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function get(array $request_options = []): array
	{
		if (empty($request_options['cdek_number']) && empty($request_options['order_uuid']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: one of cdek_number, order_uuid',
			];
		}

		if (!empty($request_options['order_uuid']) && !Uuid::isValid(\trim((string) $request_options['order_uuid'])))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Invalid option value: order_uuid',
			];
		}

		return $this->request->getResponse('/passport', $request_options, 'GET');
	}

}
