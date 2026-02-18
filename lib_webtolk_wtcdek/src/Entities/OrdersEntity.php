<?php
/**
 * OrdersEntity API entity.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

use function rawurlencode;

defined('_JEXEC') or die;

final class OrdersEntity extends AbstractEntity
{
	/**
	 * Legacy-compatible order creation method with required fields validation.
	 *
	 * @param   array  $request_options  Request options.
	 *
	 * @return  array
	 *
	 * @since  1.3.0
	 */
	public function createOrder(array $request_options): array
	{
		foreach (['tariff_code', 'recipient', 'packages'] as $requiredField)
		{
			if (empty($request_options[$requiredField]))
			{
				return [
					'error_code'    => '500',
					'error_message' => 'There is no ' . $requiredField . ' in $request_options',
				];
			}
		}

		return $this->request->getResponse('/orders', $request_options, 'POST');
	}

	/**
	 * Legacy-compatible order info lookup.
	 *
	 * @param   string|null  $uuid         CDEK order UUID.
	 * @param   string|null  $cdek_number  CDEK order number.
	 * @param   string|null  $im_number    Merchant order number.
	 *
	 * @return  array
	 *
	 * @since  1.3.0
	 */
	public function getOrderInfo(?string $uuid = '', ?string $cdek_number = '', ?string $im_number = ''): array
	{
		if (empty($uuid) && empty($cdek_number) && empty($im_number))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'There is no order UUID or Cdek order number or Joomla side order id specified',
			];
		}

		if (!empty($uuid))
		{
			return $this->request->getResponse('/orders/' . $uuid, [], 'GET');
		}

		if (!empty($cdek_number))
		{
			return $this->request->getResponse('/orders', ['cdek_number' => $cdek_number], 'GET');
		}

		return $this->request->getResponse('/orders', ['im_number' => $im_number], 'GET');
	}

	/**
	 * GET /v2/orders
	 *
	 * Получение информации о заказе по номеру СДЭК/ИМ
	 *
	 * **Описание:**
	 * Метод предоставляет возможность получить детальную информацию о ранее созданном заказе по номеру СДЭК/ИМ
	 * заказа. В ответе содержатся данные о статусе заказа, деталях доставки и информации о получателе.
	 *
	 * Есть возможность получить информацию, в том числе о заказах, которые были созданы через другие каналы
	 * (личный кабинет, вручную менеджером и др.), но только по тем, которые были созданы после первой
	 * авторизации по ключам интеграции.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/order/operation/get
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function get(array $data = []): array
	{
		if (empty($data['cdek_number']) && empty($data['im_number']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required options: one of cdek_number, im_number',
			];
		}

		return $this->request->getResponse('/orders', $data, 'GET');
	}

	/**
	 * PATCH /v2/orders
	 *
	 * Изменение заказа
	 *
	 * **Описание:**
	 * Метод используется для изменения созданного ранее заказа.
	 *
	 * Условием возможности изменения заказа является отсутствие движения груза на складе СДЭК (т.е. статус
	 * заказа «Создан»).
	 *
	 * Source: https://apidoc.cdek.ru/#tag/order/operation/update
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function update(array $data = []): array
	{
		if (empty($data))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Request data is required',
			];
		}

		return $this->request->getResponse('/orders', $data, 'PATCH');
	}

	/**
	 * POST /v2/orders
	 *
	 * Регистрация заказа
	 *
	 * **Описание:**
	 * Метод предназначен для создания в ИС СДЭК заказа на доставку товаров до покупателей. В запросе
	 * передается информация об отправителе, получателе, параметрах доставки и содержимом отправления. В ответе
	 * возвращается уникальный идентификатор заказа и текущий статус запроса*.
	 *
	 * Выделяется 2 типа заказов:
	 *
	 * “интернет-магазин” - может быть только у клиента с типом договора “Интернет-магазин”;
	 *
	 * “доставка” может быть создан любым клиентом с договором (но доступны тарифы только для обычной
	 * доставки).
	 *
	 * *Метод работает асинхронно. Статус "ACCEPTED" в ответе на запрос не гарантирует, что заказ создан в ИС
	 * СДЭК. Этот статус относится к запросу (запрос успешно принят) и говорит о том, что запрос прошел
	 * первичные валидации и структурно составлен корректно. Далее запрос проходит остальные валидации,
	 * результат можно получить с помощью методов получения информации о заказе. Статус запроса "SUCCESSFUL" -
	 * сущность успешно создана в системе, статус "INVALID" - при создании возникла ошибка, необходимо её
	 * исправить и повторно отправить запрос на регистрацию заказа.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/order/operation/register_1
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function create(array $data = []): array
	{
		foreach (['tariff_code', 'recipient', 'packages'] as $requiredField)
		{
			if (empty($data[$requiredField]))
			{
				return [
					'error_code'    => '500',
					'error_message' => 'There is no ' . $requiredField . ' in $data',
				];
			}
		}

		return $this->request->getResponse('/orders', $data, 'POST');
	}

	/**
	 * GET /v2/orders/{orderUuid}/intakes
	 *
	 * Получение информации о всех заявках по заказу
	 *
	 * **Описание:**
	 * Метод предназначен для получения информации о всех созданных заявках на вызов курьера по идентификатору
	 * заказа.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/order/operation/getIntakes
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getIntakes(string $order_uuid, array $request_options = []): array
	{
		return $this->request->getResponse('/orders/' . rawurlencode($order_uuid) . '/intakes', $request_options, 'GET');
	}

	/**
	 * DELETE /v2/orders/{uuid}
	 *
	 * Удаление заказа
	 *
	 * **Описание:**
	 * С помощью этого метода можно удалить ранее созданный заказ из системы CDEK. В запросе указывается
	 * идентификатор заказа, который необходимо удалить. В ответе возвращается подтверждение успешного удаления
	 * или информация об ошибке, если удаление невозможно.
	 *
	 * Условием возможности удаления заказа является отсутствие движения груза на складе СДЭК (статус заказа
	 * «Создан»).
	 *
	 * Source: https://apidoc.cdek.ru/#tag/order/operation/delete
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function delete(string $uuid, array $request_options = []): array
	{
		return $this->request->getResponse('/orders/' . rawurlencode($uuid), $request_options, 'DELETE');
	}

	/**
	 * GET /v2/orders/{uuid}
	 *
	 * Получение информации о заказе по UUID
	 *
	 * **Описание:**
	 * Метод предоставляет возможность получить детальную информацию о ранее созданном заказе по его
	 * идентификатору. В ответе содержатся данные о статусе заказа, деталях доставки и информации о получателе.
	 *
	 * Есть возможность получить информацию, в том числе о заказах, которые были созданы через другие каналы
	 * (личный кабинет, вручную менеджером и др.), но только по тем, которые были созданы после первой
	 * авторизации по ключам интеграции.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/order/operation/get_2
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getByUuid(string $uuid, array $request_options = []): array
	{
		return $this->request->getResponse('/orders/' . rawurlencode($uuid), $request_options, 'GET');
	}

	/**
	 * POST /v2/orders/{uuid}/clientReturn
	 *
	 * Регистрация клиентского возврата
	 *
	 * **Описание:**
	 * Метод предназначен для оформления клиентских возвратов для интернет-магазинов.
	 *
	 * Клиентский возврат — это возврат, который оформляет сам клиент уже после получения (вручения) заказа
	 * (создается только для заказов интернет-магазина в конечном статусе "Вручен").
	 *
	 * Отличие от обычного возврата в конечном статусе прямого заказа: у клиентских возвратов конечный статус
	 * "вручен" и возврат оформляет сам клиент, у обычных возвратов, конечный статус "Не вручен" и возврат
	 * оформляется СДЭКом. Для частично врученных заказов можно оформить и клиентский возврат и обычный
	 * возврат.
	 * Клиентский возврат связывается с прямым заказом СДЭК. Если прямая доставка осуществлялась не СДЭК, для
	 * создания обратного заказа необходимо использовать метод "Регистрация заказа" - если заказ оформляется
	 * как клиентский возврат, необходимо в запросе на регистрацию передать поле is_client_return = true.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/order/operation/clientReturn
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function clientReturn(string $uuid, array $request_options = []): array
	{
		if (empty($request_options))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Request data is required',
			];
		}

		return $this->request->getResponse('/orders/' . rawurlencode($uuid) . '/clientReturn', $request_options, 'POST');
	}

	/**
	 * POST /v2/orders/{uuid}/refusal
	 *
	 * Регистрация отказа
	 *
	 * **Описание:**
	 * Метод предназначен для регистрации отказа по заказу и дальнейшего возврата данного заказа в интернет-
	 * магазин.
	 * После успешной регистрации отказа статус заказа переходит в "Не вручен" (код NOT_DELIVERED) с
	 * дополнительным статусом "Возврат, отказ от получения: Без объяснения" (код 11). Заказ может быть отменен
	 * в любом статусе*, пока не установлен статус "Вручен" или "Не вручен".
	 * * Не рекомендуется использовать метод для заказов, которые находятся в статусе "Создан" и не планируются
	 * к отгрузке на склады СДЭК. В случае применения метода отказа для заказов, находящихся в статусе
	 * "Создан", по ним будут начислены операции и заказы будут включены в Акт оказанных услуг. Для отмены
	 * заказа в статусе "Создан" воспользуйтесь методом "Удаление заказа".
	 *
	 * Source: https://apidoc.cdek.ru/#tag/order/operation/refuse
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function refuse(string $uuid, array $request_options = []): array
	{
		return $this->request->getResponse('/orders/' . rawurlencode($uuid) . '/refusal', $request_options, 'POST');
	}

}

