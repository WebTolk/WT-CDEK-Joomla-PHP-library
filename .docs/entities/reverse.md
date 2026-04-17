# ReverseEntity

`Webtolk\Cdekapi\Entities\ReverseEntity` используется для проверки доступности реверса до создания прямого заказа. Это полезный метод, если бизнес-процесс предусматривает возвратные сценарии и нужно заранее понять, доступна ли такая логистика для выбранного направления.

Получение Entity:

```php
$reverse = $cdek->reverse();
```

## `checkAvailability(array $request_options = []): array`

**Сигнатура**

```php
public function checkAvailability(array $request_options = []): array
```

**REST API**

`POST /v2/reverse/availability`

**Что делает**

Проверяет, доступен ли реверс для будущего заказа.

**Параметры**

Библиотека проверяет только непустой массив. Обычно передают тариф, направление, отправителя, получателя и данные по пунктам отправления/доставки.

**Что возвращает**

Если реверс доступен, API обычно возвращает успешный ответ без бизнес-ошибки. Если нет — описание причины недоступности.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->reverse()->checkAvailability([
    'tariff_code'   => 136,
    'from_location' => ['code' => 44],
    'to_location'   => ['code' => 270],
]);
```
