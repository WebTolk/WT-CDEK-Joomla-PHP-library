# DeliverypointsEntity

`Webtolk\Cdekapi\Entities\DeliverypointsEntity` используется для поиска ПВЗ и постаматов СДЭК. Обычно именно эту Entity вызывают при построении карты пунктов выдачи, списка офисов в checkout и серверного поиска ближайших точек доставки.

Получение Entity:

```php
$deliverypoints = $cdek->deliverypoints();
```

## `getDeliveryPoints(array $request_options = []): array`

**Сигнатура**

```php
public function getDeliveryPoints(array $request_options = []): array
```

**REST API**

`GET /v2/deliverypoints`

**Что делает**

Возвращает список действующих офисов СДЭК по фильтрам. Результат кэшируется. Если передать пустой массив, библиотека подставит дефолтные значения вроде `type = ALL` и `lang = rus`.

**Параметры**

Чаще всего используются:

- `city_code` — код города СДЭК;
- `type` — `PVZ`, `POSTAMAT` или `ALL`;
- `postal_code` — почтовый индекс;
- `country_code`;
- `region_code`;
- `have_cashless`;
- `have_cash`;
- `allowed_cod`;
- `is_dressing_room`;
- `weight_min`, `weight_max`;
- `take_only`;
- `is_handout`;
- `is_reception`;
- `fias_guid`;
- `lang` — по умолчанию `rus`.

**Что возвращает**

Массив офисов. Каждый элемент обычно содержит код офиса, адрес, график работы, доступные услуги и ограничения.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$points = $cdek->deliverypoints()->getDeliveryPoints([
    'city_code' => 270,
    'type'      => 'PVZ',
]);
```
