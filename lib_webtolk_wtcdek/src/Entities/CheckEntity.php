<?php
/**
 * CheckEntity API entity.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

defined('_JEXEC') or die;

final class CheckEntity extends AbstractEntity
{
	/**
	 * GET /check
	 *
	 * Получение информации о чеках
	 *
	 * **Описание:**
	 * Метод используется для получения информации о чеке по заказу или за выбранный день.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/receipt/operation/get_7
	 *
	 * @param   array{
	 *             order_uuid?: string,
	 *             cdek_number?: string|int,
	 *             date?: string
	 *         }  $request_options  Request options.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function get(array $request_options = []): array
	{
		if (empty($request_options['order_uuid']) && empty($request_options['cdek_number']) && empty($request_options['date']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required options: one of order_uuid, cdek_number, date',
			];
		}

		return $this->request->getResponse('/check', $request_options, 'GET');
	}

}

