# Entity: webhooks

Class: `Webtolk\Cdekapi\Entities\WebhooksEntity`

## Methods

### getAllowedTypes()

```php
public function getAllowedTypes(): array
```

Возвращает список поддерживаемых типов подписок.

@return  array<int, string>

Пример: 

```php
<?php

$result = $cdek->webhooks()->getAllowedTypes();
```

### getAll()

```php
public function getAll(): array
```

GET /v2/webhooks

Получение информации о подписках на вебхуки

**Описание:**
Метод предназначен для получения информации о всех активных подписках интернет-магазина на получение
вебхуков.

Источник: https://apidoc.cdek.ru/#tag/webhook/operation/getAll

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->webhooks()->getAll();
```

### create()

```php
public function create(string $url, string $type): array
```

POST /v2/webhooks

Добавление подписки на вебхуки

**Описание:**
Метод предназначен для подключения подписки на отправку на URL клиента событий, связанных с заказом.
Существуют следующие типы подписок:
- ORDER_STATUS - об изменении статуса заказа
- ORDER_MODIFIED - получение информации об изменении заказа
- PRINT_FORM - о готовности печатной формы
- RECEIPT - получение информации о чеке
- PREALERT_CLOSED - получение информации о закрытии преалерта
- ACCOMPANYING_WAYBILL - получение информации о транспорте для СНТ
- OFFICE_AVAILABILITY - получение информации об изменении доступности офиса
- DELIV_PROBLEM - получение информации о проблемах доставки по заказу
- DELIV_AGREEMENT - получение информации об изменении договоренности о доставке
- COURIER_INFO - получение информации о курьере
Если у клиента уже есть подписка с указанным типом, то будет создана еще одна подписка с таким же типом.

В ответе метода возвращается информация о запросе со статусом выполнения:
- ACCEPTED: запрос принят в обработку.
- SUCCESSFUL: подписка успешно создана.
- INVALID: запрос отклонен из-за ошибок в данных.

Источник: https://apidoc.cdek.ru/#tag/webhook/operation/create

@param   string  $url   URL, на который отправляется событие.
@param   string  $type  Тип вебхука.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->webhooks()->create(
    'https://example.com/cdek/webhook',
    'ORDER_STATUS'
);
```

### deleteByUuid()

```php
public function deleteByUuid(string $uuid): array
```

DELETE /v2/webhooks/{uuid}

Удаление подписки по UUID

**Описание:**
Метод предназначен для удаления подписки на получение вебхуков

Источник: https://apidoc.cdek.ru/#tag/webhook/operation/deleteById

@param   string  $uuid  UUID подписки на вебхуки.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->webhooks()->deleteByUuid('550e8400-e29b-41d4-a716-446655440000');
```

### getByUuid()

```php
public function getByUuid(string $uuid): array
```

GET /v2/webhooks/{uuid}

Получение информации о подписке по UUID

**Описание:**
Метод предназначен для получения информации о подписке клиента на вебхуки по UUID

Источник: https://apidoc.cdek.ru/#tag/webhook/operation/getById

@param   string  $uuid  UUID подписки на вебхуки.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->webhooks()->getByUuid('550e8400-e29b-41d4-a716-446655440000');
```

### getJoomlaWebhookUrl()

```php
public function getJoomlaWebhookUrl(): string
```

Возвращает URL Joomla для приема входящих вебхуков CDEK.

В URL добавляется токен из параметров системного плагина `wtcdek`,
если параметр `webhook_token` задан.

@return  string

Пример: 

```php
<?php

$result = $cdek->webhooks()->getJoomlaWebhookUrl();
```
