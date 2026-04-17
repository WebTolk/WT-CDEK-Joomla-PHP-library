# LocationEntity

`Webtolk\Cdekapi\Entities\LocationEntity` — это справочная Entity для работы с географией СДЭК. Именно через нее обычно решают задачи автодополнения города, поиска кодов городов, индексов и привязки координат к населенному пункту.

Получение Entity:

```php
$location = $cdek->location();
```

## `getRegions(array $request_options = []): array`

**Сигнатура**

```php
public function getRegions(array $request_options = []): array
```

**REST API**

`GET /v2/location/regions`

**Что делает**

Возвращает список регионов. По умолчанию библиотека подставляет:

- `size = 1000`;
- `page = 0`;
- `lang = rus`.

Если `country_codes` передать массивом, библиотека сама склеит его в строку через запятую.

**Параметры**

- `country_codes` — массив кодов стран или уже готовая строка;
- `size`;
- `page`;
- `lang`.

**Что возвращает**

Массив регионов.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$regions = $cdek->location()->getRegions([
    'country_codes' => ['RU'],
]);
```

## `getCities(array $request_options = []): array`

**Сигнатура**

```php
public function getCities(array $request_options = []): array
```

**REST API**

`GET /v2/location/cities`

**Что делает**

Возвращает список населенных пунктов.

По умолчанию библиотека подставляет:

- `size = 500`;
- `page = 0`;
- `lang = rus`.

**Параметры**

- `country_codes`;
- `postal_code`;
- `region_code`;
- `city`;
- `fias_guid`;
- `size`;
- `page`;
- `lang`.

**Что возвращает**

Массив городов с кодами СДЭК и дополнительной справочной информацией.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$cities = $cdek->location()->getCities([
    'postal_code' => '630099',
    'city'        => 'Новосибирск',
    'size'        => 1,
]);
```

## `getPostalCodes(int $city_code): array`

**Сигнатура**

```php
public function getPostalCodes(int $city_code): array
```

**REST API**

`GET /v2/location/postalcodes`

**Что делает**

Возвращает список почтовых индексов по коду города СДЭК.

**Параметры**

- `$city_code` — код города СДЭК.

**Что возвращает**

Массив индексов или ошибку, если код города не передан.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$indexes = $cdek->location()->getPostalCodes(270);
```

## `getCityByCoordinates(array $request_options = []): array`

**Сигнатура**

```php
public function getCityByCoordinates(array $request_options = []): array
```

**REST API**

`GET /v2/location/coordinates`

**Что делает**

Определяет локацию по координатам.

**Параметры**

Можно передать:

- `latitude` и `longitude`;
- либо сокращенные варианты `lat` и `lng`;
- либо `lat` и `lon`.

Библиотека сама нормализует короткие названия ключей.

**Что возвращает**

Массив с определенной локацией.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->location()->getCityByCoordinates([
    'lat' => 55.030199,
    'lng' => 82.920430,
]);
```

## `suggestCities(string $city_name, string $country_code = ''): array`

**Сигнатура**

```php
public function suggestCities(string $city_name, string $country_code = ''): array
```

**REST API**

`GET /v2/location/suggest/cities`

**Что делает**

Возвращает подсказки по названию города. Это один из самых удобных методов для AJAX-поиска города в форме оформления заказа.

**Параметры**

- `$city_name` — обязательная строка поиска;
- `$country_code` — необязательный ISO-код страны.

**Что возвращает**

Массив подсказок. Обычно каждый элемент содержит `city_uuid`, `code`, `full_name`, `country_code`.

**Пример**

```php
<?php

declare(strict_types=1);

use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die;

$cdek = new Cdek();

$result = $cdek->location()->suggestCities('Саратов', 'RU');
```
