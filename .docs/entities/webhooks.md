# WebhooksEntity

`Webtolk\Cdekapi\Entities\WebhooksEntity` используется для управления подписками на вебхуки СДЭК. Через нее можно посмотреть активные подписки, создать новую, удалить существующую и даже получить Joomla URL для входящих вебхуков, если используется системный плагин `wtcdek`.

Получение Entity:

```php
$webhooks = $cdek->webhooks();
```

## `getAllowedTypes(): array`

**Сигнатура**

```php
public function getAllowedTypes(): array
```

**REST API**

Не вызывает REST API.

**Что делает**

Возвращает список допустимых типов подписок, которые знает сама библиотека:

- `ORDER_STATUS`
- `ORDER_MODIFIED`
- `PRINT_FORM`
- `RECEIPT`
- `PREALERT_CLOSED`
- `ACCOMPANYING_WAYBILL`
- `OFFICE_AVAILABILITY`
- `DELIV_PROBLEM`
- `DELIV_AGREEMENT`
- `COURIER_INFO`

**Что возвращает**

Массив строк с допустимыми типами вебхуков.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();
$types = $cdek->webhooks()->getAllowedTypes();
```

## `getAll(): array`

**Сигнатура**

```php
public function getAll(): array
```

**REST API**

`GET /v2/webhooks`

**Что делает**

Возвращает все активные подписки на вебхуки.

**Параметры**

Метод не принимает параметров.

**Что возвращает**

Массив подписок.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();
$webhooks = $cdek->webhooks()->getAll();
```

## `create(string $url, string $type): array`

**Сигнатура**

```php
public function create(string $url, string $type): array
```

**REST API**

`POST /v2/webhooks`

**Что делает**

Создает новую подписку на вебхук.

**Параметры**

- `$url` — корректный URL обработчика;
- `$type` — один из типов из `getAllowedTypes()`.

Библиотека отдельно проверяет валидность URL и допустимость типа.

**Что возвращает**

Асинхронный ответ API со статусом создания подписки.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->webhooks()->create(
    'https://example.com/index.php?option=com_ajax&plugin=wtcdek&group=system&format=raw&action=webhook',
    'ORDER_STATUS'
);
```

## `deleteByUuid(string $uuid): array`

**Сигнатура**

```php
public function deleteByUuid(string $uuid): array
```

**REST API**

`DELETE /v2/webhooks/{uuid}`

**Что делает**

Удаляет подписку на вебхук по UUID.

**Параметры**

- `$uuid` — UUID подписки.

**Что возвращает**

Массив ответа API.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->webhooks()->deleteByUuid('11111111-1111-1111-1111-111111111111');
```

## `getByUuid(string $uuid): array`

**Сигнатура**

```php
public function getByUuid(string $uuid): array
```

**REST API**

`GET /v2/webhooks/{uuid}`

**Что делает**

Возвращает данные одной подписки на вебхук.

**Параметры**

- `$uuid` — UUID подписки.

**Что возвращает**

Массив данных по подписке.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->webhooks()->getByUuid('11111111-1111-1111-1111-111111111111');
```

## `getJoomlaWebhookUrl(): string`

**Сигнатура**

```php
public function getJoomlaWebhookUrl(): string
```

**REST API**

Не вызывает REST API.

**Что делает**

Собирает Joomla URL для приема входящих вебхуков через `com_ajax` и системный плагин `wtcdek`. Метод работает только если:

- плагин `System - WT Cdek` включен;
- в его параметрах задан `webhook_token`.

**Что возвращает**

Строку URL или пустую строку, если URL нельзя собрать.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();
$url = $cdek->webhooks()->getJoomlaWebhookUrl();
```
