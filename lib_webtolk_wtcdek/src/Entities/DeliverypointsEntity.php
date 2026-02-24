<?php
/**
 * Сущность API СДЭК: офисы.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

use function array_filter;
use function array_merge;

defined('_JEXEC') or die;

final class DeliverypointsEntity extends AbstractEntity
{
	/**
	 * GET /v2/deliverypoints
	 *
	 * Получение списка офисов
	 *
	 * **Описание:**
	 * Метод предназначен для получения списка действующих офисов СДЭК.
	 *
	 * Мы не рекомендуем использовать статичный список офисов, так как офисы могут быть неактуальны.
	 * Рекомендуется обновлять список офисов не реже одного раза в сутки.
	 *
	 * Источник: https://apidoc.cdek.ru/#tag/delivery_point/operation/search
	 *
	 * @param   array{
	 *             postal_code?: int|string,
	 *             city_code?: int|string,
	 *             type?: 'PVZ'|'POSTAMAT'|'ALL',
	 *             country_code?: string,
	 *             region_code?: int|string,
	 *             have_cashless?: bool|int|string,
	 *             have_cash?: bool|int|string,
	 *             allowed_cod?: bool|int|string,
	 *             is_dressing_room?: bool|int|string,
	 *             weight_max?: int|float|string,
	 *             weight_min?: int|float|string,
	 *             lang?: string,
	 *             take_only?: bool|int|string,
	 *             is_handout?: bool|int|string,
	 *             is_reception?: bool|int|string,
	 *             fias_guid?: string
	 *         }  $request_options  Параметры фильтрации офисов.
	 *
	 * @return  array
	 *
	 * @since  1.3.0
	 */
	public function getDeliveryPoints(array $request_options = []): array
	{
		$options = [
			'postal_code'      => '',
			'city_code'        => '',
			'type'             => 'ALL',
			'country_code'     => '',
			'region_code'      => '',
			'have_cashless'    => '',
			'have_cash'        => '',
			'allowed_cod'      => '',
			'is_dressing_room' => '',
			'weight_max'       => '',
			'weight_min'       => '',
			'lang'             => 'rus',
			'take_only'        => '',
			'is_handout'       => '',
			'is_reception'     => '',
			'fias_guid'        => '',
		];

		$options = array_filter(array_merge($options, $request_options));
		$cache = $this->request->getCache();
		$cacheKey = 'deliverypoints_' . md5((string) json_encode($options));
		$cachedData = $cache->get($cacheKey);

		if (!empty($cachedData))
		{
			$decoded = json_decode((string) $cachedData, true);

			if (is_array($decoded))
			{
				return $decoded;
			}
		}

		$response = $this->request->getResponse('/deliverypoints', $options, 'GET');
		$cache->store((string) json_encode($response, JSON_UNESCAPED_UNICODE), $cacheKey);

		return $response;
	}

}

