<?php
/**
 * Library to connect to CDEK service.
 * @package    WT Cdek library package
 * @author     Sergey Tolkachyov
 * @copyright   Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version     1.3.0
 * @license     GNU General Public License version 3 or later. Only for *.php files!
 * @link       https://web-tolk.ru
 */

namespace Joomla\Plugin\System\Wtcdek\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Document\Document;
use JLoader;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\WebAsset\WebAssetRegistry;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Webtolk\Cdekapi\Cdek;
use Webtolk\Cdekapi\Event\WebhookEvent;
use function array_diff_key;
use function array_flip;
use function is_array;
use function json_decode;
use function json_encode;
use function trim;


/**
 *
 *
 * @since  2.5
 */
class Wtcdek extends CMSPlugin implements SubscriberInterface, DispatcherAwareInterface
{
	use DispatcherAwareTrait;

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
		$app = $this->getApplication();
		$action = $app->getInput()->getString('action', '');

		if (empty($action))
		{
			return;
		}

		$actionType = $app->getInput()->getString('action_type', 'internal');
		$result = [];

		if ($actionType === 'internal')
		{
			$result = $this->handleInternalAction($action);
		}
		else
		{
			$result = $this->handleExternalAction($action);
		}

		$event->setArgument('result', json_encode($result, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Обрабатывает внутренние действия Joomla (AJAX из интерфейса).
	 *
	 * @param   string  $action  Имя действия.
	 *
	 * @return  array<string, mixed>
	 *
	 * @since   1.3.1
	 */
	private function handleInternalAction(string $action): array
	{
		if (!Session::checkToken())
		{
			return ['success' => false, 'message' => Text::_('PLG_WTCDEK_AJAX_INVALID_TOKEN')];
		}

		$cdek = new Cdek();
		$input = $this->getApplication()->getInput();
		$data = $input->getArray();

		switch ($action)
		{
			case 'offices':
				return $cdek->deliverypoints()->getDeliveryPoints($data);

			case 'calculate':
				return $cdek->calculator()->calculateTariffList()($data);

			case 'webhook_list':
				$list = $cdek->webhooks()->getAll();
				return [
					'success' => !isset($list['error_code']),
					'message' => isset($list['error_code']) ? (string) ($list['error_message'] ?? Text::_('PLG_WTCDEK_AJAX_WEBHOOK_LIST_ERROR')) : Text::_('PLG_WTCDEK_AJAX_WEBHOOK_LIST_LOADED'),
					'data'    => $this->normalizeWebhookList($list),
				];

			case 'webhook_subscribe':
				$url = trim((string) $input->getString('url', ''));
				$type = trim((string) $input->getString('type', ''));
				$created = $cdek->webhooks()->create($url, $type);

				return [
					'success' => !isset($created['error_code']),
					'message' => isset($created['error_code']) ? (string) ($created['error_message'] ?? Text::_('PLG_WTCDEK_AJAX_WEBHOOK_SUBSCRIBE_ERROR')) : Text::_('PLG_WTCDEK_AJAX_WEBHOOK_SUBSCRIBED'),
					'data'    => $created,
				];

			case 'webhook_unsubscribe':
				$uuid = trim((string) $input->getString('uuid', ''));
				$deleted = $cdek->webhooks()->deleteByUuid($uuid);

				return [
					'success' => !isset($deleted['error_code']),
					'message' => isset($deleted['error_code']) ? (string) ($deleted['error_message'] ?? Text::_('PLG_WTCDEK_AJAX_WEBHOOK_UNSUBSCRIBE_ERROR')) : Text::_('PLG_WTCDEK_AJAX_WEBHOOK_UNSUBSCRIBED'),
					'data'    => $deleted,
				];

			default:
				return ['success' => false, 'message' => Text::_('PLG_WTCDEK_AJAX_UNKNOWN_ACTION')];
		}
	}

	/**
	 * Обрабатывает внешние входящие webhook от CDEK.
	 *
	 * @param   string  $action  Имя действия.
	 *
	 * @return  array<string, mixed>
	 *
	 * @since   1.3.1
	 */
	private function handleExternalAction(string $action): array
	{
		$allowWebhooks = (bool) $this->params->get('allow_cdek_webhooks', false);
		$tokenFromRequest = $this->getApplication()->getInput()->get->get('token', '', 'raw');
		$webhookToken = (string) $this->params->get('webhook_token', '');

		if (!$allowWebhooks)
		{
			return ['success' => false, 'message' => Text::_('PLG_WTCDEK_AJAX_WEBHOOKS_DISABLED')];
		}

		if (empty($tokenFromRequest) || empty($webhookToken) || $tokenFromRequest !== $webhookToken)
		{
			return ['success' => false, 'message' => Text::_('PLG_WTCDEK_AJAX_WEBHOOK_INVALID_TOKEN')];
		}

		if ($action !== 'webhook')
		{
			return ['success' => false, 'message' => Text::_('PLG_WTCDEK_AJAX_UNKNOWN_ACTION')];
		}

		$payload = $this->extractWebhookPayload();
		$subject = new Registry($payload);
		$dispatcher = $this->getDispatcher();

		PluginHelper::importPlugin('system', null, true, $dispatcher);
		PluginHelper::importPlugin('wtcdek', null, true, $dispatcher);

		$webhookEvent = WebhookEvent::create(
			'onWtcdekIncomingWebhook',
			[
				'eventClass' => WebhookEvent::class,
				'subject'    => $subject,
			]
		);
		$dispatcher->dispatch($webhookEvent->getName(), $webhookEvent);

		return ['success' => true, 'message' => Text::_('PLG_WTCDEK_AJAX_WEBHOOK_ACCEPTED')];
	}

	/**
	 * Извлекает payload входящего webhook.
	 *
	 * @return  array<string, mixed>
	 *
	 * @since   1.3.1
	 */
	private function extractWebhookPayload(): array
	{
		$remove = ['option', 'plugin', 'group', 'format', 'action', 'action_type', 'token'];
		$queryData = array_diff_key($this->getApplication()->getInput()->getArray(), array_flip($remove));
		$rawBody = trim((string) file_get_contents('php://input'));
		$bodyData = [];

		if (!empty($rawBody))
		{
			$decoded = json_decode($rawBody, true);

			if (is_array($decoded))
			{
				$bodyData = $decoded;
			}
		}

		if (!empty($bodyData))
		{
			return $bodyData;
		}

		return is_array($queryData) ? $queryData : [];
	}

	/**
	 * Нормализует список вебхуков для UI.
	 *
	 * @param   array<string, mixed>  $rawList  Ответ API.
	 *
	 * @return  array<int, array<string, string>>
	 *
	 * @since   1.3.1
	 */
	private function normalizeWebhookList(array $rawList): array
	{
		if (isset($rawList['error_code']))
		{
			return [];
		}

		$result = [];

		foreach ($rawList as $item)
		{
			if (is_array($item))
			{
				$result[] = [
					'uuid' => (string) ($item['uuid'] ?? ''),
					'type' => (string) ($item['type'] ?? ''),
					'url'  => (string) ($item['url'] ?? ''),
				];
			}
		}

		return $result;
	}
}
