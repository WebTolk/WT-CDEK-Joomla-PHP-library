# DeliverypointsField: выбор ПВЗ и постаматов

С версии **1.4.0-dev** поле `Deliverypoints` выбирает один пункт СДЭК и сохраняет его **код**, например `SAR141`. По умолчанию доступны ПВЗ, постаматы и пункты только выдачи. Библиотека не привязана к компоненту магазина.

Для стандартного AJAX-поиска включите `System - WT CDEK` и настройте аккаунт библиотеки. Поле подключает WebAsset самостоятельно; писать JS для наблюдения за адресом не нужно.

## Адрес из полей формы

```xml
<form addfieldprefix="Webtolk\Cdekapi\Fields">
  <fieldset name="delivery">
    <field name="destination_city" type="text" label="Город" />
    <field name="destination_zip" type="text" label="Индекс" />
    <field name="delivery_point" type="Deliverypoints" label="Пункт СДЭК"
           watchfield='{"city":"destination_city","zip":"destination_zip"}' />
  </fieldset>
</form>
```

Имена в `watchfield` относятся к текущей группе Joomla Form. Для другой группы используйте `address.city`. Префикс формы может отличаться от `jform`. Поле реагирует на `input` с задержкой 250 мс, на `change` — без этой задержки. Несколько полей могут использовать одни источники; повторяемый subform поддерживается автоматически.

## XML-атрибуты

| Атрибут | Значение по умолчанию | Назначение |
| --- | --- | --- |
| `city` | Не задан | Название города в XML |
| `zip` | Не задан | Почтовый индекс в XML |
| `fias_guid` | Не задан | UUID населённого пункта ФИАС |
| `watchfield` | Не задан | JSON-карта с ключами `city`, `zip`, `fias_guid`; достаточно одного |
| `point_type` | `ALL` | `ALL`, `PVZ` или `POSTAMAT` |
| `is_reception` | Без ограничения | Приём отправлений: `true`, `false`, `1`, `0`; пустой атрибут снимает ограничение |
| `is_handout` | Без ограничения | Выдача отправлений: те же значения |
| `watchfield-data-source` | `ajax` | `ajax`, `scriptoptions` или alias `onpage` |
| `watchfield-data-url` | URL `com_ajax` текущего клиента Joomla | Адрес собственного AJAX-провайдера |
| `watchfield-data-options` | Не задан | Точный ключ Joomla Script Options для данных страницы |
| `watchfield-data-path` | `data.0.options` для стандартного AJAX | Точечный путь до массива вариантов; при смене режима по умолчанию пустой |

**Наличие** любого адресного атрибута `city`, `zip`, `fias_guid`, включая `city=""`, полностью отключает `watchfield`. Значения XML и наблюдаемые значения не смешиваются. Фильтры типов и услуг сами по себе не отключают наблюдение.

```xml
<field name="fixed_point" type="Deliverypoints"
       addfieldprefix="Webtolk\Cdekapi\Fields"
       city="Саратов" point_type="ALL" label="Пункт СДЭК" />

<field name="locker" type="Deliverypoints"
       addfieldprefix="Webtolk\Cdekapi\Fields"
       fias_guid="bf465fda-7834-47d5-986b-ccdb584a85a6"
       point_type="POSTAMAT" is_handout="true" label="Постамат" />

<field name="pickup_only" type="Deliverypoints"
       addfieldprefix="Webtolk\Cdekapi\Fields"
       watchfield='{"city":"destination_city","zip":"destination_zip"}'
       point_type="PVZ" is_reception="false" is_handout="true"
       label="Пункт только выдачи" />
```

Кнопки «Обновить список ПВЗ» нет: при XML-адресе список загружается при инициализации, при наблюдении — также при изменении источников.

## UUID и API СДЭК

`fias_guid` — UUID **ФИАС**, а не `city_uuid` СДЭК. Например, для Саратова API возвращает `fias_guid=bf465fda-7834-47d5-986b-ccdb584a85a6` и другой `city_uuid`.

Непустой FIAS UUID направляется в `GET /v2/deliverypoints` без предварительного поиска города. Без UUID библиотека ищет город по названию и/или индексу. Несколько различных кодов города дают пустой список, чтобы не выбирать населённый пункт произвольно.

В [официальном OpenAPI СДЭК](https://gateway.cdek.ru/api-cdek-docs/web/docs/merged?sectionId=api_v2_integration), проверенном 12.10.2026, `fias_guid` населённого пункта не помечен устаревшим. `deprecated` относится к отдельному `fias_region_guid`. Параметра `city_uuid` у запроса `/v2/deliverypoints` нет. [Отчёт проверки](../../docs/reports/2026-10-12-cdek-fias-guid-deprecation-check.md).

## Собственные данные

Провайдер передаёт массив `[{"value":"SAR141","text":"Адрес пункта"}]`. Стандартный `com_ajax` помещает его в `data[0].options`. Поле обращается к единому обработчику `plugin=wtcdek` с `action=deliverypoints`; он работает на сайте и в админке. Для собственного endpoint задайте его URL и путь до массива в JSON-ответе. В POST ключ `zip` преобразуется в `postal_code`; передаются также фильтры `type`, `is_reception`, `is_handout` и CSRF-токен Joomla.

Можно использовать данные страницы:

```php
use Joomla\CMS\Factory;

Factory::getApplication()->getDocument()->addScriptOptions('shop.delivery', [
    'points' => [['value' => 'SAR141', 'text' => 'Пункт на проспекте']]],
);
```

```xml
<field name="delivery_point" type="Deliverypoints"
       addfieldprefix="Webtolk\Cdekapi\Fields"
       city="Саратов" watchfield-data-source="scriptoptions"
       watchfield-data-options="shop.delivery" watchfield-data-path="points" />
```

Общий контракт источников и событий описан в [Watchfield](./watchfield.md).

## Выбор и ошибки

При смене адреса прежний выбор сбрасывается. Отсутствующий в первоначальном ответе сохранённый код остаётся выбранным с предупреждением. Пустой список, отсутствующий адрес и ошибка загрузки имеют отдельные сообщения. Устаревшие AJAX-ответы не заменяют новые; при удалении поля запрос отменяется. Подписи пунктов выводятся текстом.

Проверено на установленной **Joomla 6.1.3 / joomla.local** в Chrome: по городу, XML-адресу и FIAS UUID получены 28 ПВЗ Саратова. Для фильтров постаматов и только выдачи провайдер вернул пустые списки; работа фильтров с подходящими пунктами подтверждена контрольными данными. [Приёмка 1.4.0-dev](../../docs/reports/2026-10-12-watchfield-140-dev-verification.md).
