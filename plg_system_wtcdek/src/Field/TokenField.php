<?php
/**
 * Поле генерации токена для входящих webhook CDEK.
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
use Joomla\CMS\User\UserHelper;
use function implode;

/**
 * Поле токена webhook с автогенерацией при пустом значении.
 *
 * @since  1.3.1
 */
class TokenField extends FormField
{
	/**
	 * Тип Joomla Form Field.
	 *
	 * @var    string
	 * @since  1.3.1
	 */
	protected $type = 'Token';

	/**
	 * Возвращает HTML поля.
	 *
	 * @return  string
	 *
	 * @since   1.3.1
	 */
	protected function getInput(): string
	{
		$newValue = '';

		if (empty($this->value))
		{
			$newValue = UserHelper::genRandomPassword(64);
		}

		$fieldInput = [];
		$fieldInput[] = '<div class="input-group">';
		$fieldInput[] = '<input type="text" class="form-control" name="' . $this->__get('name') . '" id="' . $this->__get('id')
			. '" value="' . (!empty($this->value) ? $this->value : $newValue) . '">';

		if (empty($this->value))
		{
			$fieldInput[] = '<div class="invalid-feedback d-block">';
			$fieldInput[] = Text::_('PLG_WTCDEK_FIELD_WEBHOOK_TOKEN_TOKEN_IS_EMPTY');
			$fieldInput[] = '</div>';
			$this->value = $newValue;
		}
		else
		{
			$fieldInput[] = '<div class="valid-feedback d-block">';
			$fieldInput[] = Text::_('PLG_WTCDEK_FIELD_WEBHOOK_TOKEN_TOKEN_IS_CREATED');
			$fieldInput[] = '</div>';
		}

		$fieldInput[] = '</div>';

		return implode('', $fieldInput);
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
		return Text::_((string) ($this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name']));
	}
}
