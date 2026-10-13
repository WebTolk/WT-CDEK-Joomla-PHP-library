# ADR 001: единый AJAX WT CDEK

Статус: принято оператором 13.10.2026. Запись создана ретроспективно при closeout; не выдаётся за предварительное проектирование.

## Контекст

Первоначально поле имело отдельный onAjaxWtcdekdeliverypoints. Оператор указал использовать один onAjaxWtcdek с простым routing по action и разрешить админку. Root getArray мог включать cookie n3t.

## Решение

Один onAjaxWtcdek с offices/calculate/deliverypoints. GET/POST читаются явно, POST имеет приоритет; cookie не достигают provider parameters. Поле использует plugin=wtcdek и action=deliverypoints. JSON envelope поля и raw JSON виджета сохраняются с правильным Joomla event result. Недействительный token не вызывает клиента.

## Последствия

Старый отдельный endpoint удалён; consumers обновляются вместе с полем. Field posted token и валидация остаются; widget URL/POST/header token поддерживаются. Публичный контракт и migration notes: docs/closeout/2026-10-13-dev. F1–F5 не закрываются этим решением.

## Проверка

540 native assertions Joomla4/5/6, 110 archive assertions, package audit. Реальный новый installation/browser/provider acceptance остаётся pending.
