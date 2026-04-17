[![Version](https://img.shields.io/github/release/WebTolk/WT-CDEK-Joomla-PHP-library.svg)]() [![PHP](https://img.shields.io/badge/PHP-7.4+-green.svg)]() [![JoomlaVersion](https://img.shields.io/badge/Joomla-4.2.7+-orange.svg)]() [![JoomlaVersion](https://img.shields.io/badge/Joomla-5.x-orange.svg)]() [![Documentation](https://img.shields.io/badge/Documentation-blue.svg)](https://web-tolk.ru/dev/biblioteki/wt-cdek-library-for-joomla-developers?utm_source=github)

# WT CDEK Joomla PHP library

Нативная библиотека для Joomla 4.2.7+ и Joomla 5, которая упрощает работу с REST API СДЭК. Пакет включает:

- библиотеку `Webtolk/Cdekapi`
- системный плагин `System - WT Cdek` для хранения настроек и AJAX-интеграций
- task-плагин `Task - Update WT Cdek data` для обновления справочников по расписанию
- web asset с официальным JavaScript-виджетом СДЭК

Начиная с `1.3.0`, библиотека использует entity-based API: основной класс `Cdek` выступает фасадом, а логика запросов распределена по сущностям `calculator`, `orders`, `location`, `webhooks` и другим.

![image](https://github.com/WebTolk/WT-CDEK-Joomla-PHP-library/assets/6236403/ff2d142d-c602-41fc-afa8-dc3490fc929f)

## Установка

Установите пакет `pkg_lib_wtcdek` как обычное расширение Joomla. Вместе с ним будут установлены:

- библиотека `lib_webtolk_wtcdek`
- плагин `plg_system_wtcdek`
- плагин `plg_task_updatewtcdekdata`

После установки:

1. включите плагин `System - WT Cdek`
2. заполните `Account` (`client_id`) и `Secure` (`client_secret`)
3. при использовании планировщика задач Joomla настройте запуск команды `scheduler:run` через CRON для создания локальной копии справочников CDEK по регионам, городам и пунктов выдачи

## Быстрый старт

```php
<?php

use Webtolk\Cdekapi\Cdek;

\defined('_JEXEC') or die;

// Вариант 1: брать credentials из настроек плагина
$cdek = new Cdek();

// Вариант 2: передать credentials явно
$cdek = new Cdek(test_mode: true, client_id: 'your_client_id', client_secret: 'your_client_secret');
```

Авторизация происходит автоматически. Токен кэшируется средствами Joomla.

### Пример: поиск города

```php
<?php

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

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

### Пример: подсказки по городам

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

## Доступные сущности

В `1.3.0` библиотека поддерживает следующие сущности:

- `calculator()`
- `check()`
- `delivery()`
- `deliverypoints()`
- `intakes()`
- `international()`
- `location()`
- `oauth()`
- `orders()`
- `passport()`
- `payment()`
- `photoDocument()`
- `prealert()`
- `print()`
- `reverse()`
- `webhooks()`

У каждой сущности свой набор методов. Подробные примеры вынесены в каталог `docs/`.

## Обратная совместимость с v.1.2.0

Класс `Cdek` сохранён как фасад и содержит часть старых публичных методов для совместимости. Но новое использование библиотеки должно строиться через сущности:

```php
<?php

$cdek->orders()->getOrderInfo($uuid);
$cdek->deliverypoints()->getDeliveryPoints(['city_code' => 44]);
$cdek->webhooks()->subscribe($url, $type);
```

Старые вызовы из ветки `1.2.0` переведены в deprecated-обёртки и будут удалены в следующих мажорных версиях.

## Что изменилось в 1.3.0

- монолитный класс `Cdek` переработан в фасад + transport layer `CdekRequest`
- логика API вынесена в отдельные entity-классы
- добавлены новые сущности: `webhooks`, `prealert`, `photoDocument`, `reverse`, `international`, `passport`, `payment`, `print`, `check`, `intakes`, `oauth`
- добавлен installer script для корректной установки и удаления layouts библиотеки
- обновлены языковые файлы и UI-поля для выбора тарифов
- `TarifflistField` переведён на сгруппированный список тарифов
- добавлен `TariffinfoField` и layout `layouts/fields/tariffinfo.php`
- обновлён JavaScript widget asset
- в `location()->suggestCities()` добавлен необязательный параметр `country_code`

## Виджет СДЭК

JavaScript-виджет оформлен как Joomla Web Asset. Для подключения:

```php
<?php

use Joomla\CMS\Factory;

\defined('_JEXEC') or die;

$doc = Factory::getApplication()->getDocument();
$wa  = $doc->getWebAssetManager();

$wa->useScript('cdek-widget-umd');
```

Доступные asset names:

- `cdek-widget-umd`
- `cdek-widget-es`
- `cdek-widget-ts`

Если виджету нужен `servicePath` для AJAX-запросов, используйте endpoint системного плагина через `com_ajax`.

```php
<?php

use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

$serviceUrl = new Uri(Uri::base());
$serviceUrl->setPath('/index.php');
$serviceUrl->setQuery([
    'option'                => 'com_ajax',
    'plugin'                => 'wtcdek',
    'group'                 => 'system',
    'format'                => 'raw',
    'city_code'             => $cityCode,
    Session::getFormToken() => 1,
]);

echo $serviceUrl->toString();

```

## Обновление локальных справочников

Плагин `Task - Update WT Cdek data` позволяет по расписанию загружать в вашу базу данных и обновлять:

- страны и регионы
- населённые пункты
- пункты выдачи

В дальнейшем вы можете использовать в своих расширениях локальные справочники, без ожидания результатов запросов со стороннего API.
Пока что не через методы библиотеки, а напрямую.

Для запуска планировщика Joomla через CLI:

```bash
php /path/to/site/public_html/cli/joomla.php scheduler:run
```

Посмотреть список задач:

```bash
php /path/to/site/public_html/cli/joomla.php scheduler:list
```

Запустить конкретную задачу:

```bash
php /path/to/site/public_html/cli/joomla.php scheduler:run --id=XXX
```

Для больших справочников лучше использовать серверный CRON, а не веб-запуск.

## Документация

- локальная документация по сущностям: `docs/`
- официальный API СДЭК: https://api-docs.cdek.ru/
- страница проекта: https://web-tolk.ru/
