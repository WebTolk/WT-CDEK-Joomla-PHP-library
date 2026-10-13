# Release Notes

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Release Scope

Локальный пакет 1.4.0 и dev source checkpoint; GitHub release/tag/deploy не создавались.

## User-Visible Changes

Выбор ПВЗ/постаматов по адресу; автоматическое обновление списка; обновление карточки тарифа; совместимость с cookie consent banners. RU/EN Whats New содержат четыре коротких пункта.

## Internal Changes

Watchfield WebAsset/helper; единый AJAX-router для site/admin; explicit GET/POST и stopping CSRF failure; PF file headers/JS metadata.

## Verification Status

650 narrow assertions включая archive, PHP40/JS3/headers43/manifests4/languages2. [Финальный AJAX и проверки](../../reports/2026-10-13-ajax-routing-verification.md); [открытый аудит F1–F5](../../reports/2026-10-12-fields-reaudit-review-findings.md); [браузерная проверка](../../reports/2026-10-13-jshopping-admin-browser-verification.md); [пакет](../../release/2026-10-13-1.4.0-local-package.md).

## Risks And Caveats

Ручная установка финального 1.4.0 и реальный ответ СДЭК ещё не проверены. Четыре P2 F1–F4 и P3 F5 остаются открытыми; checkpoint dev не является публичным релизом.

## Rollback Notes

Product base main887e4ff; прежние ZIP сохранены локально. Для установленного dev возврат source-package надо выполнять Joomla installer, избегая удаления конфигурации.

## Toolchain Contract References

phing via configured toolchain; native PHP/Node verification. Local ZIP hash: 2e17b506ffa953f1f6d86d38175116bf5f3b2b9b2f19d3cd2c7dc0c16e2c2982
