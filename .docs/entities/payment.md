# PaymentEntity

`Webtolk\Cdekapi\Entities\PaymentEntity` нужна для получения сведений о переводах наложенного платежа интернет-магазину. Это полезно в бухгалтерских и сверочных сценариях.

Получение Entity:

```php
$payment = $cdek->payment();
```

## `get(array $request_options = []): array`

**Сигнатура**

```php
public function get(array $request_options = []): array
```

**REST API**

`GET /v2/payment`

**Что делает**

Возвращает информацию по переводам наложенного платежа за указанную дату.

**Параметры**

- `date` — обязательная дата.

**Что возвращает**

Массив данных о перечислениях.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->payment()->get([
    'date' => '2026-04-17',
]);
```
