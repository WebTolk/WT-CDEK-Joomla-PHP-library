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
	 * Legacy-compatible method for all tariffs list.
	 *
	 * @return  array
	 *
	 * @since  1.3.0
	 */
	public function getAllTariffs(): array
	{
		$result = $this->request->getResponse('/calculator/alltariffs', [], 'GET');

		return $result['tariff_codes'] ?? [];
	}

	/**
	 * Legacy-compatible method for tariff calculation.
	 *
	 * @param   array  $request_options  Request options.
	 *
	 * @return  array
	 *
	 * @since  1.3.0
	 */
	public function calculateTariff(array $request_options = []): array
	{
		$validation = $this->validatePackagesRequest($request_options);

		if ($validation !== null)
		{
			return $validation;
		}

		return $this->request->getResponse('/calculator/tariff', $request_options, 'POST');
	}

	/**
	 * Validates required calculator request options.
	 *
	 * @param   array  $request_options  Request options.
	 *
	 * @return  array|null
	 *
	 * @since  1.3.0
	 */
	private function validatePackagesRequest(array $request_options): ?array
	{
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
	 * Legacy-compatible method for tariff list calculation.
	 *
	 * @param   array  $request_options  Request options.
	 *
	 * @return  array
	 *
	 * @since  1.3.0
	 */
	public function calculateTariffList(array $request_options = []): array
	{
		$validation = $this->validatePackagesRequest($request_options);

		if ($validation !== null)
		{
			return $validation;
		}

		return $this->request->getResponse('/calculator/tarifflist', $request_options, 'POST');
	}

	/**
	 * GET /v2/calculator/alltariffs
	 *
	 * Список доступных тарифов
	 *
	 * **Описание:**
	 * Метод позволяет получить список всех доступных и актуальных тарифов по договору.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/calculator/operation/availableTariffs
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function availableTariffs(array $data = []): array
	{
		return $this->request->getResponse('/calculator/alltariffs', $data, 'GET');
	}

	/**
	 * POST /v2/calculator/tariff
	 *
	 * Расчет по коду тарифа
	 *
	 * **Описание:**
	 * Метод используется для расчета стоимости и сроков доставки по конкретному коду тарифа по указанному
	 * направлению с учетом весо-габаритных характеристик груза. В ответе предоставляется информация о
	 * доступных тарифах, их стоимости и сроках доставки.
	 * В данном методе возможно произвести расчет стоимость доставки с учетом стоимости дополнительных услуг.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/calculator/operation/tariff
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function tariff(array $data = []): array
	{
		$validation = $this->validatePackagesRequest($data);

		if ($validation !== null)
		{
			return $validation;
		}

		return $this->request->getResponse('/calculator/tariff', $data, 'POST');
	}

	/**
	 * POST /v2/calculator/tariffAndService
	 *
	 * Расчет по доступным тарифам и дополнительным услугам
	 *
	 * **Описание:**
	 * Метод используется клиентами для расчета стоимости и сроков доставки по доступным тарифам, с учётом
	 * переданных дополнительных услуг.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/calculator/operation/tariffWithServices
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function tariffWithServices(array $data = []): array
	{
		$validation = $this->validatePackagesRequest($data);

		if ($validation !== null)
		{
			return $validation;
		}

		return $this->request->getResponse('/calculator/tariffAndService', $data, 'POST');
	}

	/**
	 * POST /v2/calculator/tarifflist
	 *
	 * Расчет по доступным тарифам
	 *
	 * **Описание:**
	 * Метод используется клиентами для расчета стоимости и сроков доставки по всем доступным тарифам.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/calculator/operation/tariffList
	 *
	 * @param   array  $data  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function tariffList(array $data = []): array
	{
		$validation = $this->validatePackagesRequest($data);

		if ($validation !== null)
		{
			return $validation;
		}

		return $this->request->getResponse('/calculator/tarifflist', $data, 'POST');
	}
}

