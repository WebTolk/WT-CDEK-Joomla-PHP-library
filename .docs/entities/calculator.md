# CalculatorEntity

`Webtolk\Cdekapi\Entities\CalculatorEntity` отвечает за расчет стоимости и сроков доставки. Это одна из самых востребованных Entity в интернет-магазине: именно через неё обычно считают доступные тарифы до создания заказа.

Получение Entity:

```php
$calculator = $cdek->calculator();
```

## `getAllTariffs()`

**Сигнатура**

```php
public function getAllTariffs(): array
```

**REST API**

`GET /v2/calculator/alltariffs`

**Что делает**

Возвращает коды тарифов, доступных по вашему договору со СДЭК. Результат кэшируется средствами Joomla.

**Параметры**

Метод не принимает параметров.

**Что возвращает**

Массив из поля `tariff_codes` ответа API. Если API вернул ошибку, библиотека вернет пустой массив.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();
$tariffs = $cdek->calculator()->getAllTariffs();
```

## `calculateTariff(array $request_options = []): array`

**Сигнатура**

```php
public function calculateTariff(array $request_options = []): array
```

**REST API**

`POST /v2/calculator/tariff`

**Что делает**

Считает стоимость и сроки доставки по одному конкретному тарифу.

**Параметры**

- `tariff_code` — обязательный код тарифа;
- `from_location` — обязательная точка отправления;
- `to_location` — обязательная точка доставки;
- `packages` — обязательный массив мест;
- `packages[].weight` — обязательный вес каждого места;
- `services` — дополнительные услуги, если они нужны.

**Что возвращает**

Массив ответа API. Обычно в нем есть `delivery_sum`, `period_min`, `period_max`, `total_sum`, `services`, `currency`.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->calculator()->calculateTariff([
    'tariff_code'   => 136,
    'from_location' => ['code' => 44],
    'to_location'   => ['code' => 270],
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

## `calculateTariffList(array $request_options = []): array`

**Сигнатура**

```php
public function calculateTariffList(array $request_options = []): array
```

**REST API**

`POST /v2/calculator/tarifflist`

**Что делает**

Считает сразу список доступных тарифов по направлению и параметрам отправления.

**Параметры**

- `from_location` — обязательная точка отправления;
- `to_location` — обязательная точка доставки;
- `packages` — обязательный массив мест;
- `packages[].weight` — обязательный вес каждого места.

`tariff_code` здесь не нужен, потому что метод сам возвращает список тарифов.

**Что возвращает**

Массив тарифов, которые подходят под запрос.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->calculator()->calculateTariffList([
    'from_location' => ['code' => 44],
    'to_location'   => ['code' => 270],
    'packages'      => [
        [
            'weight' => 1500,
            'length' => 20,
            'width'  => 15,
            'height' => 10,
        ],
    ],
]);
```

## `tariffAndService(array $request_options = []): array`

**Сигнатура**

```php
public function tariffAndService(array $request_options = []): array
```

**REST API**

`POST /v2/calculator/tariffAndService`

**Что делает**

Считает тарифы и одновременно учитывает дополнительные услуги: страховку, упаковку, фотодокументы и так далее.

**Параметры**

- `from_location` — обязательная точка отправления;
- `to_location` — обязательная точка доставки;
- `packages` — обязательный массив мест;
- `packages[].weight` — обязательный вес каждого места;
- `services` — массив услуг вида `['code' => 'INSURANCE', 'parameter' => 1000]`.

**Что возвращает**

Массив с тарифами и дополнительными услугами, которые участвуют в расчете.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->calculator()->tariffAndService([
    'from_location' => ['code' => 44],
    'to_location'   => ['code' => 270],
    'packages'      => [
        [
            'weight' => 1000,
            'length' => 20,
            'width'  => 20,
            'height' => 20,
        ],
    ],
    'services'      => [
        [
            'code'      => 'INSURANCE',
            'parameter' => 5000,
        ],
    ],
]);
```
