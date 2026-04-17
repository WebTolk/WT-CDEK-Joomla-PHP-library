# CheckEntity

`Webtolk\Cdekapi\Entities\CheckEntity` используется для получения информации о чеках. Обычно эта Entity нужна тогда, когда интеграция должна показать, был ли сформирован чек по заказу, либо выгрузить чеки за конкретную дату.

Получение Entity:

```php
$check = $cdek->check();
```

## `get(array $request_options = []): array`

**Сигнатура**

```php
public function get(array $request_options = []): array
```

**REST API**

`GET /v2/check`

**Что делает**

Получает информацию о чеке по одному из идентификаторов заказа или по дате.

**Параметры**

Нужно передать хотя бы одно из полей:

- `order_uuid` — UUID заказа;
- `cdek_number` — номер заказа СДЭК;
- `date` — дата, за которую нужно получить чеки.

Если передан `order_uuid`, библиотека дополнительно проверяет корректность UUID.

**Что возвращает**

Массив данных о чеке или ошибку библиотеки/REST API.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->check()->get([
    'cdek_number' => '1234567890',
]);
```
