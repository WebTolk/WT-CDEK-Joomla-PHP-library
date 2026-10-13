# TarifflistField: список тарифов

`Webtolk\Cdekapi\Fields\TarifflistField` — стандартный сгруппированный список Joomla. Значение поля — **код тарифа**, например `136`. Группа показывает название тарифа, вариант — режим доставки и его код.

```xml
<form addfieldprefix="Webtolk\Cdekapi\Fields">
  <fieldset name="cdek">
    <field name="tariff_code" type="Tarifflist"
           label="Тариф СДЭК" default="136" required="true" />
    <field name="tariff_details" type="Tariffinfo"
           label="Условия тарифа" watchfield="tariff_code" />
  </fieldset>
</form>
```

Список берётся из локального каталога `calculator()->getAllTariffs()`. Это справочник режимов и ограничений; поле не рассчитывает стоимость конкретной доставки и не создаёт отправление. Для расчёта используйте [CalculatorEntity](../entities/calculator.md).

Поле поддерживает обычные атрибуты списка Joomla: `name`, `label`, `description`, `default`, `required`, `disabled`, `class`, `showon`. Само поле не требует нового JS. Связанное [Tariffinfo](./tariffinfo.md) подключает ассет наблюдения и автоматически обновляет карточку.

В **1.4.0-dev** существующий XML типа `Tarifflist` сохраняется. Браузерные проверки подтвердили связь с несколькими карточками Tariffinfo и работу списка внутри native subform.
