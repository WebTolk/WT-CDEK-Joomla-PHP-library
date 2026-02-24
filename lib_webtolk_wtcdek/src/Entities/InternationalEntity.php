<?php
/**
 * InternationalEntity API entity.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

defined('_JEXEC') or die;

final class InternationalEntity extends AbstractEntity
{
	/**
	 * POST /v2/international/package/restrictions
	 *
	 * Получение ограничений по международным заказам
	 *
	 * **Описание:**
	 * Метод предназначен для получения ограничений по направлению и тарифу для международного заказа.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/restriction_hints/operation/checkPackagesRestrictions
	 *
	 * @param   array  $request_options  Request options.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function checkPackagesRestrictions(array $request_options = []): array
	{
		if (empty($request_options))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Request data is required',
			];
		}

		return $this->request->getResponse('/international/package/restrictions', $request_options, 'POST');
	}

}

