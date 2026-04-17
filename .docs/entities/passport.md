# PassportEntity

`Webtolk\Cdekapi\Entities\PassportEntity` относится к международным отправлениям. Через нее запрашивают информацию о паспортных данных, которые нужны для таможенного оформления.

Получение Entity:

```php
$passport = $cdek->passport();
```

## `get(array $request_options = []): array`

**Сигнатура**

```php
public function get(array $request_options = []): array
```

**REST API**

`GET /v2/passport`

**Что делает**

Получает информацию о паспортных данных по международному заказу.

**Параметры**

Нужно передать одно из полей:

- `cdek_number`;
- `order_uuid`.

Дополнительно можно передать:

- `client` — `SENDER`, `RECEIVER` или `ALL`.

**Что возвращает**

Массив с данными о статусе паспортной информации.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->passport()->get([
    'order_uuid' => '11111111-1111-1111-1111-111111111111',
    'client'     => 'RECEIVER',
]);
```
