# Test Plan

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Objectives

Сохранить регрессионную доказательность AJAX/fields и план ручной проверки финального пакета.

## Test Scope

Native form/service/endpoint, cookie source separation, action/event/client/CSRF, archive metadata/syntax; installed UI normal paths отдельно.

## Environments

Joomla 4.4.14 shared core, 5.4.7 shared core, 6.1.3 joomla.local; CLI без site config/DB/provider.

## Checks To Run

Уже выполнены fixtures deliverypoints.php/ajax-cookie.php/ajax-routing.php на трёх cores, затем cookie/routing на архивном handler. Завтра install 1.4.0 и actual field/widget UI.

## Browser Or Runtime Checks

Site/admin AJAX по unified URL; изменение city/zip, empty address, point selection и tariff selection; unknown action/invalid token; cookie consent enabled. Затем targeted F1–F5 repro.

## Exit Gate

Narrow regressions passed; manual/BC gates остаются pending, публичный release закрывается только после их принятия.

## Toolchain Contract References

Платформа Joomla 4/5/6; toolchain PHP/Node и phing via configured toolchain. PhpStorm применял кодовые правки/inspections, shell запускал native fixtures и стандартный packager. PHPUnit/phpstan/php-cs-fixer не запускались и не выдаются за пройденные gates.

## Logical Tools Used

php/native fixture, node syntax, phing, browser Playwright (историческая проверка).

## Fallback Used

Да: shell/installed Playwright SDK в предыдущей сессии.

## Fallback Reason

MCP browser runtime не разделял активную сессию; пользователь явно выбрал Playwright. Raw UI fixture несовместим, свидетельство не засчитывается.
