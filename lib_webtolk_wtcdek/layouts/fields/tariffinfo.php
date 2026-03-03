<?php
/**
 * Layout поля тарифа: шаблоны для JS-рендера информации о выбранном тарифе.
 *
 * @package    WT Cdek library package
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version    1.3.0
 * @license    GNU General Public License version 3 or later. Only for *.php files!
 * @link       https://web-tolk.ru
 */

defined('_JEXEC') or die;

extract($displayData);
?>
<div
	id="<?php echo htmlspecialchars((string) ($container_id ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
	class="wt-cdek-tariffinfo-field"
	data-watch-field="<?php echo htmlspecialchars((string) ($watchfield ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
	data-tariffs="<?php echo htmlspecialchars((string) json_encode($tariffs ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
>
	<div data-role="render-target"></div>

	<template data-template="state-empty">
		<div class="alert alert-info">
			<span data-field="message"></span>
		</div>
	</template>

	<template data-template="state-not-found">
		<div class="alert alert-warning">
			Информация по тарифу с кодом <strong data-field="tariff_code"></strong> не найдена.
		</div>
	</template>

	<template data-template="state-error">
		<div class="alert alert-danger">
			<span data-field="message"></span>
		</div>
	</template>

	<template data-template="tariff-card">
		<div class="alert alert-secondary">
			<div><strong data-field="tariff_name"></strong></div>
			<div>Код тарифа: <strong data-field="tariff_code"></strong></div>
			<div>Режим доставки: <span data-field="delivery_mode_name"></span> [<span data-field="delivery_mode"></span>]</div>
			<div>Вес: <span data-field="weight"></span></div>
			<div>Длина: <span data-field="length"></span></div>
			<div>Ширина: <span data-field="width"></span></div>
			<div>Высота: <span data-field="height"></span></div>
			<div>Плательщик: <span data-field="payer_contragent_type"></span></div>
			<div>Отправитель: <span data-field="sender_contragent_type"></span></div>
			<div>Получатель: <span data-field="recipient_contragent_type"></span></div>
		</div>
	</template>
</div>
