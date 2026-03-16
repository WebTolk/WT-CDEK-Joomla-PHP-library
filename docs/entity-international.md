# Entity: international

Class: `Webtolk\Cdekapi\Entities\InternationalEntity`

## Methods

### checkPackagesRestrictions()

```php
public function checkPackagesRestrictions(array $request_options = []): array
```

POST /v2/international/package/restrictions

Получение ограничений по международным заказам

**Описание:**
Метод предназначен для получения ограничений по направлению и тарифу для международного заказа.

Источник: https://apidoc.cdek.ru/#tag/restriction_hints/operation/checkPackagesRestrictions

@param   array  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->international()->checkPackagesRestrictions([
    'from_location' => ['country_code' => 'RU'],
    'to_location'   => ['country_code' => 'KZ'],
]);
```
