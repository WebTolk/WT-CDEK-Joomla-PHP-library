# Test Cases

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Executed Cases

Joomla4: field84+cookie20+router46; Joomla5:85+20+90; Joomla6:85+20+90. Archived handler: cookie20+router90. Matrix includes offices/calculate/deliverypoints x site/admin x generic/modern events where present.

## Expected Results

Correct handler/data shape; explicit request data retained; cookies excluded; no client on invalid token/unknown/empty request; native field URL includes correct action.

## Actual Results

Все 650 assertions passed; package audit и code syntax passed. Исторический admin browser: 28/424 points, tariff136->333, no save/pageerror.

## Failures

Cookie leakage reproduced on pre-fix handler; old DOM-fixture VM queueMicrotask diagnostic is an unresolved fixture limitation, не product verdict.

## Skipped Cases

Новый installation/live provider acceptance и final browser; future F fixes; full phpstan/phpunit/php-cs-fixer.

## Evidence Links

[Финальный AJAX и проверки](../../reports/2026-10-13-ajax-routing-verification.md); [открытый аудит F1–F5](../../reports/2026-10-12-fields-reaudit-review-findings.md); [браузерная проверка](../../reports/2026-10-13-jshopping-admin-browser-verification.md); [пакет](../../release/2026-10-13-1.4.0-local-package.md).

## Toolchain Contract References

Платформа Joomla 4/5/6; toolchain PHP/Node и phing via configured toolchain. PhpStorm применял кодовые правки/inspections, shell запускал native fixtures и стандартный packager. PHPUnit/phpstan/php-cs-fixer не запускались и не выдаются за пройденные gates.

## Logical Tools Used

php native fixtures и node syntax; phing package.

## Fallback Used

Да.

## Fallback Reason

Offline CLI обеспечивает реальные core classes без внешнего API/БД.
