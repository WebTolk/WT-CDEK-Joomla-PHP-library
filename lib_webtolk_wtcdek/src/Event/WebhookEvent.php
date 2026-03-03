<?php
/**
 * Событие входящего вебхука CDEK.
 *
 * @package    WT Cdek library package
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version    1.3.0
 * @license    GNU General Public License version 3 or later. Only for *.php files!
 * @link       https://web-tolk.ru
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Event;

defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\Registry\Registry;
use function is_array;

/**
 * Кастомное событие для обработки входящих вебхуков CDEK.
 *
 * @since  1.3.1
 */
class WebhookEvent extends AbstractEvent
{
	/**
	 * Возвращает все данные входящего webhook.
	 *
	 * @return  array<string, mixed>
	 *
	 * @since   1.3.1
	 */
	public function getData(): array
	{
		$subject = $this->arguments['subject'] ?? null;

		if ($subject instanceof Registry)
		{
			return $subject->toArray();
		}

		return is_array($subject) ? $subject : [];
	}

	/**
	 * Возвращает тип вебхука, если он передан в payload.
	 *
	 * @return  string
	 *
	 * @since   1.3.1
	 */
	public function getWebhookType(): string
	{
		$data = $this->getData();

		return (string) ($data['type'] ?? '');
	}

	/**
	 * Возвращает UUID заказа, если доступен в payload.
	 *
	 * @return  string
	 *
	 * @since   1.3.1
	 */
	public function getOrderUuid(): string
	{
		$data = $this->getData();

		return (string) ($data['order_uuid'] ?? '');
	}
}
