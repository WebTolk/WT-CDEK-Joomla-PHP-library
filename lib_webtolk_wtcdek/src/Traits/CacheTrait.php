<?php
/**
 * Joomla cache support for CDEK API data.
 * @package       WT Cdek library package
 * @subpackage    Webtolk/Cdekapi
 * @author        Sergey Tolkachyov, WebTolk
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @version       __DEPLOY_VERSION__
 * @license       GNU General Public License version 3 or later.
 * @link          https://web-tolk.ru
 * @since         1.3.0
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Traits;

defined('_JEXEC') or die;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\OutputController;
use Joomla\CMS\Factory;
use function array_merge;

trait CacheTrait
{
	/**
	 * Возвращает настроенный контроллер кэша.
	 *
	 * @param   array  $cache_options  Дополнительные параметры кэша.
	 *
	 * @return  OutputController
	 *
	 * @since 1.3.0
	 */
	public function getCache(array $cache_options = []): OutputController
	{
		$config  = Factory::getContainer()->get('config');
		$options = [
			'defaultgroup' => 'wt_cdek',
			'caching'      => true,
			'cachebase'    => $config->get('cache_path'),
			'storage'      => $config->get('cache_handler'),
		];
		$options = array_merge($options, $cache_options);

		return Factory::getContainer()
			->get(CacheControllerFactoryInterface::class)
			->createCacheController('output', $options);
	}
}
