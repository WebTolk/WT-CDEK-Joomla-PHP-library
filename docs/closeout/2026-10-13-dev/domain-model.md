# Domain Model

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Entities

Address(city/postal_code/fias_guid); delivery point(provider code/type/location/flags); option(value/text); tariff(code/name/constraints); form target и source fields.

## Relationships

Address/filter -> points -> options -> selected code. Tariff selection -> reference record -> rendered info. Watchfield coordinates source snapshots and transport; renderer displays data.

## Rules

Presence XML literals disables watcher; multiple city codes yield empty list; UUID is FIAS, not CDEK city_uuid; normalized booleans avoid PHP false removal when passing entity query.

## Boundaries

Business/order submission is outside this form/reference workflow; CDEK provider is read-only here. Session/cookie data is not a domain parameter.
