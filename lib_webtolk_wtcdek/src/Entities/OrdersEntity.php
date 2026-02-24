<?php
/**
 * Сущность API СДЭК: заказы.
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
	 * Метод предназначен для создания в ИС СДЭК заказа на доставку товаров до покупателей.
	 * В запросе передается информация об отправителе, получателе, параметрах доставки и содержимом
	 * отправления. В ответе возвращается уникальный идентификатор заказа и текущий статус запроса*.
	 * Выделяется 2 типа заказов:
	 * - “интернет-магазин” - может быть только у клиента с типом договора “Интернет-магазин”;
	 * - “доставка” может быть создан любым клиентом с договором (но доступны тарифы только для обычной доставки).
	 *
	 * Метод работает асинхронно. Статус "ACCEPTED" в ответе на запрос не гарантирует, что заказ
	 * создан в ИС СДЭК. Этот статус относится к запросу (запрос успешно принят) и говорит о том,
	 * что запрос прошел первичные валидации и структурно составлен корректно. Далее запрос проходит
	 * остальные валидации, результат можно получить с помощью методов получения информации о заказе.
	 * Статус запроса "SUCCESSFUL" - сущность успешно создана в системе, статус "INVALID" - при создании
	 * возникла ошибка, необходимо её исправить и повторно отправить запрос на регистрацию заказа.
	 *
	 * @param   array{
	 *             tariff_code?: int|string,
	 *             recipient?: array{
	 *                 name?: string,
	 *                 phones?: array<int, array{number?: string}>
	 *             },
	 *             packages?: array<int, array{
	 *                 number?: string|int,
	 *                 weight?: int|float|string
	 *             }>
	 *         }  $request_options  Параметры создания заказа.
	 *                               Обязательно по локальной схеме API:
	 *                               `tariff_code`, `recipient`, `packages`,
	 *                               `recipient.name`, `packages[].number`, `packages[].weight`.
	 *
	 * @return  array  Ответ API or structured validation error.
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

		if (empty($request_options['recipient']['name']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: recipient.name',
			];
		}

		foreach ($request_options['packages'] as $index => $package)
		{
			if (empty($package['number']))
			{
				return [
					'error_code'    => '500',
					'error_message' => 'Required option: packages[' . $index . '].number',
				];
			}

			if (empty($package['weight']))
			{
				return [
					'error_code'    => '500',
					'error_message' => 'Required option: packages[' . $index . '].weight',
				];
			}
		}

		if (!empty($request_options['recipient']['phones']))
		{
			foreach ($request_options['recipient']['phones'] as $index => $phone)
			{
				if (empty($phone['number']))
				{
					return [
						'error_code'    => '500',
						'error_message' => 'Required option: recipient.phones[' . $index . '].number',
					];
				}
			}
		}

		return $this->request->getResponse('/orders', $request_options, 'POST');
	}

	/**
	 * Метод предоставляет возможность получить детальную информацию о ранее созданном заказе
	 * по номеру СДЭК/ИМ заказа. В ответе содержатся данные о статусе заказа, деталях доставки
	 * и информации о получателе. Есть возможность получить информацию, в том числе о заказах,
	 * которые были созданы через другие каналы (личный кабинет, вручную менеджером и др.),
	 * но только по тем, которые были созданы после первой авторизации по ключам интеграции.
	 *
	 *
	 * @param   string|null  $uuid         UUID заказа в СДЭК.
	 * @param   string|null  $cdek_number  Номер заказа СДЭК.
	 * @param   string|null  $im_number    Номер заказа в интернет-магазине.
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
			return $this->request->getResponse('/orders/' . rawurlencode(trim($uuid)), [], 'GET');
		}

		if (!empty($cdek_number))
		{
			return $this->request->getResponse('/orders', ['cdek_number' => $cdek_number], 'GET');
		}

		return $this->request->getResponse('/orders', ['im_number' => $im_number], 'GET');
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
	 * Источник: https://apidoc.cdek.ru/#tag/order/operation/update
	 *
	 * @param   array{
	 *             uuid?: string,
	 *             cdek_number?: string|int,
	 *             type?: int|string,
	 *             recipient?: array<string, mixed>
	 *         }  $request_options  Параметры обновления заказа. Обязательные ключи по схеме: `type`, `recipient`.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function updateOrder(array $request_options = []): array
	{
		if (empty($request_options))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Параметры запроса is required',
			];
		}

		foreach (['type', 'recipient'] as $requiredField)
		{
			if (empty($request_options[$requiredField]))
			{
				return [
					'error_code'    => '500',
					'error_message' => 'Required option: ' . $requiredField,
				];
			}
		}

		if (empty($request_options['uuid']) && empty($request_options['cdek_number']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: one of uuid, cdek_number',
			];
		}

		return $this->request->getResponse('/orders', $request_options, 'PATCH');
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
	 * Источник: https://apidoc.cdek.ru/#tag/order/operation/getIntakes
	 *
	 * @param   string  $order_uuid  UUID заказа.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function getIntakes(string $order_uuid): array
	{
		if (empty($order_uuid))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: order_uuid',
			];
		}

		return $this->request->getResponse('/orders/' . rawurlencode($order_uuid) . '/intakes', [], 'GET');
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
	 * **Условием возможности удаления заказа является отсутствие движения груза на складе СДЭК (статус заказа
	 * «Создан»).**
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/order/operation/delete
	 *
	 * @param   string  $uuid  UUID заказа в СДЭК.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function deleteOrder(string $uuid): array
	{
		if (empty($uuid))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: uuid',
			];
		}

		return $this->request->getResponse('/orders/' . rawurlencode(trim($uuid)), [], 'DELETE');
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
	 * Источник: https://apidoc.cdek.ru/#tag/order/operation/clientReturn
	 *
	 * @param   string  $uuid             UUID заказа.
	 * @param   array{
	 *             tariff_code?: int|string
	 *         }  $request_options  Параметры клиентского возврата. Required key: `tariff_code`.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function clientReturn(string $uuid, array $request_options = []): array
	{
		if (empty($uuid))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: uuid',
			];
		}

		if (empty($request_options['tariff_code']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: tariff_code',
			];
		}

		return $this->request->getResponse('/orders/' . rawurlencode(trim($uuid)) . '/clientReturn', $request_options, 'POST');
	}

	/**
	 * POST /v2/orders/{uuid}/refusal
	 *
	 * Регистрация отказа
	 *
	 * **Описание:**
	 * Метод предназначен для регистрации отказа по заказу и дальнейшего возврата
	 * данного заказа в интернет-магазин.
	 * После успешной регистрации отказа статус заказа переходит в "Не вручен" (код NOT_DELIVERED) с
	 * дополнительным статусом "Возврат, отказ от получения: Без объяснения" (код 11). Заказ может быть отменен
	 * в любом статусе*, пока не установлен статус "Вручен" или "Не вручен".
	 *
	 * Не рекомендуется использовать метод для заказов, которые находятся в статусе "Создан" и не планируются
	 * к отгрузке на склады СДЭК. В случае применения метода отказа для заказов, находящихся в статусе
	 * "Создан", по ним будут начислены операции и заказы будут включены в Акт оказанных услуг. Для отмены
	 * заказа в статусе "Создан" воспользуйтесь методом "Удаление заказа".
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/order/operation/refuse
	 *
	 * @param   string  $uuid  UUID заказа.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	public function refuse(string $uuid): array
	{
		if (empty($uuid))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: uuid',
			];
		}

		return $this->request->getResponse('/orders/' . rawurlencode(trim($uuid)) . '/refusal', [], 'POST');
	}

}

