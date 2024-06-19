<?php
/**
 * Library to connect to CDEK service.
 * @package     Webtolk\Cdek
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version     1.0.0
 * @license     GNU General Public License version 3 or later. Only for *.php files!
 * @link        https://api-docs.cdek.ru/29923741.html
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
 * @since  2.5
 */
class Wtcdek extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  3.7.0
	 */
	protected $app;

	/**
	 * The document.
	 *
	 * @var Document
	 *
	 * @since  4.0.0
	 */
	private $document;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterRoute' => 'addCdekWidgetAsset',
			'onAjaxWtcdek' => 'onAjaxWtcdek',
		];
	}

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
				$result = $cdek->getDeliveryPoints($data);
				$event->setArgument('result', json_encode($result));
			}
			elseif ($action == 'calculate')
			{
				$result = $cdek->getCalculatorTarifflist($data);
				$event->setArgument('result', json_encode($result));
			}
			else
			{
				$event->setArgument('result', json_encode(['message' => 'Unknown action']));
			}
		}
	}
}
