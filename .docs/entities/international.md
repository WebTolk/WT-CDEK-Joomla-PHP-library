# InternationalEntity

`Webtolk\Cdekapi\Entities\InternationalEntity` нужна для международной логистики. Через нее можно заранее проверить ограничения по отправлению: подойдет ли направление, тариф и состав отправления еще до фактического создания заказа.

Получение Entity:

```php
$international = $cdek->international();
```

## `checkPackagesRestrictions(array $request_options = []): array`

**Сигнатура**

```php
public function checkPackagesRestrictions(array $request_options = []): array
```

**REST API**

`POST /v2/international/package/restrictions`

**Что делает**

Проверяет ограничения по международным заказам.

**Параметры**

Библиотека проверяет только то, что массив не пустой. Практически сюда передают направление, тариф и характеристики отправления.

**Что возвращает**

Массив ответа API. Если ограничений нет, возвращается успешный ответ. Если ограничения есть, API возвращает описание проблемы.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->international()->checkPackagesRestrictions([
    'tariff_code'   => 7,
    'from_location' => ['country_code' => 'RU'],
    'to_location'   => ['country_code' => 'KZ'],
    'packages'      => [
        ['weight' => 500],
    ],
]);
```
