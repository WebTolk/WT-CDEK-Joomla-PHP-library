<?php
/**
 * Сущность API СДЭК: паспортные данные.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

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
	 * Источник: https://apidoc.cdek.ru/#tag/passport/operation/get_6
	 *
	 * @param   array  $request_options  Параметры запроса.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function get(array $request_options = []): array
	{
		return $this->request->getResponse('/passport', $request_options, 'GET');
	}

}

