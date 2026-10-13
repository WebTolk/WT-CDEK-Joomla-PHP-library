# Patch

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Patch Id

2026-10-13-unified-ajax-and-package-metadata

## Source Task

Операторские задачи fields/helper/docblocks/WhatsNew/cookie fix/one AJAX/dev push.

## Problem Or Learning

Aggregate request может включать cookie; standalone field endpoint расходится с публичным router; JS deploy headers требуют textExtensions.

## Proposed Reusable Change

На project layer зафиксировать explicit GET/POST, one handler/action contracts, metadata archive audit и exclusions runtime. Global PF/template policy не изменять.

## Target Layer

Project product/config/docs; global layer untouched.

## Files To Update

Изменения уже применены и отправлены product commit. Контракты и ADR в этом комплекте; локальный package config сохранён как reproducibility snapshot.

## Compatibility Considerations

См. migration-notes/public-contracts; F1–F5 остаются open.

## Approval Status

Implementation/commit/push явно разрешены оператором в этой сессии. Это запись выполненной работы, не запрос на глобальный reusable patch.

## Toolchain Contract References

Платформа Joomla 4/5/6; toolchain PHP/Node и phing via configured toolchain. PhpStorm применял кодовые правки/inspections, shell запускал native fixtures и стандартный packager. PHPUnit/phpstan/php-cs-fixer не запускались и не выдаются за пройденные gates.
