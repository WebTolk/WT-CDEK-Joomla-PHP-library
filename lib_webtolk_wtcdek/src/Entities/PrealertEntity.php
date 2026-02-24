<?php
/**
 * Сущность API СДЭК: преалерты.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

use function rawurlencode;

defined('_JEXEC') or die;

final class PrealertEntity extends AbstractEntity
{
	/**
	 * POST /prealert
	 *
	 * Регистрация преалерта
	 *
	 * **Описание:**
	 * Метод предназначен для регистрации преалерта (реестра заказов, которые клиент собирается передать на
	 * склад СДЭК для дальнейшей доставки) - информирования со стороны интернет-магазина (далее ИМ) о желании
	 * передать некоторое количество заказов в СДЭК, чтобы к этому моменту принимающий офис подготовил
	 * необходимые документы.
	 * Преалерт нужен, только если ИМ собирается передавать большое количество заказов одновременно. Для работы
	 * с преалертом необходимо, чтобы услуга "Преалерт" была подключена по договору. По этому вопросу
	 * необходимо обратиться напрямую к закрепленному менеджеру или в закрепленный офис СДЭК (с кем
	 * подписывался договор).
	 *
	 * Преалерт не связан с заявкой на вызов курьера.
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/prealert/operation/register
	 *
	 * @param   array  $request_options  Параметры запроса.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function register(array $request_options = []): array
	{
		if (empty($request_options))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Параметры запроса is required',
			];
		}

		return $this->request->getResponse('/prealert', $request_options, 'POST');
	}

	/**
	 * GET /prealert/{uuid}
	 *
	 * Получение информации о преалерте
	 *
	 * **Описание:**
	 * Метод предназначен для получения информации по заданному преалерту
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/prealert/operation/get_1
	 *
	 * @param   string  $uuid  UUID преалерта.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function getByUuid(string $uuid): array
	{
		if (empty($uuid))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: uuid',
			];
		}

		return $this->request->getResponse('/prealert/' . rawurlencode($uuid), [], 'GET');
	}

}

