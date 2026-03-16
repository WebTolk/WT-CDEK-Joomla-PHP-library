# Entity: payment

Class: `Webtolk\Cdekapi\Entities\PaymentEntity`

## Methods

### get()

```php
public function get(array $request_options = []): array
```

GET /payment

Получение информации о переводе наложенного платежа

**Описание:**
Метод предназначен для получения информации о заказах, по которым был переведен наложенный платеж
интернет-магазину в заданную дату

Источник: https://apidoc.cdek.ru/#tag/payment/operation/get_5

@param   array{
            date?: string
        }  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->payment()->get([
    'date' => '2026-03-01',
]);
```
