<?php
/**
 * PrintEntity API entity.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

use function rawurlencode;

defined('_JEXEC') or die;

final class PrintEntity extends AbstractEntity
{
	/**
	 * POST /v2/print/barcodes
	 *
	 * Формирование ШК места к заказу
	 *
	 * **Описание:**
	 * Метод используется для формирования ШК места в формате pdf к заказу/заказам.
	 *
	 * Во избежание перегрузки платформы нельзя передавать более 100 номеров заказов в одном запросе.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/print/operation/barcodePrint
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function barcodePrint(array $data = []): array
	{
		if (empty($data))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Request data is required',
			];
		}

		return $this->request->getResponse('/print/barcodes', $data, 'POST');
	}

	/**
	 * GET /v2/print/barcodes/{uuid}
	 *
	 * Получение ШК места к заказу
	 *
	 * **Описание:**
	 * Метод используется для получения ШК места в формате pdf к заказу/заказам.
	 * Ссылка на файл с ШК местом к заказу/заказам доступна в течение 1 часа.
	 *
	 * В ответе метода возвращается набор статусов entity->statuses. Их значения могут быть следующими
	 * | Код       | Название статуса    | Комментарий |
	 * |-----------|---------------------|-------------|
	 * | ACCEPTED  | Принят              | Запрос на формирование квитанции принят |
	 * | INVALID   | Некорректный запрос | Некорректный запрос на формирование квитанции |
	 * | PROCESSING| Формируется         | Файл с квитанцией формируется |
	 * | READY     | Сформирован         | Файл с квитанцией и ссылка на скачивание файла сформированы|
	 * | REMOVED   | Удален                 | Истекло время жизни ссылки на скачивание файла с квитанцией|
	 *
	 * Source: https://apidoc.cdek.ru/#tag/print/operation/barcodeGet
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function barcodeGet(string $uuid, array $request_options = []): array
	{
		return $this->request->getResponse('/print/barcodes/' . rawurlencode($uuid), $request_options, 'GET');
	}

	/**
	 * GET /v2/print/barcodes/{uuid}.pdf
	 *
	 * Скачивание готового ШК
	 *
	 * **Описание:**
	 * Скачивание ШК места в формате pdf к заказу/заказам
	 *
	 * Source: https://apidoc.cdek.ru/#tag/print/operation/barcodeDownload
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function barcodeDownload(string $uuid, array $request_options = []): array
	{
		return $this->request->getResponse('/print/barcodes/' . rawurlencode($uuid) . '.pdf', $request_options, 'GET');
	}

	/**
	 * POST /v2/print/orders
	 *
	 * Формирование квитанции к заказу
	 *
	 * **Описание:**
	 * Метод используется для формирования квитанции в формате pdf к заказу/заказам.
	 *
	 * Во избежание перегрузки платформы нельзя передавать более 100 номеров заказов в одном запросе.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/print/operation/waybillPrint
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function waybillPrint(array $data = []): array
	{
		if (empty($data))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Request data is required',
			];
		}

		return $this->request->getResponse('/print/orders', $data, 'POST');
	}

	/**
	 * GET /v2/print/orders/{uuid}
	 *
	 * Получение квитанции к заказу
	 *
	 * **Описание:**
	 * Метод используется для получения ссылки на квитанцию в формате pdf к заказу/заказам.
	 *
	 * Ссылка на файл с квитанцией к заказу/заказам доступна в течение 1 часа.
	 *
	 * В ответе метода возвращается набор статусов entity->statuses. Их значения могут быть следующими
	 * | Код       | Название статуса    | Комментарий  |
	 * |-----------|---------------------|--------------|
	 * | ACCEPTED  | Принят              | Запрос на формирование квитанции принят |
	 * | INVALID   | Некорректный запрос | Некорректный запрос на формирование квитанции |
	 * | PROCESSING| Формируется         | Файл с квитанцией формируется |
	 * | READY     | Сформирован         | Файл с квитанцией и ссылка на скачивание файла сформированы|
	 * | REMOVED   | Удален              | Истекло время жизни ссылки на скачивание файла с квитанцией|
	 *
	 * Source: https://apidoc.cdek.ru/#tag/print/operation/waybillGet
	 *
	 * @param   string  $uuid
	 * @param   array   $request_options
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function waybillGet(string $uuid, array $request_options = []): array
	{
		return $this->request->getResponse('/print/orders/' . rawurlencode($uuid), $request_options, 'GET');
	}

	/**
	 * GET /v2/print/orders/{uuid}.pdf
	 *
	 * Скачивание готовой квитанции
	 *
	 * **Описание:**
	 * Скачивание квитанции в формате pdf к заказу/заказам.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/print/operation/waybillDownload
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function waybillDownload(string $uuid, array $request_options = []): array
	{
		return $this->request->getResponse('/print/orders/' . rawurlencode($uuid) . '.pdf', $request_options, 'GET');
	}

}

