# Browser Verification Report

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Target Flow

Поле ПВЗ в глобальных настройках JoomShopping и tariff info в shipping-price row.

## Environment

13.10.2026 Joomla6.1.3 joomla.local, установленная более ранняя 1.4.0-dev, Chrome через оператором выбранный Playwright.

## Steps Executed

Saratov28 -> Moscow424 после изменения city/zip; blank address; FancySelect selection; tariff136->333; reload и Cancel без save.

## Observed Behaviour

Обычные сценарии прошли, исходные значения восстановлены после reload. 3 AJAX response successes; 0 pageerror.

## Network Or Console Notes

Read-only CDEK reference queries/cache; COOP warning plain HTTP unrelated. Passwords/session tokens в evidence не сохранены.

## Visual Or UX Findings

Native form rendering и FancySelect нормальных сценариев подтверждены. F1–F5 этим не закрываются.

## Verdict

Историческая проверка normal paths passed. Final unified endpoint ещё требует ручной browser acceptance; не выдавать старое evidence за новую установочную проверку. [Финальный AJAX и проверки](../../reports/2026-10-13-ajax-routing-verification.md); [открытый аудит F1–F5](../../reports/2026-10-12-fields-reaudit-review-findings.md); [браузерная проверка](../../reports/2026-10-13-jshopping-admin-browser-verification.md); [пакет](../../release/2026-10-13-1.4.0-local-package.md).
