# Entity: intakes

Class: `Webtolk\Cdekapi\Entities\IntakesEntity`

## Methods

### changeStatus()

```php
public function changeStatus(array $request_options = []): array
```

PATCH /v2/intakes

Изменение статуса заявки на вызов курьера

**Описание:**
Метод позволяет изменить статус по действующей заявке на вызов курьера на "Требует обработки" с
передачей дополнительных статусов, если по заявке требуются дополнительные операции, такие как прозвон
или предоставление документов.
Перейти в статус "Требует обработки" могут заявки, которые имеют текущий статус Требует обработки/Готова
к назначению/Назначен курьер.

Источник: https://apidoc.cdek.ru/#tag/intake/operation/changeStatus

@param   array  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->intakes()->changeStatus([
    'uuid'   => '550e8400-e29b-41d4-a716-446655440000',
    'status' => 'CANCELLED',
]);
```

### create()

```php
public function create(array $request_options = []): array
```

POST /v2/intakes

Регистрация заявки на вызов курьера

**Описание:**
Метод позволяет осуществить вызов курьера для забора груза со склада интернет-магазина с последующей
доставкой до склада СДЭК. Рекомендуемый минимальный диапазон времени для приезда курьера не менее 3-х
часов. В теле запроса передаются данные об адресе отправителя, контактном лице, дате и интервале времени
забора, количестве и характеристиках мест отправления. В ответе возвращается уникальный идентификатор
заявки и текущий статус запроса*.

*Метод работает асинхронно. Статус "ACCEPTED" в ответе на запрос не гарантирует, что заявка создана в ИС
СДЭК. Этот статус относится к запросу (запрос успешно принят) и говорит о том, что запрос прошел
первичные валидации и структурно составлен корректно. Далее запрос проходит остальные валидации,
результат можно получить с помощью метода получения информации о заявке. Статус запроса "SUCCESSFUL" -
сущность успешно создана в системе, статус "INVALID" - при создании возникла ошибка, необходимо её
исправить и повторно отправить запрос на регистрацию заявки на вызов курьера.

Источник: https://apidoc.cdek.ru/#tag/intake/operation/create

@param   array  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->intakes()->create([
    'cdek_number' => '1234567890',
    'date'        => '2026-03-20',
]);
```

### getAvailableDays()

```php
public function getAvailableDays(array $request_options = []): array
```

POST /v2/intakes/availableDays

Получение дат вызова курьера для НП

**Описание:**
Метод позволяет получить доступные даты для забора груза курьером со склада интернет-магазина для
населенного пункта, в котором находится склад.

Источник: https://apidoc.cdek.ru/#tag/intake/operation/getAvailableDays

@param   array  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->intakes()->getAvailableDays([
    'date'             => '2026-03-20',
    'sender_city_code' => 44,
]);
```

### deleteByUuid()

```php
public function deleteByUuid(string $uuid): array
```

DELETE /v2/intakes/{uuid}

Удаление заявки

**Описание:**
Метод предназначен для удаления заявки на вызов курьера.

Заявку через интеграцию можно удалить в любом статусе, отличном от финального.

Источник: https://apidoc.cdek.ru/#tag/intake/operation/deleteByUuid

@param   string  $uuid  UUID заявки на вызов курьера.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->intakes()->deleteByUuid('550e8400-e29b-41d4-a716-446655440000');
```

### getByUuid()

```php
public function getByUuid(string $uuid): array
```

GET /v2/intakes/{uuid}

Получение информации о заявке по UUID

**Описание:**
Метод предназначен для получения информации по UUID заявки.

Источник: https://apidoc.cdek.ru/#tag/intake/operation/getByUuid

@param   string  $uuid  UUID заявки на вызов курьера.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->intakes()->getByUuid('550e8400-e29b-41d4-a716-446655440000');
```
