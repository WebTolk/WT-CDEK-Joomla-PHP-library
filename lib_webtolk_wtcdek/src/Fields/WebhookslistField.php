<?php
/**
 * Поле Joomla Form для отображения и управления подписками на вебхуки CDEK.
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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Webtolk\Cdekapi\Cdek;
use function htmlspecialchars;
use function json_encode;

/**
 * Поле списка вебхуков CDEK для страницы настроек плагина.
 *
 * @since  1.3.1
 */
class WebhookslistField extends FormField
{
	/**
	 * Тип поля Joomla.
	 *
	 * @var    string
	 * @since  1.3.1
	 */
	protected $type = 'Webhookslist';

	/**
	 * Идентификатор layout для рендера поля.
	 *
	 * @var    string
	 * @since  1.3.1
	 */
	protected $layout = 'libraries.webtolk.wtcdek.fields.webhookslist';

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

		return $this->getRenderer($this->layout)->render($this->collectLayoutData());
	}

	/**
	 * Готовит данные для layout.
	 *
	 * @return  array<string, mixed>
	 *
	 * @since   1.3.1
	 */
	protected function getLayoutData(): array
	{
		$layoutData = parent::getLayoutData();
		$cdek = new Cdek();
		$webhooksEntity = $cdek->webhooks();

		$layoutData['container_id'] = $this->id . '_webhookslist';
		$layoutData['webhooks'] = $webhooksEntity->getAll();
		$layoutData['webhook_url'] = $webhooksEntity->getJoomlaWebhookUrl();
		$layoutData['allowed_types'] = $webhooksEntity->getAllowedTypes();
		$layoutData['ajax_url'] = (new Uri(Uri::base()))->toString()
			. 'index.php?option=com_ajax&plugin=wtcdek&group=system&format=raw&'
			. Session::getFormToken() . '=1';
		$layoutData['i18n_json'] = htmlspecialchars((string) json_encode([
			'refresh_error'      => Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_MSG_REFRESH_ERROR'),
			'refresh_success'    => Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_MSG_REFRESH_SUCCESS'),
			'subscribe_done'     => Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_MSG_SUBSCRIBE_DONE'),
			'unsubscribe_done'   => Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_MSG_UNSUBSCRIBE_DONE'),
			'unknown_error'      => Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_MSG_UNKNOWN_ERROR'),
			'no_hooks'           => Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_NO_HOOKS'),
			'uuid_label'         => Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_UUID_LABEL'),
			'type_label'         => Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_TYPE_VALUE_LABEL'),
			'url_label'          => Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_URL_LABEL'),
			'unsubscribe_button' => Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_BTN_UNSUBSCRIBE'),
		], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

		return $layoutData;
	}

	/**
	 * Подключает inline-скрипт для кнопок управления webhook-подписками.
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
	if (window.WtCdekWebhooksListInit) {
		return;
	}

	window.WtCdekWebhooksListInit = true;

	const getI18n = (container) => {
		try {
			return JSON.parse(container.dataset.i18n || '{}');
		} catch (e) {
			return {};
		}
	};

	const postForm = async (url, payload) => {
		const formData = new URLSearchParams(payload);
		const response = await fetch(url, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: formData.toString(),
			credentials: 'same-origin',
		});

		const text = await response.text();

		try {
			return JSON.parse(text);
		} catch (e) {
			return { success: false, message: text || 'Invalid JSON response' };
		}
	};

	const renderState = (container, message, level = 'info') => {
		const stateNode = container.querySelector('[data-role="state"]');
		if (!stateNode) {
			return;
		}

		stateNode.className = `alert alert-${level}`;
		stateNode.textContent = String(message || '');
	};

	const refreshList = async (container) => {
		const ajaxUrl = container.dataset.ajaxUrl;
		const payload = {
			action_type: 'internal',
			action: 'webhook_list',
		};

		const result = await postForm(ajaxUrl, payload);
		const i18n = getI18n(container);

		if (!result.success) {
			renderState(container, result.message || i18n.refresh_error || 'Error', 'danger');
			return;
		}

		const listNode = container.querySelector('[data-role="list"]');

		if (listNode) {
			const hooks = Array.isArray(result.data) ? result.data : [];
			if (hooks.length === 0) {
				listNode.innerHTML = `<div class="alert alert-info">${i18n.no_hooks || 'No hooks'}</div>`;
			} else {
				listNode.innerHTML = hooks.map((hook) => {
					const uuid = String(hook.uuid || '');
					const url = String(hook.url || '');
					const type = String(hook.type || '');
					return `
						<div class="border rounded p-2 mb-2">
							<div><strong>${i18n.uuid_label || 'UUID'}:</strong> ${uuid}</div>
							<div><strong>${i18n.type_label || 'TYPE'}:</strong> ${type}</div>
							<div class="text-break"><strong>${i18n.url_label || 'URL'}:</strong> ${url}</div>
							<button type="button" class="btn btn-sm btn-outline-danger mt-2" data-action="unsubscribe" data-uuid="${uuid}">
								${i18n.unsubscribe_button || 'Unsubscribe'}
							</button>
						</div>
					`;
				}).join('');
			}
		}

		renderState(container, result.message || i18n.refresh_success || 'Done', 'success');
	};

	const init = (container) => {
		if (container.dataset.initialized === '1') {
			return;
		}

		container.addEventListener('click', async (event) => {
			const target = event.target.closest('[data-action]');

			if (!target) {
				return;
			}

			event.preventDefault();
			const ajaxUrl = container.dataset.ajaxUrl;
			const action = target.dataset.action;

			if (action === 'refresh') {
				await refreshList(container);
				return;
			}

			if (action === 'subscribe') {
				const typeNode = container.querySelector('[data-role="type"]');
				const urlNode = container.querySelector('[data-role="url"]');
				const payload = {
					action_type: 'internal',
					action: 'webhook_subscribe',
					type: String(typeNode?.value || ''),
					url: String(urlNode?.value || ''),
				};

				const result = await postForm(ajaxUrl, payload);
				const i18n = getI18n(container);
				renderState(container, result.message || i18n.subscribe_done || i18n.unknown_error || 'Done', result.success ? 'success' : 'danger');

				if (result.success) {
					await refreshList(container);
				}
			}

			if (action === 'unsubscribe') {
				const uuid = String(target.dataset.uuid || '');
				const payload = {
					action_type: 'internal',
					action: 'webhook_unsubscribe',
					uuid,
				};

				const result = await postForm(ajaxUrl, payload);
				const i18n = getI18n(container);
				renderState(container, result.message || i18n.unsubscribe_done || i18n.unknown_error || 'Done', result.success ? 'success' : 'danger');

				if (result.success) {
					await refreshList(container);
				}
			}
		});

		container.dataset.initialized = '1';
	};

	document.querySelectorAll('.wt-cdek-webhookslist-field').forEach(init);
})();
JS;
		$document->addScriptDeclaration($script);
	}
}
