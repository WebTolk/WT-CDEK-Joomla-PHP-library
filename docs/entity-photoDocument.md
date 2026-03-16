# Entity: photoDocument

Class: `Webtolk\Cdekapi\Entities\PhotoDocumentEntity`

## Methods

### getReadyOrders()

```php
public function getReadyOrders(array $request_options = []): array
```

POST /v2/photoDocument

Получение заказов с готовыми фото

**Описание:**
Метод используется для получения перечня заказов с ссылками на готовые к скачиванию архивы с фото. В
запросе необходимо передать либо период, за который необходимо вернуть перечень заказов, либо список
заказов. Если переданы и период, и список заказов, то период игнорируется.

Для корректной работы метода, для договора должна быть подключена фотоуслуга, а также настроен
фотопроект.

Источник: https://apidoc.cdek.ru/#tag/photo/operation/getReadyOrders

@param   array  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->photoDocument()->getReadyOrders([
    'date' => '2026-03-01',
]);
```
