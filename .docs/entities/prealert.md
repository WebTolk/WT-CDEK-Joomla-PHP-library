# PrealertEntity

`Webtolk\Cdekapi\Entities\PrealertEntity` нужна для регистрации преалертов. Проще говоря, это уведомление СДЭК о том, что вы собираетесь передать на склад сразу пачку отправлений и хотите, чтобы склад заранее подготовился.

Получение Entity:

```php
$prealert = $cdek->prealert();
```

## `register(array $request_options = []): array`

**Сигнатура**

```php
public function register(array $request_options = []): array
```

**REST API**

`POST /v2/prealert`

**Что делает**

Регистрирует преалерт.

**Параметры**

Библиотека проверяет только непустой массив. Дальше валидация выполняется на стороне API СДЭК.

**Что возвращает**

Асинхронный ответ API с UUID преалерта и статусом обработки.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->prealert()->register([
    'date'   => '2026-04-20',
    'number' => 'PREALERT-1001',
    'orders' => [
        ['number' => 'ORDER-10001'],
        ['number' => 'ORDER-10002'],
    ],
]);
```

## `getByUuid(string $uuid): array`

**Сигнатура**

```php
public function getByUuid(string $uuid): array
```

**REST API**

`GET /v2/prealert/{uuid}`

**Что делает**

Получает информацию по одному преалерту.

**Параметры**

- `$uuid` — UUID преалерта.

**Что возвращает**

Массив с данными преалерта и его текущим статусом.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->prealert()->getByUuid('11111111-1111-1111-1111-111111111111');
```
