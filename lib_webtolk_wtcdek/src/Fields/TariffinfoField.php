<?php
/**
 * Поле Joomla Form для отображения информации о выбранном тарифе СДЭК.
 *
 * @package    WT Cdek library package
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version    1.3.0
 * @license    GNU General Public License version 3 or later. Only for *.php files!
 * @link       https://web-tolk.ru
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Fields;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Webtolk\Cdekapi\Cdek;
use function array_map;
use function implode;
use function is_array;

/**
 * Поле формы Joomla для отображения информации о выбранном тарифе СДЭК.
 *
 * @since  1.3.1
 */
class TariffinfoField extends FormField
{
	/**
	 * Тип поля Joomla.
	 *
	 * @var    string
	 * @since  1.3.1
	 */
	protected $type = 'Tariffinfo';

	/**
	 * Идентификатор layout для рендера поля.
	 *
	 * @var    string
	 * @since  1.3.1
	 */
	protected $layout = 'libraries.webtolk.wtcdek.fields.tariffinfo';

	/**
	 * Возвращает HTML-разметку поля.
	 *
	 * @return  string
	 *
	 * @since   1.3.1
	 */
	protected function getInput(): string
	{
		$this->addInlineScript();

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
	 * @since   1.3.1
	 */
	protected function getLayoutData(): array
	{
		$layoutData = parent::getLayoutData();
		$layoutData['watchfield'] = (string) ($this->element['watchfield'] ?? $this->element['tariff_field'] ?? '');
		$layoutData['container_id'] = $this->id . '_tariffinfo';
		$layoutData['tariffs'] = $this->getTariffsByCode();

		return $layoutData;
	}

	/**
	 * Собирает индекс тарифов по `tariff_code` из метода `getAllTariffs()`.
	 *
	 * @return  array<string, array<string, string>>
	 *
	 * @since   1.3.1
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
					'payer_contragent_type' => $this->formatArray($tariff['payer_contragent_type'] ?? []),
					'sender_contragent_type' => $this->formatArray($tariff['sender_contragent_type'] ?? []),
					'recipient_contragent_type' => $this->formatArray($tariff['recipient_contragent_type'] ?? []),
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
	 * @since   1.3.1
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
	 * @since   1.3.1
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
	 * Подключает inline-скрипт, обновляющий блок по изменению связанного поля.
	 *
	 * @return  void
	 *
	 * @since   1.3.1
	 */
	private function addInlineScript(): void
	{
		$document = Factory::getApplication()->getDocument();
		$script = <<<'JS'
(() => {
	if (window.WtCdekTariffInfoTemplateInit) {
		return;
	}

	window.WtCdekTariffInfoTemplateInit = true;

	const findSourceField = (scope, watchField) => {
		const form = scope.closest('form') || document;
		const byId = form.querySelector(`#jform_${watchField}`);
		if (byId) {
			return byId;
		}

		const byName = form.querySelector(`[name="${watchField}"]`);
		if (byName) {
			return byName;
		}

		return form.querySelector(`[name$="[${watchField}]"]`);
	};

	const parseTariffs = (json) => {
		try {
			const parsed = JSON.parse(json || '{}');
			return (typeof parsed === 'object' && parsed !== null) ? parsed : {};
		} catch (e) {
			return {};
		}
	};

	const renderTemplate = (container, templateName, literals = {}) => {
		const target = container.querySelector('[data-role="render-target"]');
		const template = container.querySelector(`template[data-template="${templateName}"]`);

		if (!target || !template) {
			return;
		}

		const fragment = template.content.cloneNode(true);
		fragment.querySelectorAll('[data-field]').forEach((node) => {
			const key = node.dataset.field;
			node.textContent = Object.prototype.hasOwnProperty.call(literals, key)
				? String(literals[key] ?? '')
				: '';
		});

		target.replaceChildren(fragment);
	};

	const render = (container, source) => {
		const tariffs = parseTariffs(container.dataset.tariffs);
		const selectedCode = String(source?.value || '').trim();

		if (!selectedCode) {
			renderTemplate(container, 'state-empty', { message: 'Выберите тариф в связанном поле.' });
			return;
		}

		const info = tariffs[selectedCode] || null;

		if (!info) {
			renderTemplate(container, 'state-not-found', { tariff_code: selectedCode });
			return;
		}

		renderTemplate(container, 'tariff-card', info);
	};

	const initContainer = (container) => {
		if (container.dataset.initialized === '1') {
			return;
		}

		const watchField = container.dataset.watchField || '';

		if (!watchField) {
			renderTemplate(container, 'state-error', { message: 'Не задан параметр watchfield у поля Tariffinfo.' });
			container.dataset.initialized = '1';
			return;
		}

		const source = findSourceField(container, watchField);

		if (!source) {
			renderTemplate(container, 'state-error', { message: `Связанное поле "${watchField}" не найдено.` });
			container.dataset.initialized = '1';
			return;
		}

		source.addEventListener('change', () => render(container, source));
		render(container, source);
		container.dataset.initialized = '1';
	};

	const boot = () => document.querySelectorAll('.wt-cdek-tariffinfo-field').forEach(initContainer);

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot, { once: true });
	} else {
		boot();
	}
})();
JS;
		$document->addScriptDeclaration($script);
	}
}
