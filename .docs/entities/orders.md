# OrdersEntity

`Webtolk\Cdekapi\Entities\OrdersEntity` — центральная Entity библиотеки. Именно через нее создают, читают, изменяют и удаляют заказы, оформляют клиентские возвраты и отказы, а также получают связанные заявки на вызов курьера.

Получение Entity:

```php
$orders = $cdek->orders();
```

## `createOrder(array $request_options): array`

**Сигнатура**

```php
public function createOrder(array $request_options): array
```

**REST API**

`POST /v2/orders`

**Что делает**

Создает заказ в СДЭК.

**Параметры**

Библиотека требует:

- `tariff_code`;
- `recipient`;
- `packages`;
- `recipient.name`;
- `packages[].number`;
- `packages[].weight`.

Если указан `recipient.phones`, у каждого телефона должен быть `number`.

**Что возвращает**

Асинхронный ответ API с UUID запроса и статусом создания заказа.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->orders()->createOrder([
    'type'          => 1,
    'number'        => 'ORDER-10001',
    'tariff_code'   => 136,
    'recipient'     => [
        'name'   => 'Иван Иванов',
        'phones' => [
            ['number' => '+79990000000'],
        ],
    ],
    'from_location' => ['code' => 44],
    'to_location'   => ['code' => 270],
    'packages'      => [
        [
            'number' => '1',
            'weight' => 1000,
        ],
    ],
]);
```

## `getOrderInfo(?string $uuid = '', ?string $cdek_number = '', ?string $im_number = ''): array`

**Сигнатура**

```php
public function getOrderInfo(?string $uuid = '', ?string $cdek_number = '', ?string $im_number = ''): array
```

**REST API**

- `GET /v2/orders/{uuid}`, если передан UUID;
- `GET /v2/orders?cdek_number=...`, если передан номер СДЭК;
- `GET /v2/orders?im_number=...`, если передан номер заказа магазина.

**Что делает**

Возвращает информацию по ранее созданному заказу.

**Параметры**

Нужно передать хотя бы один идентификатор:

- `$uuid`;
- `$cdek_number`;
- `$im_number`.

**Что возвращает**

Массив с деталями заказа и его статусами.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$order = $cdek->orders()->getOrderInfo(cdek_number: '1234567890');
```

## `updateOrder(array $request_options = []): array`

**Сигнатура**

```php
public function updateOrder(array $request_options = []): array
```

**REST API**

`PATCH /v2/orders`

**Что делает**

Изменяет уже созданный заказ, пока он находится в изменяемом статусе.

**Параметры**

Библиотека требует:

- `type`;
- `recipient`;
- одно из полей `uuid` или `cdek_number`.

**Что возвращает**

Массив ответа API.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->orders()->updateOrder([
    'uuid'      => '11111111-1111-1111-1111-111111111111',
    'type'      => 1,
    'recipient' => [
        'name' => 'Петр Петров',
    ],
]);
```

## `getIntakes(string $order_uuid): array`

**Сигнатура**

```php
public function getIntakes(string $order_uuid): array
```

**REST API**

`GET /v2/orders/{orderUuid}/intakes`

**Что делает**

Возвращает все заявки на вызов курьера, связанные с заказом.

**Параметры**

- `$order_uuid` — UUID заказа.

**Что возвращает**

Массив заявок на вызов курьера по заказу.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->orders()->getIntakes('11111111-1111-1111-1111-111111111111');
```

## `deleteOrder(string $uuid): array`

**Сигнатура**

```php
public function deleteOrder(string $uuid): array
```

**REST API**

`DELETE /v2/orders/{uuid}`

**Что делает**

Удаляет заказ, если он находится в статусе, в котором удаление еще возможно.

**Параметры**

- `$uuid` — UUID заказа.

**Что возвращает**

Массив ответа API.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->orders()->deleteOrder('11111111-1111-1111-1111-111111111111');
```

## `clientReturn(string $uuid, array $request_options = []): array`

**Сигнатура**

```php
public function clientReturn(string $uuid, array $request_options = []): array
```

**REST API**

`POST /v2/orders/{uuid}/clientReturn`

**Что делает**

Регистрирует клиентский возврат по заказу интернет-магазина.

**Параметры**

- `$uuid` — UUID исходного заказа;
- `tariff_code` — обязательный тариф для возврата.

**Что возвращает**

Массив ответа API.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->orders()->clientReturn(
    '11111111-1111-1111-1111-111111111111',
    [
        'tariff_code' => 136,
    ]
);
```

## `refuse(string $uuid): array`

**Сигнатура**

```php
public function refuse(string $uuid): array
```

**REST API**

`POST /v2/orders/{uuid}/refusal`

**Что делает**

Регистрирует отказ по заказу с последующим возвратом.

**Параметры**

- `$uuid` — UUID заказа.

**Что возвращает**

Массив ответа API.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->orders()->refuse('11111111-1111-1111-1111-111111111111');
```
