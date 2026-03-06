<?php
/**
 * @package       WT Cdek library package
 * @version       1.3.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Plugin\System\Wtcdek\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Document\Document;
use JLoader;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\WebAsset\WebAssetRegistry;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Webtolk\Cdekapi\Cdek;


/**
 *
 *
 * @since 1.3.0
 */
class Wtcdek extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since 1.3.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since 1.3.0
	 */
	protected $app;

	/**
	 * The document.
	 *
	 * @var Document
	 *
	 * @since 1.3.0
	 */
	private $document;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since 1.3.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterRoute' => 'addCdekWidgetAsset',
			'onAjaxWtcdek' => 'onAjaxWtcdek',
		];
	}

	/**
	 * @since 1.3.0
	 */
	public function addCdekWidgetAsset(): void
	{
		// Only trigger in frontend
		if (Factory::getApplication()->isClient('site'))
		{
			/** @var Joomla\CMS\WebAsset\WebAssetRegistry $wa */
			$wa = Factory::getContainer()->get(WebAssetRegistry::class);
			$wa->addRegistryFile('media/lib_wtcdek/joomla.assets.json');
		}
	}

	/**
	 * Main ajax job. Send to Telegram via ajax.
	 * Send the array $article_ids here
	 *
	 * @param   Event  $event
	 *
	 *
	 * @since 1.0.0
	 */
	public function onAjaxWtcdek(Event $event): void
	{
		if (!$this->getApplication()->isClient('site'))
		{
			return;
		}

		if(!Session::checkToken())
		{
			$event->setArgument('result', json_encode(['message' => 'Wrong Joomla session token']));
		}

		$data = $this->getApplication()->getInput()->getArray();
		if (array_key_exists('action', $data) && !empty($data['action']))
		{
			$action = $data['action'];
			$cdek   = new Cdek();
			if ($action == 'offices')
			{
				$result = $cdek->deliverypoints()->getDeliveryPoints($data);
				$event->setArgument('result', json_encode($result));
			}
			elseif ($action == 'calculate')
			{
				$result = $cdek->calculator()->calculateTariffList($data);
				$event->setArgument('result', json_encode($result));
			}
			else
			{
				$event->setArgument('result', json_encode(['message' => 'Unknown action']));
			}
		}
	}
}
