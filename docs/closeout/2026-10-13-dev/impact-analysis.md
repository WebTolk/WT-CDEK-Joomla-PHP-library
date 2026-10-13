# Impact Analysis

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Trigger

Нейтральные библиотечные поля и исправление cookie требуют устойчивого AJAX-контракта в site/admin.

## Affected Components

DeliverypointsField, Helper/Watchfield, WatchfieldAssetItem, DeliverypointsService, system Wtcdek, assets/renderers, languages/manifests.

## Domain Surface

Адрес/ФИАС, фильтры пунктов, список value/text и выбранный код; тарифный справочник/карточка; параметры расчёта.

## Runtime Surface

POST от Watchfield -> com_ajax plugin=wtcdek action=deliverypoints -> service -> подготовленные options; виджет использует offices/calculate и raw JSON.

## Data Or State Risks

Cookie исключены из provider input. GET/POST сохраняют прежнюю фильтрацию и вложенные arrays. JS хранит transient состояние target; persisted field value управляется Joomla form.

## User-Facing Risks

Ручная установка финального 1.4.0 и реальный ответ СДЭК ещё не проверены. Четыре P2 F1–F4 и P3 F5 остаются открытыми; checkpoint dev не является публичным релизом.

## Assurance Focus

Все action в обоих приложениях; generic/modern events; CSRF/unknown action без client creation; XML watchers, FIAS/filters; source/archive metadata identity.
