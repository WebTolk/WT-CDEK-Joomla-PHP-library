# Entity: calculator

Class: `Webtolk\Cdekapi\Entities\CalculatorEntity`

## Methods

### getAllTariffs()

```php
public function getAllTariffs(): array
```

Возвращает коды тарифов, доступных по текущему договору.

Endpoint: `GET /v2/calculator/alltariffs`

Ответ API содержит поле `tariff_codes`; метод возвращает только это поле.

@return  array<int, array<string, mixed>>  Список метаданных кодов тарифов.

Пример: 

```php
<?php

$result = $cdek->calculator()->getAllTariffs();
```

### calculateTariff()

```php
public function calculateTariff(array $request_options = []): array
```

Выполняет расчет доставки по конкретному коду тарифа.

Endpoint: `POST /v2/calculator/tariff`

Обязательные ключи:
- `tariff_code`
- `from_location`
- `to_location`
- `packages` (каждое место должно содержать `weight`)

@param   array{
            tariff_code?: int|string,
            type?: int|string,
            date?: string,
            currency?: int|string,
            from_location?: array{
                code?: int|string,
                city?: string,
                country_code?: string,
                postal_code?: string
            },
            to_location?: array{
                code?: int|string,
                city?: string,
                country_code?: string,
                postal_code?: string
            },
            services?: array<int, array{code: string, parameter?: int|float|string}>,
            packages?: array<int, array{
                weight: int|float|string,
                length?: int|float|string,
                width?: int|float|string,
                height?: int|float|string
            }>
        }  $request_options  Параметры запроса калькулятора.

@return  array  Ответ API или структурированная ошибка валидации.

Пример: 

```php
<?php

$result = $cdek->calculator()->calculateTariff([
    'tariff_code'   => 136,
    'from_location' => ['code' => 44],
    'to_location'   => ['code' => 270],
    'packages'      => [
        [
            'weight' => 1000,
            'length' => 10,
            'width'  => 10,
            'height' => 10,
        ],
    ],
]);
```

### calculateTariffList()

```php
public function calculateTariffList(array $request_options = []): array
```

Выполняет расчет доставки по всем доступным тарифам.

Endpoint: `POST /v2/calculator/tarifflist`

Обязательные ключи:
- `from_location`
- `to_location`
- `packages` (каждое место должно содержать `weight`)

@param   array{
            type?: int|string,
            date?: string,
            currency?: int|string,
            from_location?: array{
                code?: int|string,
                city?: string,
                country_code?: string,
                postal_code?: string
            },
            to_location?: array{
                code?: int|string,
                city?: string,
                country_code?: string,
                postal_code?: string
            },
            services?: array<int, array{code: string, parameter?: int|float|string}>,
            packages?: array<int, array{
                weight: int|float|string,
                length?: int|float|string,
                width?: int|float|string,
                height?: int|float|string
            }>
        }  $request_options  Параметры запроса калькулятора.

@return  array  Ответ API или структурированная ошибка валидации.

Пример: 

```php
<?php

$result = $cdek->calculator()->calculateTariffList([
    'from_location' => ['code' => 44],
    'to_location'   => ['code' => 270],
    'packages'      => [
        [
            'weight' => 1000,
        ],
    ],
]);
```

### tariffAndService()

```php
public function tariffAndService(array $request_options = []): array
```

Выполняет расчет по доступным тарифам и дополнительным услугам.

Endpoint: `POST /v2/calculator/tariffAndService`

Обязательные ключи:
- `from_location`
- `to_location`
- `packages` (каждое место должно содержать `weight`)

Описание услуг в `services[]` (перенесено из фасадного метода):
- `code`: код услуги из справочника дополнительных услуг.
- `parameter`: значение параметра услуги зависит от `code`.
  1) количество для: `PACKAGE_1`, `COURIER_PACKAGE_A2`, `SECURE_PACKAGE_A2`,
     `SECURE_PACKAGE_A3`, `SECURE_PACKAGE_A4`, `SECURE_PACKAGE_A5`,
     `CARTON_BOX_XS`, `CARTON_BOX_S`, `CARTON_BOX_M`, `CARTON_BOX_L`,
     `CARTON_BOX_500GR`, `CARTON_BOX_1KG`, `CARTON_BOX_2KG`,
     `CARTON_BOX_3KG`, `CARTON_BOX_5KG`, `CARTON_BOX_10KG`,
     `CARTON_BOX_15KG`, `CARTON_BOX_20KG`, `CARTON_BOX_30KG`,
     `CARTON_FILLER`.
  2) объявленная стоимость заказа для `INSURANCE` (только для заказов типа "доставка").
  3) длина для `BUBBLE_WRAP`, `WASTE_PAPER`.
  4) количество фотографий для `PHOTO_DOCUMENT`.

@param   array{
            type?: int|string,
            date?: string,
            currency?: int|string,
            from_location?: array{
                code?: int|string,
                city?: string,
                country_code?: string,
                postal_code?: string
            },
            to_location?: array{
                code?: int|string,
                city?: string,
                country_code?: string,
                postal_code?: string
            },
            services?: array<int, array{code: string, parameter?: int|float|string}>,
            packages?: array<int, array{
                weight: int|float|string,
                length?: int|float|string,
                width?: int|float|string,
                height?: int|float|string
            }>
        }  $request_options  Параметры запроса калькулятора.

@return  array  Ответ API или структурированная ошибка валидации.

Пример: 

```php
<?php

$result = $cdek->calculator()->tariffAndService([
    'from_location' => ['code' => 44],
    'to_location'   => ['code' => 270],
    'packages'      => [
        [
            'weight' => 1000,
        ],
    ],
    'services'      => [
        ['code' => 'INSURANCE', 'parameter' => 1000],
    ],
]);
```
