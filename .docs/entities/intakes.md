# IntakesEntity

`Webtolk\Cdekapi\Entities\IntakesEntity` закрывает сценарии вызова курьера: создание заявки, получение доступных дат, просмотр заявки и изменение ее статуса. Это Entity для логистического back-office, а не для клиентского checkout.

Получение Entity:

```php
$intakes = $cdek->intakes();
```

## `changeStatus(array $request_options = []): array`

**Сигнатура**

```php
public function changeStatus(array $request_options = []): array
```

**REST API**

`PATCH /v2/intakes`

**Что делает**

Меняет статус уже существующей заявки на вызов курьера.

**Параметры**

Библиотека требует только непустой массив. Конкретная структура зависит от API СДЭК и вашего сценария изменения статуса.

**Что возвращает**

Массив ответа API.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->intakes()->changeStatus([
    'intake_uuid' => '11111111-1111-1111-1111-111111111111',
    'status'      => 'REQUIRE_PROCESSING',
]);
```

## `create(array $request_options = []): array`

**Сигнатура**

```php
public function create(array $request_options = []): array
```

**REST API**

`POST /v2/intakes`

**Что делает**

Создает заявку на вызов курьера.

**Параметры**

Библиотека проверяет только то, что массив не пустой. Практически обычно нужны данные адреса отправителя, контактного лица, даты забора, интервала времени и мест отправления.

**Что возвращает**

Асинхронный ответ API с UUID заявки и статусом обработки.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->intakes()->create([
    'intake_date' => '2026-04-20',
    'intake_time_from' => '10:00',
    'intake_time_to'   => '14:00',
    'name' => 'Склад интернет-магазина',
    'phones' => [
        ['number' => '+79990000000'],
    ],
    'packages' => [
        ['weight' => 2000],
    ],
]);
```

## `getAvailableDays(array $request_options = []): array`

**Сигнатура**

```php
public function getAvailableDays(array $request_options = []): array
```

**REST API**

`POST /v2/intakes/availableDays`

**Что делает**

Возвращает доступные даты вызова курьера для конкретного населенного пункта.

**Параметры**

Библиотека требует только непустой массив. Поля должны соответствовать схеме API СДЭК.

**Что возвращает**

Массив дат, в которые можно оформить забор.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->intakes()->getAvailableDays([
    'city' => 'Москва',
]);
```

## `deleteByUuid(string $uuid): array`

**Сигнатура**

```php
public function deleteByUuid(string $uuid): array
```

**REST API**

`DELETE /v2/intakes/{uuid}`

**Что делает**

Удаляет заявку на вызов курьера.

**Параметры**

- `$uuid` — UUID заявки.

**Что возвращает**

Массив ответа API.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->intakes()->deleteByUuid('11111111-1111-1111-1111-111111111111');
```

## `getByUuid(string $uuid): array`

**Сигнатура**

```php
public function getByUuid(string $uuid): array
```

**REST API**

`GET /v2/intakes/{uuid}`

**Что делает**

Возвращает информацию по одной заявке на вызов курьера.

**Параметры**

- `$uuid` — UUID заявки.

**Что возвращает**

Массив с подробностями заявки и ее текущим статусом.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->intakes()->getByUuid('11111111-1111-1111-1111-111111111111');
```
