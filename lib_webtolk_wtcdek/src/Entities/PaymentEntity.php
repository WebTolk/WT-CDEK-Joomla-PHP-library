<?php
/**
 * @package       WT Cdek library package
 * @version       1.3.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         1.3.0
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

defined('_JEXEC') or die;

final class PaymentEntity extends AbstractEntity
{
	/**
	 * GET /payment
	 *
	 * Получение информации о переводе наложенного платежа
	 *
	 * **Описание:**
	 * Метод предназначен для получения информации о заказах, по которым был переведен наложенный платеж
	 * интернет-магазину в заданную дату
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/payment/operation/get_5
	 *
	 * @param   array{
	 *             date?: string
	 *         }  $request_options  Параметры запроса.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since 1.3.0
	 */
	public function get(array $request_options = []): array
	{
		if (empty($request_options['date']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: date',
			];
		}

		return $this->request->getResponse('/payment', $request_options, 'GET');
	}

}

