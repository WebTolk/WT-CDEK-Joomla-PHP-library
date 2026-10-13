<?php
/**
 * Joomla Form field displaying CDEK tariff information.
 * @package       WT Cdek library package
 * @subpackage    Webtolk/Cdekapi
 * @author        Sergey Tolkachyov, WebTolk
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @version       __DEPLOY_VERSION__
 * @license       GNU General Public License version 3 or later.
 * @link          https://web-tolk.ru
 * @since         1.0.0
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Fields;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Webtolk\Cdekapi\Cdek;
use Webtolk\Cdekapi\Helper\Watchfield;
use function array_map;
use function implode;
use function is_array;

/**
 * Поле формы Joomla для отображения информации о выбранном тарифе СДЭК.
 *
 * @since 1.3.0
 */
class TariffinfoField extends FormField
{
	/**
	 * Тип поля Joomla.
	 *
	 * @var    string
	 * @since 1.3.0
	 */
	protected $type = 'Tariffinfo';

	/**
	 * Идентификатор layout для рендера поля.
	 *
	 * @var    string
	 * @since 1.3.0
	 */
	protected $layout = 'libraries.webtolk.cdekapi.fields.tariffinfo';

	/**
	 * Возвращает HTML-разметку поля.
	 *
	 * @return  string
	 *
	 * @since 1.3.0
	 */
	protected function getInput(): string
	{
		$app = Factory::getApplication();
		$app->getLanguage()->load('lib_webtolk_cdekapi', JPATH_SITE);
		$wa = $app->getDocument()->getWebAssetManager();
		$wa->getRegistry()->addRegistryFile('media/lib_wtcdek/joomla.assets.json');
		$wa->useScript('wtcdek.tariffinfo');

		if (empty($this->layout))
		{
			throw new \UnexpectedValueException(\sprintf('%s has no layout assigned.', $this->name));
		}

		return $this->getRenderer($this->layout)->render($this->collectLayoutData());
	}

	/**
	 * Метод подготовки данных для layout.
	 *
	 * @return  array
	 *
	 * @since 1.3.0
	 */
	protected function getLayoutData(): array
	{
		$layoutData = parent::getLayoutData();
		$layoutData['watchfield'] = (string) ($this->element['watchfield'] ?? $this->element['tariff_field'] ?? '');
		$layoutData['container_id'] = $this->id . '_tariffinfo';
		$layoutData['tariffs'] = $this->getTariffsByCode();
		Factory::getApplication()->getDocument()->addScriptOptions('wtcdek.tariffs', $layoutData['tariffs']);
		try {
			$sources = Watchfield::resolve($this->form, $layoutData['watchfield'], (string) $this->group);
			if ($sources === []) {
				throw new \UnexpectedValueException('Tariffinfo requires a tariff source.');
			}
			$options = Watchfield::dataOptions($this->element, ['source' => 'scriptoptions', 'options' => 'wtcdek.tariffs', 'path' => '']);
			$layoutData['watchfield_attributes'] = Watchfield::attributes($sources, $options);
		} catch (\JsonException | \UnexpectedValueException $exception) {
			$layoutData['watchfield_attributes'] = Watchfield::attributes([], ['source' => 'scriptoptions', 'options' => 'wtcdek.tariffs', 'error' => true]);
		}

		return $layoutData;
	}

	/**
	 * Собирает индекс тарифов по `tariff_code` из метода `getAllTariffs()`.
	 *
	 * @return  array<string, array<string, string>>
	 *
	 * @since 1.3.0
	 */
	private function getTariffsByCode(): array
	{
		$cdek = new Cdek();
		$tariffs = $cdek->calculator()->getAllTariffs();
		$result = [];

		if (!is_array($tariffs))
		{
			return $result;
		}

		foreach ($tariffs as $tariff)
		{
			if (!is_array($tariff) || !is_array($tariff['delivery_modes'] ?? null))
			{
				continue;
			}

			foreach ($tariff['delivery_modes'] as $mode)
			{
				if (!is_array($mode) || !isset($mode['tariff_code']))
				{
					continue;
				}

				$tariffCode = (string) $mode['tariff_code'];
				$result[$tariffCode] = [
					'tariff_code' => $tariffCode,
					'tariff_name' => (string) ($tariff['tariff_name'] ?? ''),
					'delivery_mode' => (string) ($mode['delivery_mode'] ?? ''),
					'delivery_mode_name' => (string) ($mode['delivery_mode_name'] ?? ''),
					'weight' => $this->formatRange($tariff['weight_min'] ?? '', $tariff['weight_max'] ?? ''),
					'length' => $this->formatRange($tariff['length_min'] ?? '', $tariff['length_max'] ?? ''),
					'width' => $this->formatRange($tariff['width_min'] ?? '', $tariff['width_max'] ?? ''),
					'height' => $this->formatRange($tariff['height_min'] ?? '', $tariff['height_max'] ?? ''),
					'payer_contragent_type' => $this->formatContragentTypes($tariff['payer_contragent_type'] ?? []),
					'sender_contragent_type' => $this->formatContragentTypes($tariff['sender_contragent_type'] ?? []),
					'recipient_contragent_type' => $this->formatContragentTypes($tariff['recipient_contragent_type'] ?? []),
				];
			}
		}

		return $result;
	}

	/**
	 * Преобразует границы диапазона в строку.
	 *
	 * @param   mixed  $min  Нижняя граница диапазона.
	 * @param   mixed  $max  Верхняя граница диапазона.
	 *
	 * @return  string
	 *
	 * @since 1.3.0
	 */
	private function formatRange($min, $max): string
	{
		return (string) $min . ' - ' . (string) $max;
	}

	/**
	 * Преобразует массив в строку.
	 *
	 * @param   mixed  $value  Значение поля тарифа.
	 *
	 * @return  string
	 *
	 * @since 1.3.0
	 */
	private function formatArray($value): string
	{
		if (!is_array($value) || empty($value))
		{
			return '-';
		}

		return implode(', ', array_map(static fn($item) => (string) $item, $value));
	}

	/**
	 * Локализует список типов контрагентов тарифа.
	 *
	 * По документации CDEK для этих полей используются enum-значения
	 * `LEGAL_ENTITY` и `INDIVIDUAL`. Неизвестные значения возвращаются как есть.
	 *
	 * @param   mixed  $value  Значение поля тарифа.
	 *
	 * @return  string
	 *
	 * @since 1.3.0
	 */
	private function formatContragentTypes($value): string
	{
		if (!is_array($value) || empty($value))
		{
			return Text::_('LIB_WTCDEK_FIELD_TARIFFINFO_CONTRAGENT_TYPE_ALL');
		}

		return implode(', ', array_map(
			static function ($item): string {
				$item = (string) $item;
				$key = 'LIB_WTCDEK_FIELD_TARIFFINFO_CONTRAGENT_TYPE_' . strtoupper($item);

				return Factory::getApplication()->getLanguage()->hasKey($key) ? Text::_($key) : $item;
			},
			$value
		));
	}

}
