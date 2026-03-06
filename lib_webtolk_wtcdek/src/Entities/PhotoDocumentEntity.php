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
	 * Источник: https://apidoc.cdek.ru/#tag/photo/operation/getReadyOrders
	 *
	 * @param   array  $request_options  Параметры запроса.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since 1.3.0
	 */
	public function getReadyOrders(array $request_options = []): array
	{
		if (empty($request_options))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Параметры запроса is required',
			];
		}

		return $this->request->getResponse('/photoDocument', $request_options, 'POST');
	}

}

