<?php
/**
 * IntakesEntity API entity.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

use function rawurlencode;

defined('_JEXEC') or die;

final class IntakesEntity extends AbstractEntity
{
	/**
	 * PATCH /v2/intakes
	 *
	 * Изменение статуса заявки на вызов курьера
	 *
	 * **Описание:**
	 * Метод позволяет изменить статус по действующей заявке на вызов курьера на "Требует обработки" с
	 * передачей дополнительных статусов, если по заявке требуются дополнительные операции, такие как прозвон
	 * или предоставление документов.
	 * Перейти в статус "Требует обработки" могут заявки, которые имеют текущий статус Требует обработки/Готова
	 * к назначению/Назначен курьер.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/intake/operation/changeStatus
	 *
	 * @param   array  $request_options  Request options.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function changeStatus(array $request_options = []): array
	{
		if (empty($request_options))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Request data is required',
			];
		}

		return $this->request->getResponse('/intakes', $request_options, 'PATCH');
	}

	/**
	 * POST /v2/intakes
	 *
	 * Регистрация заявки на вызов курьера
	 *
	 * **Описание:**
	 * Метод позволяет осуществить вызов курьера для забора груза со склада интернет-магазина с последующей
	 * доставкой до склада СДЭК. Рекомендуемый минимальный диапазон времени для приезда курьера не менее 3-х
	 * часов. В теле запроса передаются данные об адресе отправителя, контактном лице, дате и интервале времени
	 * забора, количестве и характеристиках мест отправления. В ответе возвращается уникальный идентификатор
	 * заявки и текущий статус запроса*.
	 *
	 * *Метод работает асинхронно. Статус "ACCEPTED" в ответе на запрос не гарантирует, что заявка создана в ИС
	 * СДЭК. Этот статус относится к запросу (запрос успешно принят) и говорит о том, что запрос прошел
	 * первичные валидации и структурно составлен корректно. Далее запрос проходит остальные валидации,
	 * результат можно получить с помощью метода получения информации о заявке. Статус запроса "SUCCESSFUL" -
	 * сущность успешно создана в системе, статус "INVALID" - при создании возникла ошибка, необходимо её
	 * исправить и повторно отправить запрос на регистрацию заявки на вызов курьера.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/intake/operation/create
	 *
	 * @param   array  $request_options  Request options.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function create(array $request_options = []): array
	{
		if (empty($request_options))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Request data is required',
			];
		}

		return $this->request->getResponse('/intakes', $request_options, 'POST');
	}

	/**
	 * POST /v2/intakes/availableDays
	 *
	 * Получение дат вызова курьера для НП
	 *
	 * **Описание:**
	 * Метод позволяет получить доступные даты для забора груза курьером со склада интернет-магазина для
	 * населенного пункта, в котором находится склад.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/intake/operation/getAvailableDays
	 *
	 * @param   array  $request_options  Request options.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getAvailableDays(array $request_options = []): array
	{
		if (empty($request_options))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Request data is required',
			];
		}

		return $this->request->getResponse('/intakes/availableDays', $request_options, 'POST');
	}

	/**
	 * DELETE /v2/intakes/{uuid}
	 *
	 * Удаление заявки
	 *
	 * **Описание:**
	 * Метод предназначен для удаления заявки на вызов курьера.
	 *
	 * Заявку через интеграцию можно удалить в любом статусе, отличном от финального.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/intake/operation/deleteByUuid
	 *
	 * @param   string  $uuid  Intake UUID.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function deleteByUuid(string $uuid): array
	{
		if (empty($uuid))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: uuid',
			];
		}

		return $this->request->getResponse('/intakes/' . rawurlencode($uuid), [], 'DELETE');
	}

	/**
	 * GET /v2/intakes/{uuid}
	 *
	 * Получение информации о заявке по UUID
	 *
	 * **Описание:**
	 * Метод предназначен для получения информации по UUID заявки.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/intake/operation/getByUuid
	 *
	 * @param   string  $uuid  Intake UUID.
	 *
	 * @return  array  API response.
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

		return $this->request->getResponse('/intakes/' . rawurlencode($uuid), [], 'GET');
	}

}

