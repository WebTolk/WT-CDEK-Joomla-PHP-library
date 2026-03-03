<?php
/**
 * Поле URL входящего webhook CDEK для конфигурации системного плагина.
 *
 * @package    WT Cdek library package
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version    1.3.0
 * @license    GNU General Public License version 3 or later. Only for *.php files!
 * @link       https://web-tolk.ru
 */

declare(strict_types=1);

namespace Joomla\Plugin\System\Wtcdek\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Webtolk\Cdekapi\Cdek;
use function htmlspecialchars;

/**
 * Отображает URL для приема webhook CDEK.
 *
 * @since  1.3.1
 */
class WebhookurlField extends FormField
{
	/**
	 * Тип Joomla Form Field.
	 *
	 * @var    string
	 * @since  1.3.1
	 */
	protected $type = 'Webhookurl';

	/**
	 * Возвращает HTML поля.
	 *
	 * @return  string
	 *
	 * @since   1.3.1
	 */
	protected function getInput(): string
	{
		$data = $this->form->getData();
		$webhookToken = (string) $data->get('params.webhook_token', '');

		if (empty($webhookToken))
		{
			return '<div class="alert alert-info">' . Text::_('PLG_WTCDEK_FIELD_WEBHOOK_URL_EMPTY_TOKEN') . '</div>';
		}

		$url = (new Cdek())->webhooks()->getJoomlaWebhookUrl();

		return '<input type="text" class="form-control" readonly value="'
			. htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
	}

	/**
	 * Возвращает title поля.
	 *
	 * @return  string
	 *
	 * @since   1.3.1
	 */
	protected function getTitle(): string
	{
		return $this->getLabel();
	}

	/**
	 * Возвращает label поля.
	 *
	 * @return  string
	 *
	 * @since   1.3.1
	 */
	protected function getLabel(): string
	{
		return Text::_((string) ($this->element['label'] ?: $this->element['name']));
	}
}
