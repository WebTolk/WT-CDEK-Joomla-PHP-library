# Entity: deliverypoints

Class: `Webtolk\Cdekapi\Entities\DeliverypointsEntity`

## Methods

### getDeliveryPoints()

```php
public function getDeliveryPoints(array $request_options = []): array
```

GET /v2/deliverypoints

Получение списка офисов

**Описание:**
Метод предназначен для получения списка действующих офисов СДЭК.

Мы не рекомендуем использовать статичный список офисов, так как офисы могут быть неактуальны.
Рекомендуется обновлять список офисов не реже одного раза в сутки.

Источник: https://apidoc.cdek.ru/#tag/delivery_point/operation/search

@param   array{
            postal_code?: int|string,
            city_code?: int|string,
            type?: 'PVZ'|'POSTAMAT'|'ALL',
            country_code?: string,
            region_code?: int|string,
            have_cashless?: bool|int|string,
            have_cash?: bool|int|string,
            allowed_cod?: bool|int|string,
            is_dressing_room?: bool|int|string,
            weight_max?: int|float|string,
            weight_min?: int|float|string,
            lang?: string,
            take_only?: bool|int|string,
            is_handout?: bool|int|string,
            is_reception?: bool|int|string,
            fias_guid?: string
        }  $request_options  Параметры фильтрации офисов.

@return  array

Пример: 

```php
<?php

$result = $cdek->deliverypoints()->getDeliveryPoints([
    'city_code' => 44,
    'type'      => 'PVZ',
]);
```
