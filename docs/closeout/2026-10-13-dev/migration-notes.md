# Migration Notes

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## When Migration Is Required

При обновлении более ранних prototype/dev installations или custom overrides/provider URLs.

## Preconditions

Backup Joomla extension params; correct installable 1.4.0 ZIP hash; оператор выполняет ручную установку. PF onboarding отдельно, в этом checkpoint не выполняется.

## Steps

Установить стандартный ZIP; проверить library/system/task versions; WAM assets и refreshed page cache; field calls plugin=wtcdek/action=deliverypoints; test site/admin. Старый standalone Watchfield field class заменён Helper namespace.

## Backward Compatibility

offices/calculate raw JSON сохраняются; deliverypoints options JSON path сохраняется. Старый onAjaxWtcdekdeliverypoints удалён. XML custom endpoint можно задать отдельно. Legacy Tariffinfo overrides F3 требуют отдельного решения.

## Data Or Config Impact

Миграции business data/DB нет. Существующие point/tariff values сохраняются, но validate=options F4 может препятствовать native submit.

## Rollback Strategy

Сохранённые ZIP/source snapshots и backup params. Не удалять библиотеку как способ отката без проверки consumers.

## Toolchain Contract References

phing via configured toolchain; Joomla native installer для будущей ручной проверки.
