# TariffinfoField: условия выбранного тарифа

`Webtolk\Cdekapi\Fields\TariffinfoField` отображает справочную карточку выбранного тарифа: название, код, режим доставки, диапазоны веса и размеров, типы плательщика, отправителя и получателя. Поле не сохраняет отдельное значение и не выполняет расчёт стоимости.

```xml
<form addfieldprefix="Webtolk\Cdekapi\Fields">
  <fieldset name="cdek">
    <field name="tariff_code" type="Tarifflist" label="Тариф" default="136" />
    <field name="tariff_info" type="Tariffinfo" label="Условия тарифа"
           watchfield="tariff_code" />
  </fieldset>
</form>
```

## Связанное поле

`watchfield="tariff_code"` — имя источника в текущей группе Joomla Form. Поддерживается прежний alias `tariff_field="tariff_code"`; если заданы оба атрибута, используется `watchfield`.

Именованная карта:

```xml
<field name="tariff_info" type="Tariffinfo"
       watchfield='{"tariff_code":"tariff_code"}' />
```

Для выбора кода обработчик использует ключ `tariff_code`, затем `value` скалярного `watchfield`, затем первое значение карты. Для источника другой группы задайте имя `delivery.tariff_code`. Никаких предположений о префиксе `jform` нет. Несколько карточек, отдельные формы и строки repeatable subform работают независимо.

## Источник данных

По умолчанию каталог библиотеки экспортируется в **Joomla Script Options** с точным ключом `wtcdek.tariffs`. Исполняемого inline-скрипта нет. Поле вызывает `useScript('wtcdek.tariffinfo')`, общий обработчик загружен как зависимость, переводы для JS прикрепляет `WatchfieldAssetItem`.

| Атрибут | По умолчанию | Назначение |
| --- | --- | --- |
| `watchfield-data-source` | `scriptoptions` | `scriptoptions`, `onpage` или `ajax` |
| `watchfield-data-options` | `wtcdek.tariffs` | Точный ключ `Joomla.getOptions()` |
| `watchfield-data-path` | Пустой | Точечный путь внутри данных |
| `watchfield-data-url` | Не задан | Обязателен при AJAX |

Данные — объект, индексированный кодом тарифа. Значения содержат `tariff_code`, `tariff_name`, `delivery_mode`, `delivery_mode_name`, `weight`, `length`, `width`, `height`, `payer_contragent_type`, `sender_contragent_type`, `recipient_contragent_type`. Отсутствующие свойства отображаются пустыми.

```php
use Joomla\CMS\Factory;

Factory::getApplication()->getDocument()->addScriptOptions('shop.cdek', [
    'catalog' => ['136' => [
        'tariff_code' => '136',
        'tariff_name' => 'Посылка',
        'delivery_mode_name' => 'склад-склад',
    ]],
]);
```

```xml
<field name="tariff_info" type="Tariffinfo" watchfield="tariff_code"
       watchfield-data-source="scriptoptions"
       watchfield-data-options="shop.cdek" watchfield-data-path="catalog" />

<field name="tariff_info_ajax" type="Tariffinfo" watchfield="tariff_code"
       watchfield-data-source="ajax"
       watchfield-data-url="index.php?option=com_ajax&amp;plugin=yourplugin&amp;group=system&amp;format=json"
       watchfield-data-path="data.0.catalog" />
```

Скалярный источник отправляется в AJAX как POST-параметр `value`; карта с ключом `tariff_code` отправляет `tariff_code`. Собственный endpoint реализует получение каталога и возвращает JSON. [Контракт общего обработчика](./watchfield.md).

## Layout и состояния

Layout остаётся `libraries.webtolk.cdekapi.fields.tariffinfo`; сохраняются `render-target`, шаблоны `state-empty`, `state-not-found`, `state-error`, `tariff-card` и элементы `data-field`. В переопределении layout выводите `$watchfield_attributes` на контейнере `.wt-cdek-tariffinfo-field`. Каталог доступен через `$tariffs` для PHP-переопределений, а стандартный JS читает Script Options.

При пустом тарифе выводится предложение выбрать тариф, при неизвестном коде — сообщение об отсутствии информации, при неверном источнике или пути — ошибка. Значения каталога вставляются как текст.

Существующие XML `Tariffinfo` с `watchfield` и `tariff_field` проверены на установленной Joomla 6.1.3. В **1.4.0-dev** можно оставить их без изменений. [Отчёт E2E](../../docs/reports/2026-10-12-watchfield-140-dev-verification.md).
