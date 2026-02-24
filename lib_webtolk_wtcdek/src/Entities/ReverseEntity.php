<?php
/**
 * Сущность API СДЭК: реверс.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

defined('_JEXEC') or die;

final class ReverseEntity extends AbstractEntity
{
	/**
	 * POST /v2/reverse/availability
	 *
	 * Проверка доступности реверса
	 *
	 * **Описание:**
	 * Метод позволяет проверить доступность реверса до создания прямого заказа. Для выполнения проверки
	 * необходимо передать в запросе данные, аналогичные тем, которые будут использоваться при создании заказа,
	 * включая направление, данные отправителя и получателя, офис отправки/доставки и выбранный тариф.
	 * Если реверс доступен, API вернёт пустой ответ с кодом 200. В случае недоступности услуги или при наличии
	 * ошибок в запросе, в ответе будет возвращено сообщение об ошибке с соответствующим описанием.
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/reverse/operation/checkAvailability
	 *
	 * @param   array  $request_options  Параметры запроса.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function checkAvailability(array $request_options = []): array
	{
		if (empty($request_options))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Параметры запроса is required',
			];
		}

		return $this->request->getResponse('/reverse/availability', $request_options, 'POST');
	}

}

