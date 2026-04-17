# PrintEntity

`Webtolk\Cdekapi\Entities\PrintEntity` работает с печатными формами: штрихкодами мест и квитанциями к заказам. Это Entity для тех сценариев, где из CMS или back-office нужно запускать генерацию PDF и затем получать ссылки на готовые файлы.

Получение Entity:

```php
$print = $cdek->print();
```

## `barcodePrint(array $request_options = []): array`

**Сигнатура**

```php
public function barcodePrint(array $request_options = []): array
```

**REST API**

`POST /v2/print/barcodes`

**Что делает**

Запускает формирование PDF со штрихкодами мест.

**Параметры**

Библиотека требует только непустой массив. Обычно сюда передают список заказов.

**Что возвращает**

Асинхронный ответ API с UUID задания на формирование.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->print()->barcodePrint([
    'orders' => [
        ['order_uuid' => '11111111-1111-1111-1111-111111111111'],
    ],
]);
```

## `barcodeGet(string $uuid): array`

**Сигнатура**

```php
public function barcodeGet(string $uuid): array
```

**REST API**

`GET /v2/print/barcodes/{uuid}`

**Что делает**

Возвращает статус формирования штрихкодов и, когда файл готов, ссылку на PDF.

**Параметры**

- `$uuid` — UUID задания на печать.

**Что возвращает**

Массив со статусами `ACCEPTED`, `PROCESSING`, `READY`, `INVALID`, `REMOVED` и сопутствующими данными.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->print()->barcodeGet('11111111-1111-1111-1111-111111111111');
```

## `barcodeDownload(string $uuid): array`

**Сигнатура**

```php
public function barcodeDownload(string $uuid): array
```

**REST API**

`GET /v2/print/barcodes/{uuid}.pdf`

**Что делает**

Получает готовый PDF со штрихкодами.

**Параметры**

- `$uuid` — UUID задания на печать.

**Что возвращает**

Ответ API по готовому PDF. На практике это вызов по endpoint готового документа.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->print()->barcodeDownload('11111111-1111-1111-1111-111111111111');
```

## `waybillPrint(array $request_options = []): array`

**Сигнатура**

```php
public function waybillPrint(array $request_options = []): array
```

**REST API**

`POST /v2/print/orders`

**Что делает**

Запускает формирование PDF-квитанции к заказу или группе заказов.

**Параметры**

Библиотека требует только непустой массив.

**Что возвращает**

Асинхронный ответ API с UUID задания на печать.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->print()->waybillPrint([
    'orders' => [
        ['order_uuid' => '11111111-1111-1111-1111-111111111111'],
    ],
]);
```

## `waybillGet(string $uuid): array`

**Сигнатура**

```php
public function waybillGet(string $uuid): array
```

**REST API**

`GET /v2/print/orders/{uuid}`

**Что делает**

Возвращает статус формирования квитанции и ссылку на PDF, когда файл готов.

**Параметры**

- `$uuid` — UUID задания на печать.

**Что возвращает**

Массив со статусами формирования документа.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->print()->waybillGet('11111111-1111-1111-1111-111111111111');
```

## `waybillDownload(string $uuid): array`

**Сигнатура**

```php
public function waybillDownload(string $uuid): array
```

**REST API**

`GET /v2/print/orders/{uuid}.pdf`

**Что делает**

Получает готовую PDF-квитанцию.

**Параметры**

- `$uuid` — UUID задания на печать.

**Что возвращает**

Ответ API по готовому PDF-документу.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->print()->waybillDownload('11111111-1111-1111-1111-111111111111');
```
