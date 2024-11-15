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
        }

        if ($task_params->get('update_cities', 1))
        {
            $result = $this->updateLocationCities($cdek, $task_params);
        }


//        $daysToDeleteAfter = (int) $event->getArgument('params')->logDeletePeriod ?? 0;
//        $this->logTask(sprintf('Delete Logs after %d days', $daysToDeleteAfter));
//        $now               = Factory::getDate()->toSql();
//        $db                = $this->getDatabase();
//        $query             = $db->getQuery(true);
//
//        if ($daysToDeleteAfter > 0) {
//            $days = -1 * $daysToDeleteAfter;
//
//            $query->clear()
//                ->delete($db->quoteName('#__action_logs'))
//                ->where($db->quoteName('log_date') . ' < ' . $query->dateAdd($db->quote($now), $days, 'DAY'));
//
//            $db->setQuery($query);
//
//            try {
//                $db->execute();
//            } catch (\RuntimeException $e) {
//                // Ignore it
//                return Status::KNOCKOUT;
//            }
//        }

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
        $size   = $task_params->get('request_data_size', 1000);
	    $countries = $task_params->get('countries_for_update', ['RU']);
        $page   = 0;
        $result = false;
	    $nowDate = (new Date('now'))->toSql();

	    $request_options = ['size' => $size];
	    if (!\in_array('--', $countries))
	    {
		    $request_options['country_codes'] = $countries;
	    }

        $this->clearTable('#__lib_wtcdek_location_regions');
        do
        {
            $request_options['page'] = $page;
			$regions = $cdek->getLocationRegions($request_options);
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
        }
        while (\count($regions) == $size);

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
	    $nowDate = (new Date('now'))->toSql();

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


        }
        while (\count($cities) == $size);

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
	 * @link https://api-docs.cdek.ru/36982648.html
     */

    private function updateDeliverypoints($cdek, $task_params): bool
    {
        $size      = $task_params->get('request_data_size', 1000);
        $countries = $task_params->get('countries_for_update', ['RU']);
        $page      = 0;
        $result    = false;
	    $nowDate = (new Date('now'))->toSql();

        $request_options = ['size' => $size];
        if (!\in_array('--', $countries))
        {
            $request_options['country_codes'] = $countries;
        }
        $this->clearTable('#__lib_wtcdek_location_cities');

//        do
//        {
//            $request_options['page'] = $page;
//            $cities                  = $cdek->getLocationCities($request_options);
//            $page++;
//
//            $db = $this->getDatabase();
//
//            $columns = [
//                $db->quoteName('code'),
//                $db->quoteName('city_uuid'),
//                $db->quoteName('city'),
//                $db->quoteName('country_code'),
//                $db->quoteName('country'),
//                $db->quoteName('region'),
//                $db->quoteName('region_code'),
//                $db->quoteName('postal_codes'),
//                $db->quoteName('sub_region'),
//                $db->quoteName('longitude'),
//                $db->quoteName('latitude'),
//                $db->quoteName('date_modified'),
//            ];
//
//	        $values = [];
//            foreach ($cities as $city)
//            {
//
//	            $cityData = [
//		            'code'          => ((array_key_exists('code', $city) && !empty($city['code'])) ? $city['code'] : null),
//		            'city_uuid'     => ((array_key_exists('city_uuid', $city) && !empty($city['city_uuid'])) ? $city['city_uuid'] : null),
//		            'city'          => ((array_key_exists('city', $city) && !empty($city['city'])) ? $city['city'] : null),
//		            'country_code'  => ((array_key_exists('country_code', $city) && !empty($city['country_code'])) ? $city['country_code'] : null),
//		            'country'       => ((array_key_exists('country', $city) && !empty($city['country'])) ? $city['country'] : null),
//		            'region'        => ((array_key_exists('region', $city) && !empty($city['region'])) ? $city['region'] : null),
//		            'region_code'   => ((array_key_exists('region_code', $city) && !empty($city['region_code'])) ? $city['region_code'] : null),
//		            'postal_codes'  => null, // очень медленно работает получение индексов для каждого города
//		            'sub_region'    => ((array_key_exists('sub_region', $city) && !empty($city['sub_region'])) ? $city['sub_region'] : null),
//		            'longitude'     => ((array_key_exists('longitude', $city) && !empty($city['longitude'])) ? $city['longitude'] : null),
//		            'latitude'      => ((array_key_exists('latitude', $city) && !empty($city['latitude'])) ? $city['latitude'] : null),
//		            'date_modified' => $nowDate,
//	            ];
//	            $values[] = '(' . implode(',', $db->quote($cityData)) . ')';
//
//            }
//
//	        $query = 'INSERT INTO ' . $db->quoteName('#__lib_wtcdek_location_cities') . ' (' . implode(
//			        ', ',
//			        $columns
//		        ) . ') VALUES ' . implode(',', $values);
//
//	        $db->setQuery($query);
//
//	        try
//	        {
//		        $result = $db->execute();
//	        }
//	        catch (\RuntimeException $e)
//	        {
//		        $this->logTask($e->getMessage(), 'error');
//		        $result = false;
//	        }
//
//
//        }
//        while (\count($cities) == $size);

        return $result;
    }
}
