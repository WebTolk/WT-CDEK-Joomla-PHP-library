# Joomla Form поля

Этот файл описывает Joomla Form поля библиотеки из `lib_webtolk_wtcdek/src/Fields/`.

## Доступные поля

- `Tarifflist`
- `Tariffinfo`

## Tarifflist

Класс: `Webtolk\Cdekapi\Fields\TarifflistField`

Базовый класс: Joomla `GroupedlistField`

### Логика работы

- загружает список тарифов через `Cdek()->calculator()->getAllTariffs()`
- строит сгруппированный список для `<field type="Tarifflist" />`
- использует `tariff_name` как название группы
- использует `delivery_modes[]` как элементы внутри группы
- сохраняет `tariff_code` как значение option

Формат подписи option:

```text
{delivery_mode_name} [{delivery_mode}] (code: {tariff_code})
```

Если `delivery_mode_name` пустой, используется запасной формат:

```text
mode {delivery_mode} (code: {tariff_code})
```

### Параметры XML

Сам класс поля не читает собственные кастомные XML-атрибуты.

Можно использовать стандартные атрибуты Joomla для списка, например:

- `name`
- `type="Tarifflist"`
- `label`
- `description`
- `default`
- `required`
- `multiple`
- `class`
- `hint`

### Пример

```xml
<field
    name="tariff_code"
    type="Tarifflist"
    label="Тариф"
    description="Выберите тариф CDEK"
    required="true"
/>
```

## Tariffinfo

Класс: `Webtolk\Cdekapi\Fields\TariffinfoField`

Базовый класс: Joomla `FormField`

Layout:

```text
libraries.webtolk.wtcdek.fields.tariffinfo
```

### Логика работы

- рендерит информационный блок по выбранному тарифу
- загружает данные тарифов через `Cdek()->calculator()->getAllTariffs()`
- строит внутренний индекс по `tariff_code`
- следит за значением другого поля формы и обновляет блок при изменении значения
- показывает название тарифа, код, режим доставки, ограничения по габаритам и типам контрагентов

Это поле само по себе не сохраняет код тарифа. Оно работает как вспомогательное отображаемое поле, связанное с другим полем формы.

### Поддерживаемые XML-параметры

Параметры, которые реально читаются PHP-кодом:

- `watchfield`
- `tariff_field`

`watchfield` — основной параметр.

`tariff_field` — обратнос совместимый alias. Если указаны оба параметра, используется `watchfield`.

Ожидаемое значение:

- имя связанного поля, в котором хранится выбранный `tariff_code`

JavaScript внутри поля ищет связанное поле по следующим селекторам:

- `#jform_{watchfield}`
- `[name="{watchfield}"]`
- `[name$="[{watchfield}]"]`

Поэтому для обычной Joomla формы безопаснее всего указывать просто имя поля, например `tariff_code`.

Также можно использовать стандартные атрибуты Joomla поля, например:

- `name`
- `type="Tariffinfo"`
- `label`
- `description`
- `class`

### Пример

```xml
<field
    name="tariff_code"
    type="Tarifflist"
    label="Тариф"
    required="true"
/>

<field
    name="tariff_info"
    type="Tariffinfo"
    label="Информация о тарифе"
    watchfield="tariff_code"
/>
```

### Что отображается

В блоке могут показываться:

- `tariff_name`
- `tariff_code`
- `delivery_mode`
- `delivery_mode_name`
- `weight`
- `length`
- `width`
- `height`
- `payer_contragent_type`
- `sender_contragent_type`
- `recipient_contragent_type`

### Примечания

- если `watchfield` не задан, поле показывает состояние ошибки
- если связанное поле не найдено в форме, поле показывает состояние ошибки
- если тариф ещё не выбран, поле показывает информационное пустое состояние
- если код тарифа выбран, но не найден в индексе, поле показывает предупреждение
