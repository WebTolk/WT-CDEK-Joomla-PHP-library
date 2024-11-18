<?php

/**
 * @package    WT Cdek library package
 * @subpackage      Task.deleteactionlogs
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\Updatewtcdekdata\Extension;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Webtolk\Cdekapi\Cdek;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A task plugin. For Delete Action Logs after x days
 * {@see ExecuteTaskEvent}.
 *
 * @since 5.0.0
 */
final class Updatewtcdekdata extends CMSPlugin implements SubscriberInterface
{
	use DatabaseAwareTrait;
	use TaskPluginTrait;

	/**
	 * @var string[]
	 * @since 5.0.0
	 */
	private const TASKS_MAP = [
		'plg_task_updatewtcdekdata' => [
			'langConstPrefix' => 'PLG_TASK_UPDATEWTCDEKDATA',
			'method'          => 'UpdateWtCdekData',
			'form'            => 'updatewtcdekdata',
		],
	];

	/**
	 * @var boolean
	 * @since 5.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @since 5.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'standardRoutineHandler',
			'onContentPrepareForm' => 'enhanceTaskItemForm',
		];
	}

	/**
	 * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
	 *
	 * @return integer  The routine exit code.
	 *
	 * @throws \Exception
	 * @since  5.0.0
	 */
	private function UpdateWtCdekData(ExecuteTaskEvent $event): int
	{
		/** @var Registry $params Current task params */
		$task_params = new Registry($event->getArgument('params'));
		/** @var int $task_id The task id */
		$task_id = $event->getTaskId();
		/** @var bool $result Task result */
		$result = false;

		$cdek = new Cdek();

		if ($task_params->get('update_regions', 1))
		{
			$result = $this->updateLocationRegions($cdek, $task_params);
			if(!$result)
			{
				$this->logTask('There is error while update location regions from CDEK API');
				return Status::KNOCKOUT;
			}
		}

		if ($task_params->get('update_cities', 1))
		{
			$result = $this->updateLocationCities($cdek, $task_params);
			if(!$result)
			{
				$this->logTask('There is error while update location cities from CDEK API');
				return Status::KNOCKOUT;
			}
		}

		$result = $this->updateDeliverypoints($cdek, $task_params);

		return $result ? Status::OK : Status::KNOCKOUT;
	}


	/**
	 * Обновляем страны и области доставки
	 *
	 * @param   Cdek      $cdek
	 * @param   Registry  $task_params  параметры задачи
	 *
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	private function updateLocationRegions($cdek, $task_params): bool
	{
		$size      = $task_params->get('request_data_size', 1000);
		$countries = $task_params->get('countries_for_update', ['RU']);
		$page      = 0;
		$result    = false;
		$nowDate   = (new Date('now'))->toSql();

		$request_options = ['size' => $size];
		if (!\in_array('--', $countries))
		{
			$request_options['country_codes'] = $countries;
		}

		$this->clearTable('#__lib_wtcdek_location_regions');
		do
		{
			$request_options['page'] = $page;
			$regions                 = $cdek->getLocationRegions($request_options);
			$page++;

			$db = $this->getDatabase();

			$columns = [
				$db->quoteName('country_code'),
				$db->quoteName('country'),
				$db->quoteName('region'),
				$db->quoteName('region_code'),
				$db->quoteName('date_modified'),
			];


			$values = [];

			foreach ($regions as $region)
			{
				$regionData = [
					'country_code'  => ((array_key_exists(
							'country_code',
							$region
						) && !empty($region['country_code'])) ? $region['country_code'] : null),
					'country'       => ((array_key_exists(
							'country',
							$region
						) && !empty($region['country'])) ? $region['country'] : null),
					'region'        => ((array_key_exists(
							'region',
							$region
						) && !empty($region['region'])) ? $region['region'] : null),
					'region_code'   => ((array_key_exists(
							'region_code',
							$region
						) && !empty($region['region_code'])) ? $region['region_code'] : 0),
					'date_modified' => $nowDate,
				];

				$values[] = '(' . implode(',', $db->quote($regionData)) . ')';
			}

			$query = 'INSERT INTO ' . $db->quoteName('#__lib_wtcdek_location_regions') . ' (' . implode(
					', ',
					$columns
				) . ') VALUES ' . implode(',', $values);

			$db->setQuery($query);
			try
			{
				$result = $db->execute();
			}
			catch (\RuntimeException $e)
			{
				$this->logTask($e->getMessage(), 'error');
				$result = false;
			}
		} while (\count($regions) == $size);

		return $result;
	}

	/**
	 * @param   string  $table  Table name to TRUNCATE
	 *
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	private function clearTable(string $table): bool
	{
		if (empty($table))
		{
			return false;
		}

		$db    = $this->getDatabase();
		$query = 'TRUNCATE ' . $db->quoteName($table);

		return $db->setQuery($query)->execute();
	}

	/**
	 * Обновляем страны и области доставки
	 *
	 * @param   Cdek      $cdek
	 * @param   Registry  $task_params  параметры задачи
	 *
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	private function updateLocationCities($cdek, $task_params): bool
	{
		$size      = $task_params->get('request_data_size', 1000);
		$countries = $task_params->get('countries_for_update', ['RU']);
		$page      = 0;
		$result    = false;
		$nowDate   = (new Date('now'))->toSql();

		$request_options = ['size' => $size];
		if (!\in_array('--', $countries))
		{
			$request_options['country_codes'] = $countries;
		}
		$this->clearTable('#__lib_wtcdek_location_cities');

		do
		{
			$request_options['page'] = $page;
			$cities                  = $cdek->getLocationCities($request_options);
			$page++;

			$db = $this->getDatabase();

			$columns = [
				$db->quoteName('code'),
				$db->quoteName('city_uuid'),
				$db->quoteName('city'),
				$db->quoteName('country_code'),
				$db->quoteName('country'),
				$db->quoteName('region'),
				$db->quoteName('region_code'),
				$db->quoteName('postal_codes'),
				$db->quoteName('sub_region'),
				$db->quoteName('longitude'),
				$db->quoteName('latitude'),
				$db->quoteName('date_modified'),
			];

			$values = [];
			foreach ($cities as $city)
			{

				$cityData = [
					'code'          => ((array_key_exists('code', $city) && !empty($city['code'])) ? $city['code'] : null),
					'city_uuid'     => ((array_key_exists('city_uuid', $city) && !empty($city['city_uuid'])) ? $city['city_uuid'] : null),
					'city'          => ((array_key_exists('city', $city) && !empty($city['city'])) ? $city['city'] : null),
					'country_code'  => ((array_key_exists('country_code', $city) && !empty($city['country_code'])) ? $city['country_code'] : null),
					'country'       => ((array_key_exists('country', $city) && !empty($city['country'])) ? $city['country'] : null),
					'region'        => ((array_key_exists('region', $city) && !empty($city['region'])) ? $city['region'] : null),
					'region_code'   => ((array_key_exists('region_code', $city) && !empty($city['region_code'])) ? $city['region_code'] : null),
					'postal_codes'  => null, // очень медленно работает получение индексов для каждого города
					'sub_region'    => ((array_key_exists('sub_region', $city) && !empty($city['sub_region'])) ? $city['sub_region'] : null),
					'longitude'     => ((array_key_exists('longitude', $city) && !empty($city['longitude'])) ? $city['longitude'] : null),
					'latitude'      => ((array_key_exists('latitude', $city) && !empty($city['latitude'])) ? $city['latitude'] : null),
					'date_modified' => $nowDate,
				];
				$values[] = '(' . implode(',', $db->quote($cityData)) . ')';

			}

			$query = 'INSERT INTO ' . $db->quoteName('#__lib_wtcdek_location_cities') . ' (' . implode(
					', ',
					$columns
				) . ') VALUES ' . implode(',', $values);

			$db->setQuery($query);

			try
			{
				$result = $db->execute();
			}
			catch (\RuntimeException $e)
			{
				$this->logTask($e->getMessage(), 'error');
				$result = false;
			}


		} while (\count($cities) == $size);

		return $result;
	}


	/**
	 * Обновляем страны и области доставки
	 *
	 * @param   Cdek      $cdek
	 * @param   Registry  $task_params  параметры задачи
	 *
	 * @return bool
	 *
	 * @since 1.1.0
	 * @link  https://api-docs.cdek.ru/36982648.html
	 */

	private function updateDeliverypoints($cdek, $task_params): bool
	{
		$countries = $task_params->get('countries_for_update', ['RU']);

		$this->clearTable('#__lib_wtcdek_delivery_points');

		if (empty($countries) || \in_array('--', $countries))
		{
			// All delivery points from whole world
			$result = $this->getDeliveryPoints($cdek, $task_params, '');
		}
		else
		{
			foreach ($countries as $country)
			{
				$result = $this->getDeliveryPoints($cdek, $task_params, $country);
				if (!$result)
				{
					return $result;
				}
			}
		}


		return $result;
	}


	private function getDeliveryPoints($cdek, $task_params, $country_code = '')
	{
		$size = $task_params->get('request_data_size', 1000);

		$page            = 0;
		$result          = false;
		$nowDate         = (new Date('now'))->toSql();
		$request_options = ['size' => $size];
		if (!empty($country_code))
		{
			$request_options['country_code'] = $country_code;
		}

		do
		{
			$request_options['page'] = $page;
			$deliveryPoints          = $cdek->getDeliveryPoints($request_options);
			if (!empty($deliveryPoints))
			{
				$page++;

				$db = $this->getDatabase();

				$columns = [
					$db->quoteName('code'),
					$db->quoteName('name'),
					$db->quoteName('uuid'),
					$db->quoteName('location'),
					$db->quoteName('address_comment'),
					$db->quoteName('nearest_station'),
					$db->quoteName('nearest_metro_station'),
					$db->quoteName('work_time'),
					$db->quoteName('phones'),
					$db->quoteName('email'),
					$db->quoteName('note'),
					$db->quoteName('type'),
					$db->quoteName('owner_code'),
					$db->quoteName('take_only'),
					$db->quoteName('is_handout'),
					$db->quoteName('is_reception'),
					$db->quoteName('is_dressing_room'),
					$db->quoteName('have_cashless'),
					$db->quoteName('have_cash'),
					$db->quoteName('have_fast_payment_system'),
					$db->quoteName('allowed_cod'),
					$db->quoteName('is_ltl'),
					$db->quoteName('fulfillment'),
					$db->quoteName('site'),
					$db->quoteName('office_image_list'),
					$db->quoteName('work_time_list'),
					$db->quoteName('work_time_exception_list'),
					$db->quoteName('weight_min'),
					$db->quoteName('weight_max'),
					$db->quoteName('dimensions'),
					$db->quoteName('date_modified'),
				];

				$values = [];
				foreach ($deliveryPoints as $deliveryPoint)
				{

					$deliveryPointData = [
						'code'                     => ((array_key_exists('code', $deliveryPoint) && !empty($deliveryPoint['code'])) ? $deliveryPoint['code'] : null),
						'name'                     => ((array_key_exists('name', $deliveryPoint) && !empty($deliveryPoint['name'])) ? $deliveryPoint['name'] : null),
						'uuid'                     => ((array_key_exists('uuid', $deliveryPoint) && !empty($deliveryPoint['uuid'])) ? $deliveryPoint['uuid'] : null),
						'location'                 => ((array_key_exists('location', $deliveryPoint) && !empty($deliveryPoint['location'])) ? (new Registry($deliveryPoint['location']))->toString() : null),
						'address_comment'          => ((array_key_exists('address_comment', $deliveryPoint) && !empty($deliveryPoint['address_comment'])) ? $deliveryPoint['address_comment'] : null),
						'nearest_station'          => ((array_key_exists('nearest_station', $deliveryPoint) && !empty($deliveryPoint['nearest_station'])) ? $deliveryPoint['nearest_station'] : null),
						'nearest_metro_station'    => ((array_key_exists('nearest_metro_station', $deliveryPoint) && !empty($deliveryPoint['nearest_metro_station'])) ? $deliveryPoint['nearest_metro_station'] : null),
						'work_time'                => ((array_key_exists('work_time', $deliveryPoint) && !empty($deliveryPoint['work_time'])) ? $deliveryPoint['work_time'] : null),
						'phones'                   => ((array_key_exists('phones', $deliveryPoint) && !empty($deliveryPoint['phones'])) ? (new Registry($deliveryPoint['phones']))->toString() : null),
						'email'                    => ((array_key_exists('email', $deliveryPoint) && !empty($deliveryPoint['email'])) ? $deliveryPoint['email'] : null),
						'note'                     => ((array_key_exists('note', $deliveryPoint) && !empty($deliveryPoint['note'])) ? $deliveryPoint['note'] : null),
						'type'                     => ((array_key_exists('type', $deliveryPoint) && !empty($deliveryPoint['type'])) ? $deliveryPoint['type'] : null),
						'owner_code'               => ((array_key_exists('owner_code', $deliveryPoint) && !empty($deliveryPoint['owner_code'])) ? $deliveryPoint['owner_code'] : null),
						'take_only'                => ((array_key_exists('take_only', $deliveryPoint) && !empty($deliveryPoint['take_only'])) ? (int) $deliveryPoint['take_only'] : 0),
						'is_handout'               => ((array_key_exists('is_handout', $deliveryPoint) && !empty($deliveryPoint['is_handout'])) ? (int) $deliveryPoint['is_handout'] : 0),
						'is_reception'             => ((array_key_exists('is_reception', $deliveryPoint) && !empty($deliveryPoint['is_reception'])) ? (int) $deliveryPoint['is_reception'] : 0),
						'is_dressing_room'         => ((array_key_exists('is_dressing_room', $deliveryPoint) && !empty($deliveryPoint['is_dressing_room'])) ? (int) $deliveryPoint['is_dressing_room'] : 0),
						'have_cashless'            => ((array_key_exists('have_cashless', $deliveryPoint) && !empty($deliveryPoint['have_cashless'])) ? (int) $deliveryPoint['have_cashless'] : 0),
						'have_cash'                => ((array_key_exists('have_cash', $deliveryPoint) && !empty($deliveryPoint['have_cash'])) ? (int) $deliveryPoint['have_cash'] : 0),
						'have_fast_payment_system' => ((array_key_exists('have_fast_payment_system', $deliveryPoint) && !empty($deliveryPoint['have_fast_payment_system'])) ? (int) $deliveryPoint['have_fast_payment_system'] : 0),
						'allowed_cod'              => ((array_key_exists('allowed_cod', $deliveryPoint) && !empty($deliveryPoint['allowed_cod'])) ? (int) $deliveryPoint['allowed_cod'] : 0),
						'is_ltl'                   => ((array_key_exists('is_ltl', $deliveryPoint) && !empty($deliveryPoint['is_ltl'])) ? (int) $deliveryPoint['is_ltl'] : 0),
						'fulfillment'              => ((array_key_exists('fulfillment', $deliveryPoint) && !empty($deliveryPoint['fulfillment'])) ? (int) $deliveryPoint['fulfillment'] : 0),
						'site'                     => ((array_key_exists('site', $deliveryPoint) && !empty($deliveryPoint['site'])) ? $deliveryPoint['site'] : null),
						'office_image_list'        => ((array_key_exists('office_image_list', $deliveryPoint) && !empty($deliveryPoint['office_image_list'])) ? (new Registry($deliveryPoint['office_image_list']))->toString() : null),
						'work_time_list'           => ((array_key_exists('work_time_list', $deliveryPoint) && !empty($deliveryPoint['work_time_list'])) ? (new Registry($deliveryPoint['work_time_list']))->toString() : null),
						'work_time_exception_list' => ((array_key_exists('work_time_exception_list', $deliveryPoint) && !empty($deliveryPoint['work_time_exception_list'])) ? (new Registry($deliveryPoint['work_time_exception_list']))->toString() : null),
						'weight_min'               => ((array_key_exists('weight_min', $deliveryPoint) && !empty($deliveryPoint['weight_min'])) ? (float) $deliveryPoint['weight_min'] : 0),
						'weight_max'               => ((array_key_exists('weight_max', $deliveryPoint) && !empty($deliveryPoint['weight_max'])) ? (float) $deliveryPoint['weight_max'] : 0),
						'dimensions'               => ((array_key_exists('dimensions', $deliveryPoint) && !empty($deliveryPoint['dimensions'])) ? (new Registry($deliveryPoint['dimensions']))->toString() : null),
						'date_modified'            => $nowDate,
					];
					$values[]          = '(' . implode(',', $db->quote($deliveryPointData)) . ')';

				}

				$query = 'INSERT INTO ' . $db->quoteName('#__lib_wtcdek_delivery_points') . ' (' . implode(
						', ',
						$columns
					) . ') VALUES ' . implode(',', $values);

				$db->setQuery($query);

				try
				{
					$result = $db->execute();
				}
				catch (\RuntimeException $e)
				{
					$this->logTask($e->getMessage(), 'error');
					$result = false;
				}
			}

		} while (\count($deliveryPoints) == $size);

		return $result;
	}
}
