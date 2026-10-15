# REL-007: Привести стиль и PHPDoc к общему PHP QA-профилю

Приоритет: **P3**. Статус: **Style-часть выполнена; PHPDoc/type cleanup — следующий релиз**.

## Подтверждение

PHPCS: 4915 style errors/60 warnings в 39 файлах; Fixer dry-run предлагает 38 файлов. Это срез до узкой правки установщика.

## Места в коде

- `lib_webtolk_wtcdek/src/Cdek.php:1112`
- `plg_system_wtcdek/src/Extension/Wtcdek.php:89`
- `plg_system_wtcdek/src/Field/PlugininfoField.php:88`
- `plg_task_updatewtcdekdata/src/Field/PlugininfoField.php:88`

## Проблема

Табы, braces/EOL/imports и PHPDoc расходятся с общим Joomla PSR-12 профилем. Есть некорректный namespace WebAssetRegistry в PHPDoc, пустой getLabel с обещанным string, unused imports/methods, неверные типы TASKS_MAP и @return.

## Требуемое изменение

Выполнить отдельный style/type cleanup после функциональных исправлений. Сохранить правила общего профиля и адаптировать только Finder к расширению. Разобрать 32 сообщения PHPStan, не подавлять все baseline/ignore. Учесть контекст layouts, magic FormField properties, динамический TASKS_MAP и неточный return DatabaseInterface::quote.

## Критерии приёмки

- [ ] Стиль проверяется воспроизводимой конфигурацией с осознанной политикой для layouts и _JEXEC guards.
- [ ] PHPStan/PhpStorm findings разделены на реальные ошибки и документированные ограничения framework.
- [ ] Не добавлены readonly, promotion, typed constants, union types или иной PHP 8+ синтаксис.
- [ ] PHP 7.4 lint и 540 native checks проходят; семантика не меняется; не удаляются методы, вызываемые через TASKS_MAP.

## Доказательства

- [phpcs.stdout](../../reports/2026-10-15-prerelease-evidence/phpcs.stdout)
- [fixer.stdout](../../reports/2026-10-15-prerelease-evidence/fixer.stdout)
- [fixer-scope.php](../../reports/2026-10-15-prerelease-evidence/fixer-scope.php)
- [phpstan.stdout](../../reports/2026-10-15-prerelease-evidence/phpstan.stdout)
- [phpstorm.json](../../reports/2026-10-15-prerelease-evidence/phpstorm.json)

## Ограничения

Сохранить Joomla 4.2.7+ и PHP 7.4. Не запускать реальные запросы CDEK, менять стенд, выпускать ZIP/релиз или исправлять соседние задачи без отдельного scope. Этот файл — проектная задача; native PF ledger отсутствует, проект работает через .webtolk.


## Обновление после форматирования

Joomla/PSR-12 выполнен: PHPCS 0 errors/warnings, CS Fixer dry-run без изменений, PHP 7.4 lint 40/40. Imports/отступы/braces/EOL исправлены. PHPStan сохраняет прежние 32 сообщения; оставшийся type/PHPDoc cleanup отложен оператором. [Текущая проверка](../../reports/2026-10-15-style-package-verification-checklist.md).
