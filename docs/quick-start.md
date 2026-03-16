# Quick Start

Укажите 

```php
<?php
use Webtolk\Cdekapi\Cdek;

\defined('_JEXEC') or die;

// Вариант 1: брать credentials из настроек плагина
$cdek = new Cdek();

// Вариант 2: передать credentials явно
$cdek = new Cdek(test_mode: true, client_id: 'your_client_id', client_secret: 'your_client_secret');
```

Авторизация выполняется автоматически. Токен доступа кэшируется средствами Joomla.

## Общий шаблон вызова

```php
<?php

$result = $cdek->entity('entity_name')->method($request_data);
// или
$result = $cdek->entity_name()->method($request_data);
```

## Примеры

### Поиск города

```php
<?php

$result = $cdek->location()->getCities([
    'postal_code' => '410012',
    'city'        => 'Саратов',
    'size'        => 1,
]);
```
Результат запроса:
```text
Array
(
    [0] => Array
        (
            [code] => 428
            [city_uuid] => 7e54a0b3-76f0-41e2-92e0-f1e600ad84fd
            [city] => Саратов
            [fias_guid] => bf465fda-7834-47d5-986b-ccdb584a85a6
            [country_code] => RU
            [country] => Россия
            [region] => Саратовская область
            [region_code] => 47
            [fias_region_guid] => df594e0e-a935-4664-9d26-0bae13f904fe
            [sub_region] => городской округ Саратов
            [longitude] => 46.034266
            [latitude] => 51.533562
            [time_zone] => Europe/Saratov
            [payment_limit] => -1
        )

)
```

```php
<?php

$result = $cdek->location()->suggestCities('Саратов', 'RU');
```
Результат запроса:
```text
Array
(
    [0] => Array
        (
            [city_uuid] => 7e54a0b3-76f0-41e2-92e0-f1e600ad84fd
            [code] => 428
            [full_name] => Саратов, городской округ Саратов, Саратовская область, Россия
            [country_code] => RU
        )

    [1] => Array
        (
            [city_uuid] => 869dc183-090d-459e-b4fe-43b7ba98f969
            [code] => 31730
            [full_name] => Саратовская, городской округ Горячий Ключ, Краснодарский край, Россия
            [country_code] => RU
        )

    [2] => Array
        (
            [city_uuid] => a85bc703-265f-49df-9fe3-948823442e1b
            [code] => 1859734
            [full_name] => Саратовка, Табунский район, Алтайский край, Россия
            [country_code] => RU
        )

    [3] => Array
        (
            [city_uuid] => 22e364f4-bd36-4d74-ab07-646c21360c8c
            [code] => 1933700
            [full_name] => Саратовка, Воловский район, Тульская область, Россия
            [country_code] => RU
        )

    [4] => Array
        (
            [city_uuid] => 4a2797bc-0edf-406e-aec5-830fafe3bcb6
            [code] => 1859735
            [full_name] => Саратовский, Кочубеевский муниципальный округ, Ставропольский край, Россия
            [country_code] => RU
        )

)
```

### Пример: расчёт тарифа по коду тарифа

```php
<?php

$result = $cdek->calculator()->calculateTariff([
    'tariff_code'   => 136,
    'from_location' => ['code' => 44], // Москва
    'to_location'   => ['code' => 270], // Новосибирск
    'packages'      => [
        [
            'weight' => 1000,
            'length' => 10,
            'width'  => 10,
            'height' => 10,
        ],
    ],
]);
```
Результат запроса:
```text
Array
(
    [delivery_sum] => 457.14
    [period_min] => 4
    [period_max] => 5
    [calendar_min] => 4
    [calendar_max] => 5
    [weight_calc] => 1000
    [services] => Array
        (
            [0] => Array
                (
                    [code] => INSURANCE
                    [sum] => 0
                    [total_sum] => 0
                    [discount_percent] => 0
                    [discount_sum] => 0
                    [vat_rate] => 5
                    [vat_sum] => 0
                )

        )

    [total_sum] => 480
    [currency] => RUB
    [delivery_date_range] => Array
        (
            [min] => 2026-03-20
            [max] => 2026-03-21
        )

)
```

### Пример: создание заказа

```php
<?php

$orderData = [
    'type' => 1,
    'number' => 'ORDER-10001',
    'tariff_code' => 136,
    'recipient' => [
        'name' => 'Иван Иванов',
        'phones' => [
            ['number' => '+79990000000'],
        ],
    ],
    'from_location' => ['code' => 44],
    'to_location' => ['code' => 270],
    'packages' => [
        [
            'number' => '1',
            'weight' => 1000,
            'items' => [
                [
                    'name' => 'Товар',
                    'ware_key' => 'sku-1',
                    'payment' => ['value' => 1000],
                    'cost' => 1000,
                    'weight' => 1000,
                    'amount' => 1,
                ],
            ],
        ],
    ],
];

$result = $cdek->orders()->createOrder($orderData);
```