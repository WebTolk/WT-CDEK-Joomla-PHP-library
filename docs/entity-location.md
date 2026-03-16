# Entity: location

Class: `Webtolk\Cdekapi\Entities\LocationEntity`

## Methods

### getRegions()

```php
public function getRegions(array $request_options = []): array
```

GET /v2/location/regions

Получение списка регионов

**Описание:**
Метод предназначен для получения детальной информации о регионах.

Список регионов может быть ограничен характеристиками, задаваемыми пользователем.

Источник: https://apidoc.cdek.ru/#tag/location/operation/regions

@param   array  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->location()->getRegions([
    'country_codes' => ['RU'],
]);
```

### getCities()

```php
public function getCities(array $request_options = []): array
```

GET /v2/location/cities

Получение списка населенных пунктов

**Описание:**
Метод предназначен для получения детальной информации о населенных пунктах.

Список населенных пунктов может быть ограничен характеристиками, задаваемыми пользователем.

Источник: https://apidoc.cdek.ru/#tag/location/operation/cities

@param   array  $request_options  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->location()->getCities([
    'postal_code' => '410012',
    'city'        => 'Saratov',
    'size'        => 1,
]);
```

### getPostalCodes()

```php
public function getPostalCodes(int $city_code): array
```

GET /v2/location/postalcodes

Получение почтовых индексов города

**Описание:**
Метод предназначен для получения списка почтовых индексов.

Источник: https://apidoc.cdek.ru/#tag/location/operation/postalcodes

@param   int  $city_code  Параметры запроса.

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->location()->getPostalCodes(44);
```

### getCityByCoordinates()

```php
public function getCityByCoordinates(array $request_options = []): array
```

GET /v2/location/coordinates

Получение локации по координатам

**Описание:**
Метод позволяет определить локацию по переданным в запросе координатам

Источник: https://apidoc.cdek.ru/#tag/location/operation/getCityByCoordinates

@param   array  $request_options  Параметры запроса. Допустимые ключи координат:
                       `latitude` (или `lat`) и `longitude` (или `lng`/`lon`).

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->location()->getCityByCoordinates([
    'latitude'  => 51.533562,
    'longitude' => 46.034266,
]);
```

### suggestCities()

```php
public function suggestCities(string $city_name, string $country_code = ''): array
```

GET /v2/location/suggest/cities

Подбор локации по названию города

**Описание:**
Метод позволяет получать подсказки по подбору населенного пункта по его наименованию.

Список населенных пунктов может быть ограничен характеристиками, задаваемыми пользователем.

Источник: https://apidoc.cdek.ru/#tag/location/operation/suggestCities

@param   string  $city_name     Наименование города для подбора подсказок.
@param   string  $country_code  Код страны в формате ISO_3166-1

@return  array  Ответ API.

Пример: 

```php
<?php

$result = $cdek->location()->suggestCities('Saratov', 'RU');
```
