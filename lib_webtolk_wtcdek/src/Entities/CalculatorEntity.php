<?php
/**
 * Сущность API СДЭК: калькулятор.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

defined('_JEXEC') or die;

final class CalculatorEntity extends AbstractEntity
{
	/**
	 * Возвращает коды тарифов, доступных по текущему договору.
	 *
	 * Эндпоинт: `GET /v2/calculator/alltariffs`
	 *
	 * Ответ API содержит поле `tariff_codes`; метод возвращает только это поле.
	 *
	 * @return  array<int, array<string, mixed>>  Список метаданных кодов тарифов.
	 *
	 * @since  1.3.0
	 */
	public function getAllTariffs(): array
	{
		$result = $this->request->getResponse('/calculator/alltariffs', [], 'GET');

		return $result['tariff_codes'] ?? [];
	}

	/**
	 * Выполняет расчет доставки по конкретному коду тарифа.
	 *
	 * Эндпоинт: `POST /v2/calculator/tariff`
	 *
	 * Обязательные ключи:
	 * - `tariff_code`
	 * - `from_location`
	 * - `to_location`
	 * - `packages` (каждое место должно содержать `weight`)
	 *
	 * @param   array{
	 *             tariff_code?: int|string,
	 *             type?: int|string,
	 *             date?: string,
	 *             currency?: int|string,
	 *             from_location?: array{
	 *                 code?: int|string,
	 *                 city?: string,
	 *                 country_code?: string,
	 *                 postal_code?: string
	 *             },
	 *             to_location?: array{
	 *                 code?: int|string,
	 *                 city?: string,
	 *                 country_code?: string,
	 *                 postal_code?: string
	 *             },
	 *             services?: array<int, array{code: string, parameter?: int|float|string}>,
	 *             packages?: array<int, array{
	 *                 weight: int|float|string,
	 *                 length?: int|float|string,
	 *                 width?: int|float|string,
	 *                 height?: int|float|string
	 *             }>
	 *         }  $request_options  Параметры запроса калькулятора.
	 *
	 * @return  array  Ответ API или структурированная ошибка валидации.
	 *
	 * @since  1.3.0
	 */
	public function calculateTariff(array $request_options = []): array
	{
		return $this->requestTariff('/calculator/tariff', $request_options, true);
	}

	/**
	 * Выполняет расчет доставки по всем доступным тарифам.
	 *
	 * Эндпоинт: `POST /v2/calculator/tarifflist`
	 *
	 * Обязательные ключи:
	 * - `from_location`
	 * - `to_location`
	 * - `packages` (каждое место должно содержать `weight`)
	 *
	 * @param   array{
	 *             type?: int|string,
	 *             date?: string,
	 *             currency?: int|string,
	 *             from_location?: array{
	 *                 code?: int|string,
	 *                 city?: string,
	 *                 country_code?: string,
	 *                 postal_code?: string
	 *             },
	 *             to_location?: array{
	 *                 code?: int|string,
	 *                 city?: string,
	 *                 country_code?: string,
	 *                 postal_code?: string
	 *             },
	 *             services?: array<int, array{code: string, parameter?: int|float|string}>,
	 *             packages?: array<int, array{
	 *                 weight: int|float|string,
	 *                 length?: int|float|string,
	 *                 width?: int|float|string,
	 *                 height?: int|float|string
	 *             }>
	 *         }  $request_options  Параметры запроса калькулятора.
	 *
	 * @return  array  Ответ API или структурированная ошибка валидации.
	 *
	 * @since  1.3.0
	 */
	public function calculateTariffList(array $request_options = []): array
	{
		return $this->requestTariff('/calculator/tarifflist', $request_options);
	}

	/**
	 * Выполняет расчет по доступным тарифам и дополнительным услугам.
	 *
	 * Эндпоинт: `POST /v2/calculator/tariffAndService`
	 *
	 * Обязательные ключи:
	 * - `from_location`
	 * - `to_location`
	 * - `packages` (каждое место должно содержать `weight`)
	 *
	 * Описание услуг в `services[]` (перенесено из фасадного метода):
	 * - `code`: код услуги из справочника дополнительных услуг.
	 * - `parameter`: значение параметра услуги зависит от `code`.
	 *   1) количество для: `PACKAGE_1`, `COURIER_PACKAGE_A2`, `SECURE_PACKAGE_A2`,
	 *      `SECURE_PACKAGE_A3`, `SECURE_PACKAGE_A4`, `SECURE_PACKAGE_A5`,
	 *      `CARTON_BOX_XS`, `CARTON_BOX_S`, `CARTON_BOX_M`, `CARTON_BOX_L`,
	 *      `CARTON_BOX_500GR`, `CARTON_BOX_1KG`, `CARTON_BOX_2KG`,
	 *      `CARTON_BOX_3KG`, `CARTON_BOX_5KG`, `CARTON_BOX_10KG`,
	 *      `CARTON_BOX_15KG`, `CARTON_BOX_20KG`, `CARTON_BOX_30KG`,
	 *      `CARTON_FILLER`.
	 *   2) объявленная стоимость заказа для `INSURANCE` (только для заказов типа "доставка").
	 *   3) длина для `BUBBLE_WRAP`, `WASTE_PAPER`.
	 *   4) количество фотографий для `PHOTO_DOCUMENT`.
	 *
	 * @param   array{
	 *             type?: int|string,
	 *             date?: string,
	 *             currency?: int|string,
	 *             from_location?: array{
	 *                 code?: int|string,
	 *                 city?: string,
	 *                 country_code?: string,
	 *                 postal_code?: string
	 *             },
	 *             to_location?: array{
	 *                 code?: int|string,
	 *                 city?: string,
	 *                 country_code?: string,
	 *                 postal_code?: string
	 *             },
	 *             services?: array<int, array{code: string, parameter?: int|float|string}>,
	 *             packages?: array<int, array{
	 *                 weight: int|float|string,
	 *                 length?: int|float|string,
	 *                 width?: int|float|string,
	 *                 height?: int|float|string
	 *             }>
	 *         }  $request_options  Параметры запроса калькулятора.
	 *
	 * @return  array  Ответ API или структурированная ошибка валидации.
	 *
	 * @since  1.3.1
	 */
	public function tariffAndService(array $request_options = []): array
	{
		return $this->requestTariff('/calculator/tariffAndService', $request_options);
	}

	/**
	 * Проверяет обязательные параметры запроса калькулятора.
	 *
	 * @param   array  $request_options   Параметры запроса.
	 * @param   bool   $requireTariffCode  Требуется ли обязательная передача кода тарифа.
	 *
	 * @return  array|null  Ошибка валидации или `null`, если данные корректны.
	 *
	 * @since  1.3.0
	 */
	private function validatePackagesRequest(array $request_options, bool $requireTariffCode = false): ?array
	{
		if ($requireTariffCode && empty($request_options['tariff_code']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: tariff_code',
			];
		}

		if (empty($request_options['from_location']) || empty($request_options['to_location']) || empty($request_options['packages']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required options: from_location, to_location, packages',
			];
		}

		foreach ($request_options['packages'] as $package)
		{
			if (empty($package['weight']))
			{
				return [
					'error_code'    => '500',
					'error_message' => 'Each package must contain weight',
				];
			}
		}

		return null;
	}

	/**
	 * Выполняет запрос калькулятора с общей валидацией.
	 *
	 * @param   string  $endpoint          Эндпоинт калькулятора.
	 * @param   array   $request_options   Параметры запроса.
	 * @param   bool    $requireTariffCode Требуется ли обязательная передача кода тарифа.
	 *
	 * @return  array  Ответ API.
	 *
	 * @since  1.3.0
	 */
	private function requestTariff(string $endpoint, array $request_options = [], bool $requireTariffCode = false): array
	{
		$validation = $this->validatePackagesRequest($request_options, $requireTariffCode);

		if ($validation !== null)
		{
			return $validation;
		}

		return $this->request->getResponse($endpoint, $request_options, 'POST');
	}
}

