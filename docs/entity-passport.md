# Entity: passport

Class: `Webtolk\Cdekapi\Entities\PassportEntity`

## Methods

### get()

```php
public function get(array $request_options = []): array
```

GET /passport

Получение информации о паспортных данных

**Описание:**
Метод используется для получения информации о паспортных данных (сообщает о готовности передавать заказы
на таможню) по международным заказу/заказам.

Источник: https://apidoc.cdek.ru/#tag/passport/operation/get_5

@param   array{
            cdek_number?: string|int,
            order_uuid?: string,
            client?: 'SENDER'|'RECEIVER'|'ALL'
        }  $request_options  Параметры запроса.
                             - cdek_number: номер заказа СДЭК.
                             - order_uuid: UUID заказа.
                             - client: сторона, для которой запрашиваются паспортные данные.
                               Допустимые значения: SENDER, RECEIVER, ALL.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->passport()->get([
    'cdek_number' => '1234567890',
    'client'      => 'ALL',
]);
```
