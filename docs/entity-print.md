# Entity: print

Class: `Webtolk\Cdekapi\Entities\PrintEntity`

## Methods

### barcodePrint()

```php
public function barcodePrint(array $request_options = []): array
```

POST /v2/print/barcodes

Формирование ШК места к заказу

**Описание:**
Метод используется для формирования ШК места в формате pdf к заказу/заказам.

Во избежание перегрузки платформы нельзя передавать более 100 номеров заказов в одном запросе.

Источник: https://apidoc.cdek.ru/#tag/print/operation/barcodePrint

@param   array  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->print()->barcodePrint([
    'orders' => [
        ['order_uuid' => '550e8400-e29b-41d4-a716-446655440000'],
    ],
]);
```

### barcodeGet()

```php
public function barcodeGet(string $uuid): array
```

GET /v2/print/barcodes/{uuid}

Получение ШК места к заказу

**Описание:**
Метод используется для получения ШК места в формате pdf к заказу/заказам.
Ссылка на файл с ШК местом к заказу/заказам доступна в течение 1 часа.

В ответе метода возвращается набор статусов entity->statuses. Их значения могут быть следующими
| Код       | Название статуса    | Комментарий |
|-----------|---------------------|-------------|
| ACCEPTED  | Принят              | Запрос на формирование квитанции принят |
| INVALID   | Некорректный запрос | Некорректный запрос на формирование квитанции |
| PROCESSING| Формируется         | Файл с квитанцией формируется |
| READY     | Сформирован         | Файл с квитанцией и ссылка на скачивание файла сформированы|
| REMOVED   | Удален                 | Истекло время жизни ссылки на скачивание файла с квитанцией|

Источник: https://apidoc.cdek.ru/#tag/print/operation/barcodeGet

@param   string  $uuid  UUID запроса на печать.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->print()->barcodeGet('550e8400-e29b-41d4-a716-446655440000');
```

### barcodeDownload()

```php
public function barcodeDownload(string $uuid): array
```

GET /v2/print/barcodes/{uuid}.pdf

Скачивание готового ШК

**Описание:**
Скачивание ШК места в формате pdf к заказу/заказам

Источник: https://apidoc.cdek.ru/#tag/print/operation/barcodeDownload

@param   string  $uuid  UUID запроса на печать.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->print()->barcodeDownload('550e8400-e29b-41d4-a716-446655440000');
```

### waybillPrint()

```php
public function waybillPrint(array $request_options = []): array
```

POST /v2/print/orders

Формирование квитанции к заказу

**Описание:**
Метод используется для формирования квитанции в формате pdf к заказу/заказам.

Во избежание перегрузки платформы нельзя передавать более 100 номеров заказов в одном запросе.

Источник: https://apidoc.cdek.ru/#tag/print/operation/waybillPrint

@param   array  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->print()->waybillPrint([
    'orders' => [
        ['order_uuid' => '550e8400-e29b-41d4-a716-446655440000'],
    ],
]);
```

### waybillGet()

```php
public function waybillGet(string $uuid): array
```

GET /v2/print/orders/{uuid}

Получение квитанции к заказу

**Описание:**
Метод используется для получения ссылки на квитанцию в формате pdf к заказу/заказам.

Ссылка на файл с квитанцией к заказу/заказам доступна в течение 1 часа.

В ответе метода возвращается набор статусов entity->statuses. Их значения могут быть следующими
| Код       | Название статуса    | Комментарий  |
|-----------|---------------------|--------------|
| ACCEPTED  | Принят              | Запрос на формирование квитанции принят |
| INVALID   | Некорректный запрос | Некорректный запрос на формирование квитанции |
| PROCESSING| Формируется         | Файл с квитанцией формируется |
| READY     | Сформирован         | Файл с квитанцией и ссылка на скачивание файла сформированы|
| REMOVED   | Удален              | Истекло время жизни ссылки на скачивание файла с квитанцией|

Источник: https://apidoc.cdek.ru/#tag/print/operation/waybillGet

@param   string  $uuid  UUID запроса на печать.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->print()->waybillGet('550e8400-e29b-41d4-a716-446655440000');
```

### waybillDownload()

```php
public function waybillDownload(string $uuid): array
```

GET /v2/print/orders/{uuid}.pdf

Скачивание готовой квитанции

**Описание:**
Скачивание квитанции в формате pdf к заказу/заказам.

Источник: https://apidoc.cdek.ru/#tag/print/operation/waybillDownload

@param   string  $uuid  UUID запроса на печать.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->print()->waybillDownload('550e8400-e29b-41d4-a716-446655440000');
```
