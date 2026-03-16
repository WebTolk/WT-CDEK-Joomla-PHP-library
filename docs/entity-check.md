# Entity: check

Class: `Webtolk\Cdekapi\Entities\CheckEntity`

## Methods

### get()

```php
public function get(array $request_options = []): array
```

GET /check

Получение информации о чеках

**Описание:**
Метод используется для получения информации о чеке по заказу или за выбранный день.

Источник: https://apidoc.cdek.ru/#tag/receipt/operation/get_7

@param   array{
            order_uuid?: string,
            cdek_number?: string|int,
            date?: string
        }  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->check()->get([
    'cdek_number' => '1234567890',
]);
```
