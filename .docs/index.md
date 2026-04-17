# Документация по Entity-библиотеке WT CDEK для Joomla

WT CDEK Joomla PHP library — это нативная библиотека для Joomla 4.2.7+ и Joomla 5, которая закрывает типовой сценарий интеграции со СДЭК через REST API без необходимости писать свой транспортный слой, отдельно заниматься OAuth 2.0 и вручную собирать URL для каждого метода API.

Начиная с версии `1.3.0`, библиотека построена по entity-based подходу. Это значит, что вы работаете не с одним «толстым» классом, а с фасадом `Webtolk\Cdekapi\Cdek`, который отдает специализированные Entity:

- `calculator()` — расчёт тарифов;
- `check()` — чеки;
- `delivery()` — договорённости о доставке;
- `deliverypoints()` — ПВЗ и постаматы;
- `intakes()` — вызов курьера;
- `international()` — ограничения по международным отправлениям;
- `location()` — регионы, города, индексы, подсказки;
- `oauth()` — ручное получение токена;
- `orders()` — заказы;
- `passport()` — паспортные данные для международных отправлений;
- `payment()` — наложенные платежи;
- `photoDocument()` — фотоуслуги;
- `prealert()` — преалерты;
- `print()` — печатные формы и штрихкоды;
- `reverse()` — проверка реверса;
- `webhooks()` — подписки на вебхуки.

## С чего начать

Если плагин `System - WT Cdek` уже включен и в нем заполнены `client_id` и `client_secret`, достаточно создать объект `Cdek` без аргументов.

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();
```

Если credentials нужно передать явно, это можно сделать в конструкторе:

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek(
    test_mode: true,
    client_id: 'your_client_id',
    client_secret: 'your_client_secret'
);
```

## Как читать эту документацию

В каждой Entity-странице есть:

- объяснение, зачем сущность нужна в реальной интеграции;
- список всех публичных методов;
- сигнатура метода в том виде, в котором он есть в библиотеке;
- REST endpoint и HTTP-метод;
- описание параметров, которые реально проверяет библиотека;
- описание результата;
- минимальный пример PHP-кода с нужными `namespace` и `use`.

Важно понимать разницу между двумя уровнями валидации:

- библиотека проверяет только часть обязательных полей;
- полную бизнес-валидацию выполняет уже API СДЭК.

Поэтому ситуация, когда библиотека вернула успешный HTTP-запрос, а API СДЭК ответило ошибкой в массиве `error_code` и `error_message`, является нормальной и ожидаемой.

## Общий формат ответа

Почти все методы библиотеки возвращают `array`.

Успешный ответ:

- это обычный массив данных API СДЭК;
- структура зависит от конкретного endpoint.

Ответ с ошибкой:

```php
[
    'error_code'    => 400,
    'error_message' => 'Текст ошибки',
]
```

## Что библиотека делает сама

Библиотека автоматически:

- определяет боевую или тестовую среду;
- получает OAuth-токен;
- кэширует токен средствами Joomla;
- нормализует часть ошибок API;
- кэширует некоторые справочные запросы, например тарифы и ПВЗ.

## Entity

- [CalculatorEntity](./entities/calculator.md)
- [CheckEntity](./entities/check.md)
- [DeliveryEntity](./entities/delivery.md)
- [DeliverypointsEntity](./entities/deliverypoints.md)
- [IntakesEntity](./entities/intakes.md)
- [InternationalEntity](./entities/international.md)
- [LocationEntity](./entities/location.md)
- [OauthEntity](./entities/oauth.md)
- [OrdersEntity](./entities/orders.md)
- [PassportEntity](./entities/passport.md)
- [PaymentEntity](./entities/payment.md)
- [PhotoDocumentEntity](./entities/photo-document.md)
- [PrealertEntity](./entities/prealert.md)
- [PrintEntity](./entities/print.md)
- [ReverseEntity](./entities/reverse.md)
- [WebhooksEntity](./entities/webhooks.md)
- [AbstractEntity](./entities/abstract-entity.md)
