<?php
/**
 * @package       WT Cdek library package
 * @version       1.3.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         1.3.0
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

use Symfony\Component\Uid\Uuid;

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
	 * Источник: https://apidoc.cdek.ru/#tag/receipt/operation/get_7
	 *
	 * @param   array{
	 *             order_uuid?: string,
	 *             cdek_number?: string|int,
	 *             date?: string
	 *         }  $request_options  Параметры запроса.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since 1.3.0
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

		if (!empty($request_options['order_uuid']) && !Uuid::isValid(\trim((string) $request_options['order_uuid'])))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Invalid option value: order_uuid',
			];
		}

		return $this->request->getResponse('/check', $request_options, 'GET');
	}

}

