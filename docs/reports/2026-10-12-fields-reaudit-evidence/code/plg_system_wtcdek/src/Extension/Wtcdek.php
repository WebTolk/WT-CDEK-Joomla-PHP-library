<?php
/**
 * @package       WT Cdek library package
 * @version       1.3.1
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
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Language\Text;
use JLoader;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\WebAsset\WebAssetRegistry;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Webtolk\Cdekapi\Cdek;
use Webtolk\Cdekapi\Service\DeliverypointsService;


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
			'onAjaxWtcdekdeliverypoints' => 'onAjaxWtcdekdeliverypoints',
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
	 * Read-only delivery-point lookup for library fields in site and administrator forms.
	 * The POST token is checked before constructing a client or calling the provider.
	 *
	 * @param   Event  $event  Joomla 4 generic or Joomla 5/6 result-aware AJAX event.
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAjaxWtcdekdeliverypoints(Event $event): void
	{
		$app = $this->getApplication();
		$input = $app->getInput();

		if (!$app->isClient('site') && !$app->isClient('administrator'))
		{
			throw new \RuntimeException('Unsupported application.', 403);
		}

		if ($input->getMethod() !== 'POST')
		{
			throw new \RuntimeException('POST required.', 405);
		}

		// Require the posted token explicitly; checkToken may redirect and return true on a new session.
		if ($input->post->getInt(Session::getFormToken(), 0) !== 1 || !Session::checkToken('post'))
		{
			throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
		}

		$app->getLanguage()->load('lib_webtolk_cdekapi', JPATH_SITE);
		$city = trim($input->post->getString('city', ''));
		$postalCode = trim($input->post->getString('postal_code', ''));

		if (strlen($city) > 200 || strlen($postalCode) > 20)
		{
			throw new \InvalidArgumentException(Text::_('LIB_WTCDEK_FIELD_DELIVERYPOINTS_ERROR'), 400);
		}

		try
		{
			$filters = DeliverypointsService::normalizeFilters([
				'type' => $input->post->getString('type', 'ALL'),
				'is_reception' => $input->post->getString('is_reception', ''),
				'is_handout' => $input->post->getString('is_handout', ''),
			]);
		}
		catch (\InvalidArgumentException $exception)
		{
			throw new \InvalidArgumentException(Text::_('LIB_WTCDEK_FIELD_DELIVERYPOINTS_FILTER_INVALID'), 400);
		}

		try
		{
			$fiasGuid = DeliverypointsService::normalizeFiasGuid($input->post->getString('fias_guid', ''));
		}
		catch (\InvalidArgumentException $exception)
		{
			throw new \InvalidArgumentException(Text::_('LIB_WTCDEK_FIELD_DELIVERYPOINTS_FIAS_INVALID'), 400);
		}

		$options = [];

		if ($city !== '' || $postalCode !== '' || $fiasGuid !== '')
		{
			try
			{
				$options = (new DeliverypointsService())->getOptions(new Cdek(), $city, $postalCode, $filters, $fiasGuid);
			}
			catch (\Throwable $exception)
			{
				throw new \RuntimeException(Text::_('LIB_WTCDEK_FIELD_DELIVERYPOINTS_ERROR'), 502);
			}
		}

		$result = ['options' => $options];

		if ($event instanceof ResultAwareInterface)
		{
			$event->addResult($result);
		}
		else
		{
			$results = $event->getArgument('result', []);
			$results[] = $result;
			$event->setArgument('result', $results);
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
