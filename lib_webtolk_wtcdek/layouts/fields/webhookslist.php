<?php
/**
 * Layout поля списка вебхуков CDEK.
 *
 * @package    WT Cdek library package
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version    1.3.0
 * @license    GNU General Public License version 3 or later. Only for *.php files!
 * @link       https://web-tolk.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

$hooks = is_array($webhooks ?? null) ? $webhooks : [];
?>
<div
	id="<?php echo htmlspecialchars((string) ($container_id ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
	class="wt-cdek-webhookslist-field"
	data-ajax-url="<?php echo htmlspecialchars((string) ($ajax_url ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
	data-i18n="<?php echo (string) ($i18n_json ?? '{}'); ?>"
>
	<div class="mb-3">
		<label class="form-label"><?php echo Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_WEBHOOK_URL_LABEL'); ?></label>
		<input type="text" class="form-control" data-role="url"
			value="<?php echo htmlspecialchars((string) ($webhook_url ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
			readonly>
	</div>

	<div class="mb-3">
		<label class="form-label"><?php echo Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_TYPE_LABEL'); ?></label>
		<select class="form-select" data-role="type">
			<?php foreach (($allowed_types ?? []) as $type) : ?>
				<option value="<?php echo htmlspecialchars((string) $type, ENT_QUOTES, 'UTF-8'); ?>">
					<?php echo htmlspecialchars((string) $type, ENT_QUOTES, 'UTF-8'); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="d-flex gap-2 mb-3">
		<button type="button" class="btn btn-success" data-action="subscribe"><?php echo Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_BTN_SUBSCRIBE'); ?></button>
		<button type="button" class="btn btn-outline-secondary" data-action="refresh"><?php echo Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_BTN_REFRESH'); ?></button>
	</div>

	<div data-role="state" class="alert alert-info"><?php echo Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_READY'); ?></div>

	<div data-role="list">
		<?php if (empty($hooks)) : ?>
			<div class="alert alert-info"><?php echo Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_NO_HOOKS'); ?></div>
		<?php else : ?>
			<?php foreach ($hooks as $hook) : ?>
				<div class="border rounded p-2 mb-2">
					<div><strong><?php echo Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_UUID_LABEL'); ?>:</strong> <?php echo htmlspecialchars((string) ($hook['uuid'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
					<div><strong><?php echo Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_TYPE_VALUE_LABEL'); ?>:</strong> <?php echo htmlspecialchars((string) ($hook['type'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
					<div class="text-break"><strong><?php echo Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_URL_LABEL'); ?>:</strong> <?php echo htmlspecialchars((string) ($hook['url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
					<?php if (!empty($hook['uuid'])) : ?>
						<button type="button"
								class="btn btn-sm btn-outline-danger mt-2"
								data-action="unsubscribe"
								data-uuid="<?php echo htmlspecialchars((string) $hook['uuid'], ENT_QUOTES, 'UTF-8'); ?>">
							<?php echo Text::_('LIB_WTCDEK_FIELD_WEBHOOKSLIST_BTN_UNSUBSCRIBE'); ?>
						</button>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
