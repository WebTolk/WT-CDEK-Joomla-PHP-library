# Entity: reverse

Class: `Webtolk\Cdekapi\Entities\ReverseEntity`

## Methods

### checkAvailability()

```php
public function checkAvailability(array $request_options = []): array
```

POST /v2/reverse/availability

Проверка доступности реверса

**Описание:**
Метод позволяет проверить доступность реверса до создания прямого заказа. Для выполнения проверки
необходимо передать в запросе данные, аналогичные тем, которые будут использоваться при создании заказа,
включая направление, данные отправителя и получателя, офис отправки/доставки и выбранный тариф.
Если реверс доступен, API вернёт пустой ответ с кодом 200. В случае недоступности услуги или при наличии
ошибок в запросе, в ответе будет возвращено сообщение об ошибке с соответствующим описанием.

Источник: https://apidoc.cdek.ru/#tag/reverse/operation/checkAvailability

@param   array  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->reverse()->checkAvailability([
    'from_location' => ['code' => 44],
    'to_location'   => ['code' => 270],
]);
```
