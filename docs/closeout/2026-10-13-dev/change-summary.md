# Change Summary

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## What Changed

Нейтральные поля выбора ПВЗ и тарифной информации используют общий Watchfield. Включён единый site/admin AJAX-router; cookie leakage и продолжение после неверного token устранены.

## Why It Changed

Разделить транспорт/наблюдение и renderer, избежать передачи cookie в СДЭК, выполнить указание оператора о едином AJAX-method.

## Behavioural Effect

Изменение адреса обновляет пункты; изменение тарифа обновляет карточку; deliverypoints branch отдаёт options; widget branches сохраняют raw JSON.

## Compatibility Notes

XML literal presence отключает watcher даже при пустом значении; FIAS normalize; новый endpoint поля plugin=wtcdek/action=deliverypoints. Старые overrides и server options validation имеют открытые findings.

## Known Limits

Ручная установка финального 1.4.0 и реальный ответ СДЭК ещё не проверены. Четыре P2 F1–F4 и P3 F5 остаются открытыми; checkpoint dev не является публичным релизом.

## Linked Artifacts

[Финальный AJAX и проверки](../../reports/2026-10-13-ajax-routing-verification.md); [открытый аудит F1–F5](../../reports/2026-10-12-fields-reaudit-review-findings.md); [браузерная проверка](../../reports/2026-10-13-jshopping-admin-browser-verification.md); [пакет](../../release/2026-10-13-1.4.0-local-package.md).

## Toolchain Contract References

Платформа Joomla 4/5/6; toolchain PHP/Node и phing via configured toolchain. PhpStorm применял кодовые правки/inspections, shell запускал native fixtures и стандартный packager. PHPUnit/phpstan/php-cs-fixer не запускались и не выдаются за пройденные gates.
