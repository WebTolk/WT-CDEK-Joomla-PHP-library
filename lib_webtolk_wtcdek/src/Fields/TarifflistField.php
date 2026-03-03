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

declare(strict_types=1);

namespace Webtolk\Cdekapi\Fields;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\GroupedlistField;
use Webtolk\Cdekapi\Cdek;
use function is_array;
use function sprintf;

class TarifflistField extends GroupedlistField
{

	protected $type = 'Tarifflist';

	/**
	 * Возвращает сгруппированный список тарифов:
	 * группа = `tariff_name`, опция = элемент `delivery_modes`.
	 *
	 * @return  array<string, array<int, object>>
	 */
	protected function getGroups()
	{
		$groups = [];
		$cdek = new Cdek();
		$tariffs = $cdek->calculator()->getAllTariffs();

		if (!is_array($tariffs) || empty($tariffs))
		{
			return $groups;
		}

		foreach ($tariffs as $tariff)
		{
			if (!is_array($tariff))
			{
				continue;
			}

			$groupName = (string) ($tariff['tariff_name'] ?? '');
			$modes = $tariff['delivery_modes'] ?? [];

			if ($groupName === '' || !is_array($modes) || empty($modes))
			{
				continue;
			}

			foreach ($modes as $mode)
			{
				if (!is_array($mode) || !isset($mode['tariff_code']))
				{
					continue;
				}

				$tariffCode = (string) $mode['tariff_code'];
				$modeName = (string) ($mode['delivery_mode_name'] ?? '');
				$modeCode = (string) ($mode['delivery_mode'] ?? '');
				$optionText = $modeName !== ''
					? sprintf('%s [%s] (code: %s)', $modeName, $modeCode, $tariffCode)
					: sprintf('mode %s (code: %s)', $modeCode, $tariffCode);

				$groups[$groupName][] = HTMLHelper::_('select.option', $tariffCode, $optionText);
			}
		}

		return $groups;
	}
}
