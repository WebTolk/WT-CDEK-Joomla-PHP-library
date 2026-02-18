<?php
/**
 * DeliveryEntity API entity.
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
	 * Source: https://apidoc.cdek.ru/#tag/schedule/operation/register_2
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function create(array $data = []): array
	{
		if (empty($data))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Request data is required',
			];
		}

		return $this->request->getResponse('/delivery', $data, 'POST');
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
	 * Source: https://apidoc.cdek.ru/#tag/schedule/operation/getEstimatedIntervals
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getEstimatedIntervals(array $data = []): array
	{
		if (empty($data))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Request data is required',
			];
		}

		return $this->request->getResponse('/delivery/estimatedIntervals', $data, 'POST');
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
	 * Source: https://apidoc.cdek.ru/#tag/schedule/operation/getIntervals
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getIntervals(array $data = []): array
	{
		if (empty($data['cdek_number']) && empty($data['order_uuid']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required options: one of cdek_number, order_uuid',
			];
		}

		return $this->request->getResponse('/delivery/intervals', $data, 'GET');
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
	 * Source: https://apidoc.cdek.ru/#tag/schedule/operation/get_3
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getByUuid(string $uuid, array $request_options = []): array
	{
		return $this->request->getResponse('/delivery/' . rawurlencode($uuid), $request_options, 'GET');
	}

}

