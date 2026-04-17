# PhotoDocumentEntity

`Webtolk\Cdekapi\Entities\PhotoDocumentEntity` нужна для работы с фотоуслугой СДЭК. Через нее получают список заказов, по которым уже готовы фотоархивы для скачивания.

Получение Entity:

```php
$photoDocument = $cdek->photoDocument();
```

## `getReadyOrders(array $request_options = []): array`

**Сигнатура**

```php
public function getReadyOrders(array $request_options = []): array
```

**REST API**

`POST /v2/photoDocument`

**Что делает**

Возвращает список заказов с готовыми фото и ссылками на архивы.

**Параметры**

Библиотека требует только непустой массив. На практике API позволяет передавать либо период, либо список заказов.

**Что возвращает**

Массив заказов с готовыми фотоархивами.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->photoDocument()->getReadyOrders([
    'date' => [
        'from' => '2026-04-01',
        'to'   => '2026-04-17',
    ],
]);
```
