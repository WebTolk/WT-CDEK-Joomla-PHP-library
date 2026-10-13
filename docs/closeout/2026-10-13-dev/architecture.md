# Architecture

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Current State

Финальные исходники и ZIP 1.4.0 в dev; стенд остаётся на более раннем installed 1.4.0-dev.

## Target State

Общий scanner/transport Watchfield, специализированные renderers, PHP helper и сервис нормализации; один AJAX-router для site/admin.

## Design Decisions

Action offices/calculate/deliverypoints; explicit GET/POST; posted CSRF у field; URL/POST/header CSRF у widget; native Event/addResult shape.

## Alternatives Rejected

Standalone Watchfield FormField; donor-specific tariff/orderstatus fields; отдельный onAjaxWtcdekdeliverypoints; blacklist конкретного cookie.

## Interfaces And Dependencies

Joomla Form, Input, Session, Event, JsonResponse и WAM; Cdek entities и DeliverypointsService; контракт details в public-contracts.md.

## Risk Controls

Валидация до client creation, stale response guard/debounce, text-only labels, normalizers, офлайн fixtures и архивная проверка. Backlog F1–F5 явно открыт.

## Rollout Order

Сохранить dev -> ручная установка 1.4.0 -> site/admin UI и provider acceptance -> отдельные fixes -> повторная assurance -> решение о публичном release.
