<?php
/**
 * PhotoDocumentEntity API entity.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

defined('_JEXEC') or die;

final class PhotoDocumentEntity extends AbstractEntity
{
	/**
	 * POST /v2/photoDocument
	 *
	 * Получение заказов с готовыми фото
	 *
	 * **Описание:**
	 * Метод используется для получения перечня заказов с ссылками на готовые к скачиванию архивы с фото. В
	 * запросе необходимо передать либо период, за который необходимо вернуть перечень заказов, либо список
	 * заказов. Если переданы и период, и список заказов, то период игнорируется.
	 *
	 * Для корректной работы метода, для договора должна быть подключена фотоуслуга, а также настроен
	 * фотопроект.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/photo/operation/getReadyOrders
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getReadyOrders(array $data = []): array
	{
		if (empty($data))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Request data is required',
			];
		}

		return $this->request->getResponse('/photoDocument', $data, 'POST');
	}

}

