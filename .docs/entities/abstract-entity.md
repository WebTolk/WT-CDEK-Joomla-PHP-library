# AbstractEntity

`Webtolk\Cdekapi\Entities\AbstractEntity` — это базовый класс для всех Entity библиотеки. Напрямую в прикладном коде его обычно не используют: объект создается внутри фасада `Cdek`, а в наследниках уже появляются методы конкретных разделов REST API.

## Зачем нужен

Этот класс нужен для одной задачи: передать во все Entity общий объект `CdekRequest`. Благодаря этому каждая сущность работает через один и тот же транспортный слой, использует одну и ту же авторизацию, кэш и обработку ошибок.

## Публичные методы

### `__construct(CdekRequest $request)`

**Сигнатура**

```php
public function __construct(CdekRequest $request)
```

**REST API**

Не вызывает REST API. Это внутренний служебный конструктор.

**Параметры**

- `$request` — объект транспортного слоя `CdekRequest`.

**Что возвращает**

Ничего. Конструктор сохраняет объект запроса в protected-свойство `$request`.

**Пример**

В обычной работе этот конструктор вызывается не вручную, а самим фасадом `Cdek`:

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();
$ordersEntity = $cdek->orders();
```
