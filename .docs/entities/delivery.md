# DeliveryEntity

`Webtolk\Cdekapi\Entities\DeliveryEntity` отвечает за договорённости о доставке: получение доступных интервалов, создание новой договоренности и получение ее статуса. Это полезно в сценариях, где интернет-магазин хочет дать покупателю выбор даты и окна доставки.

Получение Entity:

```php
$delivery = $cdek->delivery();
```

## `create(array $request_options = []): array`

**Сигнатура**

```php
public function create(array $request_options = []): array
```

**REST API**

`POST /v2/delivery`

**Что делает**

Регистрирует договоренность о доставке по уже существующему заказу.

**Параметры**

- `date` — обязательная дата доставки;
- одно из полей `cdek_number` или `order_uuid` — обязательно;
- `time_from`, `time_to` — желаемый интервал;
- `comment` — комментарий;
- `delivery_point` — код ПВЗ, если доставка переводится в офис;
- `to_location` — новый адрес доставки, если нужно изменить адрес.

**Что возвращает**

Асинхронный ответ API. Чаще всего сначала приходит статус запроса вроде `ACCEPTED`, а окончательный результат нужно проверять отдельным запросом `getByUuid()`.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->delivery()->create([
    'date'        => '2026-04-20',
    'order_uuid'  => '11111111-1111-1111-1111-111111111111',
    'time_from'   => '10:00',
    'time_to'     => '14:00',
    'comment'     => 'Позвонить за час до приезда',
]);
```

## `getEstimatedIntervals(array $request_options = []): array`

**Сигнатура**

```php
public function getEstimatedIntervals(array $request_options = []): array
```

**REST API**

`POST /v2/delivery/estimatedIntervals`

**Что делает**

Позволяет заранее получить доступные интервалы доставки еще до создания заказа.

**Параметры**

- `date_time` — обязательная дата и время;
- `tariff_code` — обязательный тариф;
- `to_location` — обязательная точка доставки;
- `to_location.address` — обязательный адрес;
- `from_location.address` — обязателен, если передан блок `from_location`;
- `shipment_point` — код офиса отправления, если он нужен.

**Что возвращает**

Массив доступных интервалов с информацией о доступных слотах.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->delivery()->getEstimatedIntervals([
    'date_time'    => '2026-04-20T10:00:00+03:00',
    'tariff_code'  => 137,
    'to_location'  => [
        'address' => 'Новосибирск, Красный проспект, 1',
    ],
]);
```

## `getIntervals(array $request_options = []): array`

**Сигнатура**

```php
public function getIntervals(array $request_options = []): array
```

**REST API**

`GET /v2/delivery/intervals`

**Что делает**

Получает интервалы доставки для уже существующего заказа.

**Параметры**

Нужно передать одно из полей:

- `cdek_number`;
- `order_uuid`.

**Что возвращает**

Массив доступных дат и интервалов.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->delivery()->getIntervals([
    'cdek_number' => '1234567890',
]);
```

## `getByUuid(string $uuid): array`

**Сигнатура**

```php
public function getByUuid(string $uuid): array
```

**REST API**

`GET /v2/delivery/{uuid}`

**Что делает**

Возвращает информацию по конкретной договоренности о доставке.

**Параметры**

- `$uuid` — UUID договоренности.

**Что возвращает**

Массив с данными о договоренности и ее статусе.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->delivery()->getByUuid('11111111-1111-1111-1111-111111111111');
```
