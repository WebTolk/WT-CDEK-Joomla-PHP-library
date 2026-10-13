# Review Findings

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Summary

Узкие выполненные fixes/упаковка проверены; general release readiness не объявляется.

## Findings

F1 P2: retry теряет saved selection без смены адреса. F2 P2: programmatic point change не обновляет цепочку. F3 P2/BC: legacy Tariffinfo overrides. F4 P2: validate=options отвергает dynamic values. F5 P3: повторная конфигурация target. Полные воспроизведения — в исходном аудитe.

## Severity Map

P0/P1 в прежнем аудите не найдены; четыре P2 и один P3 остаются открытыми. Cookie/endpoint/CSRF отдельные изменения не закрывают их.

## Regressions Checked

GET/POST routing, nested payload, arbitrary-cookie exclusion, event responses, both clients, auth rejection, field defaults/filters/FIAS, package/source metadata.

## Missing Tests

Manual final install/API acceptance; повторный browser acceptance нового unified endpoint и будущих F fixes.

## Residual Risks

Ручная установка финального 1.4.0 и реальный ответ СДЭК ещё не проверены. Четыре P2 F1–F4 и P3 F5 остаются открытыми; checkpoint dev не является публичным релизом.

## Recommendation

Сохранить dev checkpoint и паузу; завтра manual check, затем F1/F2/F4/F3/F5 в согласованном scope.

## Toolchain Contract References

Платформа Joomla 4/5/6; toolchain PHP/Node и phing via configured toolchain. PhpStorm применял кодовые правки/inspections, shell запускал native fixtures и стандартный packager. PHPUnit/phpstan/php-cs-fixer не запускались и не выдаются за пройденные gates.

## Logical Tools Used

php, node, PhpStorm inspections, phing, git.

## Fallback Used

Да: native CLI.

## Fallback Reason

Настоящие Joomla classes используются offline; provider/DB stubbed. На closeout IDE недоступна.
