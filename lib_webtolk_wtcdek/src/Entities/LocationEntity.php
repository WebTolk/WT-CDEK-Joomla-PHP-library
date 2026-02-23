<?php
/**
 * LocationEntity API entity.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

use function array_filter;
use function array_merge;
use function implode;
use function is_array;

defined('_JEXEC') or die;

final class LocationEntity extends AbstractEntity
{
	/**
	 * GET /v2/location/regions
	 *
	 * Получение списка регионов
	 *
	 * **Описание:**
	 * Метод предназначен для получения детальной информации о регионах.
	 *
	 * Список регионов может быть ограничен характеристиками, задаваемыми пользователем.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/location/operation/regions
	 *
	 * @param   array  $request_options  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getRegions(array $request_options = []): array
	{
		$options = [
			'country_codes' => [],
			'size'          => 1000,
			'page'          => 0,
			'lang'          => 'rus',
		];
		$options = array_filter(array_merge($options, $request_options));

		if (!empty($options['country_codes']) && is_array($options['country_codes']))
		{
			$options['country_codes'] = implode(',', $options['country_codes']);
		}

		return $this->request->getResponse('/location/regions', $options, 'GET');
	}

	/**
	 * GET /v2/location/cities
	 *
	 * Получение списка населенных пунктов
	 *
	 * **Описание:**
	 * Метод предназначен для получения детальной информации о населенных пунктах.
	 *
	 * Список населенных пунктов может быть ограничен характеристиками, задаваемыми пользователем.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/location/operation/cities
	 *
	 * @param   string  $city_name  City name to use for suggestions.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getCities(array $request_options = []): array
	{
		$options = [
			'country_codes' => [],
			'size'          => 500,
			'page'          => 0,
			'lang'          => 'rus',
		];
		$options = array_filter(array_merge($options, $request_options));

		if (!empty($options['country_codes']) && is_array($options['country_codes']))
		{
			$options['country_codes'] = implode(',', $options['country_codes']);
		}

		return $this->request->getResponse('/location/cities', $options, 'GET');
	}

	/**
	 * GET /v2/location/postalcodes
	 *
	 * Получение почтовых индексов города
	 *
	 * **Описание:**
	 * Метод предназначен для получения списка почтовых индексов.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/location/operation/postalcodes
	 *
	 * @param   int  $city_code  Request data.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getPostalCodes(int $city_code): array
	{
		if (empty($city_code))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'There is no city code specified',
			];
		}

		return $this->request->getResponse('/location/postalcodes', ['city_code' => $city_code], 'GET');
	}

	/**
	 * GET /v2/location/coordinates
	 *
	 * Получение локации по координатам
	 *
	 * **Описание:**
	 * Метод позволяет определить локацию по переданным в запросе координатам
	 *
	 * Source: https://apidoc.cdek.ru/#tag/location/operation/getCityByCoordinates
	 *
	 * @param   array  $data  Request data. Supported coordinate keys:
	 *                        `latitude` (or `lat`) and `longitude` (or `lng`/`lon`).
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function getCityByCoordinates(array $data = []): array
	{
		if (!isset($data['latitude']) && isset($data['lat']))
		{
			$data['latitude'] = $data['lat'];
		}

		if (!isset($data['longitude']) && isset($data['lng']))
		{
			$data['longitude'] = $data['lng'];
		}

		if (!isset($data['longitude']) && isset($data['lon']))
		{
			$data['longitude'] = $data['lon'];
		}

		if (!isset($data['latitude']) || !isset($data['longitude']))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required options: latitude, longitude',
			];
		}

		return $this->request->getResponse('/location/coordinates', $data, 'GET');
	}


	/**
	 * GET /v2/location/suggest/cities
	 *
	 * Подбор локации по названию города
	 *
	 * **Описание:**
	 * Метод позволяет получать подсказки по подбору населенного пункта по его наименованию.
	 *
	 * Список населенных пунктов может быть ограничен характеристиками, задаваемыми пользователем.
	 *
	 * Source: https://apidoc.cdek.ru/#tag/location/operation/suggestCities
	 *
	 * @param   string  $city_name  City name to use for suggestions.
	 *
	 * @return  array  API response.
	 *
	 * @since  1.3.0
	 */
	public function suggestCities(string $city_name): array
	{
		if (empty($city_name))
		{
			return [
				'error_code'    => '500',
				'error_message' => 'Required option: city_name',
			];
		}

		return $this->request->getResponse('/location/suggest/cities', ['name' => $city_name], 'GET');
	}

}

