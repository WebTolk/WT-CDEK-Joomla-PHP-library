<?php
/**
 * PaymentEntity API entity.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
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
	 * Source: https://apidoc.cdek.ru/#tag/payment/operation/get_5
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function get(array $data = []): array
	{
		if (empty($data['date']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: date',
			];
		}

		return $this->request->getResponse('/payment', $data, 'GET');
	}

}

