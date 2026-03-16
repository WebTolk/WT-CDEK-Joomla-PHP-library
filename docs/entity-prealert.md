# Entity: prealert

Class: `Webtolk\Cdekapi\Entities\PrealertEntity`

## Methods

### register()

```php
public function register(array $request_options = []): array
```

POST /prealert

Регистрация преалерта

**Описание:**
Метод предназначен для регистрации преалерта (реестра заказов, которые клиент собирается передать на
склад СДЭК для дальнейшей доставки) - информирования со стороны интернет-магазина (далее ИМ) о желании
передать некоторое количество заказов в СДЭК, чтобы к этому моменту принимающий офис подготовил
необходимые документы.
Преалерт нужен, только если ИМ собирается передавать большое количество заказов одновременно. Для работы
с преалертом необходимо, чтобы услуга "Преалерт" была подключена по договору. По этому вопросу
необходимо обратиться напрямую к закрепленному менеджеру или в закрепленный офис СДЭК (с кем
подписывался договор).

Преалерт не связан с заявкой на вызов курьера.

Источник: https://apidoc.cdek.ru/#tag/prealert/operation/register

@param   array  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->prealert()->register([
    'number' => 'PREALERT-10001',
]);
```

### getByUuid()

```php
public function getByUuid(string $uuid): array
```

GET /prealert/{uuid}

Получение информации о преалерте

**Описание:**
Метод предназначен для получения информации по заданному преалерту

Источник: https://apidoc.cdek.ru/#tag/prealert/operation/get_1

@param   string  $uuid  UUID преалерта.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->prealert()->getByUuid('550e8400-e29b-41d4-a716-446655440000');
```
