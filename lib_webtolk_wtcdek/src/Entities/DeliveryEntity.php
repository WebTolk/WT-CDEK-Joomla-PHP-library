<?php
/**
 * Сущность API СДЭК: договоренности о доставке.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

use function rawurlencode;

defined('_JEXEC') or die;

final class DeliveryEntity extends AbstractEntity
{
	/**
	 * POST /v2/delivery
	 *
	 * Регистрация договоренности о доставке
	 *
	 * **Описание:**
	 * Метод позволяет фиксировать оговоренные с клиентом дату и время доставки (приезда курьера), а так же
	 * изменять адрес доставки. В ответе возвращается уникальный идентификатор договоренности и текущий статус
	 * запроса*.
	 *
	 * *Метод работает асинхронно. Статус "ACCEPTED" в ответе на запрос не гарантирует, что договоренность
	 * создана в ИС СДЭК. Этот статус относится к запросу (запрос успешно принят) и говорит о том, что запрос
	 * прошел первичные валидации и структурно составлен корректно. Далее запрос проходит остальные валидации,
	 * результат можно получить с помощью метода получения информации о договоренности. Статус запроса
	 * "SUCCESSFUL" - сущность успешно создана в системе, статус "INVALID" - при создании возникла ошибка,
	 * необходимо её исправить и повторно отправить запрос на регистрацию договоренности о доставке.
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/schedule/operation/register_2
	 *
	 * @param   array{
	 *             date?: string,
	 *             cdek_number?: string|int,
	 *             order_uuid?: string,
	 *             time_from?: string,
	 *             time_to?: string,
	 *             comment?: string,
	 *             delivery_point?: string,
	 *             to_location?: array<string, mixed>
	 *         }  $request_options  Параметры создания договоренности о доставке.
	 *                               Обязательно по схеме: `date`.
	 *                               По описанию API обязательно передать одно из полей: `cdek_number` или `order_uuid`.
	 *
	 * @return  array  Ответ API или структурированная ошибка валидации.
	 *
	 * @since  1.3.0
	 */
	public function create(array $request_options = []): array
	{
		if (empty($request_options))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Параметры запроса is required',
			];
		}

		if (empty($request_options['date']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: date',
			];
		}

		if (empty($request_options['cdek_number']) && empty($request_options['order_uuid']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: one of cdek_number, order_uuid',
			];
		}

		return $this->request->getResponse('/delivery', $request_options, 'POST');
	}

	/**
	 * POST /v2/delivery/estimatedIntervals
	 *
	 * Получение интервалов доставки до создания заказа
	 *
	 * **Описание:**
	 * Метод позволяет получить доступные интервалы доставки "до двери" до создания заказа, тем самым
	 * предоставляя возможность рассчитать доступные слоты для регистрации договоренности о доставке заранее, в
	 * зависимости от направления и выбранного тарифа. Если в total_count передано значение доставок, например,
	 * равное 100, а в agreed_count количество согласованных доставок - 98, это означает, что данном интервале
	 * осталось два доступных слота для регистрации договоренности о доставке. После того как все доступные
	 * слоты будут заняты, данный интервал станет недоступен для регистрации договоренности.
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/schedule/operation/getEstimatedIntervals
	 *
	 * @param   array{
	 *             date_time?: string,
	 *             tariff_code?: int|string,
	 *             from_location?: array{address?: string},
	 *             shipment_point?: string,
	 *             to_location?: array{address?: string}
	 *         }  $request_options  Параметры запроса интервалов до создания заказа.
	 *                               Обязательно по схеме: `date_time`, `tariff_code`, `to_location`.
	 *                               Вложенное обязательное поле по схеме: `to_location.address`.
	 *
	 * @return  array  Ответ API или структурированная ошибка валидации.
	 *
	 * @since  1.3.0
	 */
	public function getEstimatedIntervals(array $request_options = []): array
	{
		if (empty($request_options))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Параметры запроса is required',
			];
		}

		foreach (['date_time', 'tariff_code', 'to_location'] as $requiredField)
		{
			if (empty($request_options[$requiredField]))
			{
				return [
					'error_code'    => '500',
					'error_message' => 'Required option: ' . $requiredField,
				];
			}
		}

		if (empty($request_options['to_location']['address']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: to_location.address',
			];
		}

		if (!empty($request_options['from_location']) && empty($request_options['from_location']['address']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: from_location.address',
			];
		}

		return $this->request->getResponse('/delivery/estimatedIntervals', $request_options, 'POST');
	}

	/**
	 * GET /v2/delivery/intervals
	 *
	 * Получение интервалов доставки
	 *
	 * **Описание:**
	 * Метод используется для получения доступных интервалов доставки по номеру/идентификатору уже созданного
	 * заказа. Позволяет узнать о свободных датах и временных интервалах, определенных в ИС СДЭК, для
	 * регистрации договоренности о доставке заказа покупателю.
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/schedule/operation/getIntervals
	 *
	 * @param   array{
	 *             cdek_number?: string|int,
	 *             order_uuid?: string
	 *         }  $request_options  Параметры запроса интервалов.
	 *
	 * @return  array  Ответ API или структурированная ошибка валидации.
	 *
	 * @since  1.3.0
	 */
	public function getIntervals(array $request_options = []): array
	{
		if (empty($request_options['cdek_number']) && empty($request_options['order_uuid']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required options: one of cdek_number, order_uuid',
			];
		}

		return $this->request->getResponse('/delivery/intervals', $request_options, 'GET');
	}

	/**
	 * GET /v2/delivery/{uuid}
	 *
	 * Получение информации о договоренности о доставке
	 *
	 * **Описание:**
	 * Метод используется для получения информации об оговоренных с клиентом дате и времени доставки (приезда
	 * курьера), а так же возможном новом адресе доставки.
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/schedule/operation/get_3
	 *
	 * @param   string  $uuid  UUID договоренности о доставке.
	 *
	 * @return  array  Ответ API или структурированная ошибка валидации.
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

		return $this->request->getResponse('/delivery/' . rawurlencode($uuid), [], 'GET');
	}

}

