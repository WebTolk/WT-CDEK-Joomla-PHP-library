# Investigation Report

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Question

Почему cookie n3t доходили до СДЭК и как сохранить единый AJAX-контракт для полей и виджета?

## Context

Старый AJAX читал root getArray(), а entities сохраняют пользовательские параметры. Добавленный отдельный endpoint поля оператор заменил единым router.

## Evidence

[Финальный AJAX и проверки](../../reports/2026-10-13-ajax-routing-verification.md); [открытый аудит F1–F5](../../reports/2026-10-12-fields-reaudit-review-findings.md); [браузерная проверка](../../reports/2026-10-13-jshopping-admin-browser-verification.md); [пакет](../../release/2026-10-13-1.4.0-local-package.md).

## Hypotheses

Подтверждена утечка из агрегированного request input; ошибка n3t как производителя cookie не подтверждена и не требуется для исправления.

## Findings

n3t хранит JSON в cc_cookie; root Joomla Input использует $_REQUEST. При включении cookie в request_order они могут попасть в getArray. Нативный регрессионный fixture воспроизводит утечку до исправления.

## Confirmed Root Cause

Граница AJAX-приёма использовала агрегированный запрос вместо явного GET/POST. Отдельный deliverypoints handler нарушал выбранную оператором архитектуру.

## Remaining Unknowns

Реальная provider acceptance после установки нового ZIP; runtime поведение открытых F1–F5 на финальном пакете.

## Recommendation

Сохранять единый onAjaxWtcdek и explicit GET/POST; проверить новый ZIP вручную, затем работать с backlog.
