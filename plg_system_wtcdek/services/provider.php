<?php
/**
 * Dependency injection provider for the WT CDEK system plugin.
 * @package       WT Cdek library package
 * @subpackage    plg_system_wtcdek
 * @author        Sergey Tolkachyov, WebTolk
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @version       __DEPLOY_VERSION__
 * @license       GNU General Public License version 3 or later.
 * @link          https://web-tolk.ru
 * @since         1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Wtcdek\Extension\Wtcdek;

return new class implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since 1.3.0
	 */
	public function register(Container $container)
	{
		/**
		 * Set a resource to the container. If the value is null the resource is removed.
		 *
		 * @param   string   $key        Name of resources key to set.
		 * @param   mixed    $value      Callable function to run or string to retrive when requesting the specified $key.
		 * @param   boolean  $shared     True to create and store a shared instance.
		 * @param   boolean  $protected  True to protect this item from being overwritten. Useful for services.
		 */
		$container->set(
			PluginInterface::class,
			function (Container $container) {
				$subject = $container->get(DispatcherInterface::class);
				$config  = (array) PluginHelper::getPlugin('system', 'wtcdek');
				$plugin = new Wtcdek($subject, $config);
				$plugin->setApplication(\Joomla\CMS\Factory::getApplication());
				return $plugin;
			}
		);
	}
};
