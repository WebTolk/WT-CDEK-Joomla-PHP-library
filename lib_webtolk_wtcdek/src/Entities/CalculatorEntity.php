<?php
/**
 * CalculatorEntity API entity.
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
	 * Returns tariff codes available by the current contract.
	 *
	 * Endpoint: `GET /v2/calculator/alltariffs`
	 *
	 * The API response contains `tariff_codes`; this method returns that field only.
	 *
	 * @return  array<int, array<string, mixed>>  List of tariff codes metadata.
	 *
	 * @since  1.3.0
	 */
	public function getAllTariffs(): array
	{
		$result = $this->request->getResponse('/calculator/alltariffs', [], 'GET');

		return $result['tariff_codes'] ?? [];
	}

	/**
	 * Calculates delivery by a specific tariff code.
	 *
	 * Endpoint: `POST /v2/calculator/tariff`
	 *
	 * Required keys:
	 * - `tariff_code`
	 * - `from_location`
	 * - `to_location`
	 * - `packages` (each package must contain `weight`)
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
	 *         }  $request_options  Calculator request payload.
	 *
	 * @return  array  API response or structured validation error.
	 *
	 * @since  1.3.0
	 */
	public function calculateTariff(array $request_options = []): array
	{
		return $this->requestTariff('/calculator/tariff', $request_options, true);
	}

	/**
	 * Calculates delivery for all available tariffs.
	 *
	 * Endpoint: `POST /v2/calculator/tarifflist`
	 *
	 * Required keys:
	 * - `from_location`
	 * - `to_location`
	 * - `packages` (each package must contain `weight`)
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
	 *         }  $request_options  Calculator request payload.
	 *
	 * @return  array  API response or structured validation error.
	 *
	 * @since  1.3.0
	 */
	public function calculateTariffList(array $request_options = []): array
	{
		return $this->requestTariff('/calculator/tarifflist', $request_options);
	}

	/**
	 * Calculates delivery by available tariffs with additional services.
	 *
	 * Endpoint: `POST /v2/calculator/tariffAndService`
	 *
	 * Required keys:
	 * - `from_location`
	 * - `to_location`
	 * - `packages` (each package must contain `weight`)
	 *
	 * Services (`services[]`) details copied from facade docs:
	 * - `code`: service code from additional services dictionary.
	 * - `parameter`: service parameter meaning depends on `code`.
	 *   1) quantity for: `PACKAGE_1`, `COURIER_PACKAGE_A2`, `SECURE_PACKAGE_A2`,
	 *      `SECURE_PACKAGE_A3`, `SECURE_PACKAGE_A4`, `SECURE_PACKAGE_A5`,
	 *      `CARTON_BOX_XS`, `CARTON_BOX_S`, `CARTON_BOX_M`, `CARTON_BOX_L`,
	 *      `CARTON_BOX_500GR`, `CARTON_BOX_1KG`, `CARTON_BOX_2KG`,
	 *      `CARTON_BOX_3KG`, `CARTON_BOX_5KG`, `CARTON_BOX_10KG`,
	 *      `CARTON_BOX_15KG`, `CARTON_BOX_20KG`, `CARTON_BOX_30KG`,
	 *      `CARTON_FILLER`.
	 *   2) declared order value for `INSURANCE` (for "delivery" type orders only).
	 *   3) length for `BUBBLE_WRAP`, `WASTE_PAPER`.
	 *   4) number of photos for `PHOTO_DOCUMENT`.
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
	 *         }  $request_options  Calculator request payload.
	 *
	 * @return  array  API response or structured validation error.
	 *
	 * @since  1.3.1
	 */
	public function tariffAndService(array $request_options = []): array
	{
		return $this->requestTariff('/calculator/tariffAndService', $request_options);
	}

	/**
	 * Validates required calculator request options.
	 *
	 * @param   array  $request_options   Request options.
	 * @param   bool   $requireTariffCode Whether tariff code is required.
	 *
	 * @return  array|null  Validation error or `null` when valid.
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
	 * Executes calculator tariff-like request with common validation.
	 *
	 * @param   string  $endpoint          Calculator endpoint.
	 * @param   array   $request_options   Request options.
	 * @param   bool    $requireTariffCode Whether tariff code is required.
	 *
	 * @return  array  API response.
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

