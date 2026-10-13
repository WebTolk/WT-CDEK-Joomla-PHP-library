# Decision Log

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Decision

Единый onAjaxWtcdek по action; Watchfield как общий WebAsset и helper, без отдельного FormField.

## Context

Оператор явно исправил первоначальную архитектуру отдельного deliverypoints endpoint и потребовал site/admin поддержку.

## Options Considered

Отдельный AJAX-method для поля; единый endpoint с action; cookie blacklist по имени; явные источники GET/POST.

## Chosen Direction

Единый endpoint и явные источники GET/POST. Ветка deliverypoints возвращает нормализованные options, offices/calculate сохраняют widget raw JSON.

## Consequences

Field URL/POST action изменены; Joomla event results используют общий массив/addResult; session failure останавливает provider call; integrators обновляют endpoint или XML custom provider.

## Revisit Trigger

Подтверждённый BC дефект, новый action или изменение публичного transport/data contract. [ADR](../../adr/001-unified-cdek-ajax.md).
