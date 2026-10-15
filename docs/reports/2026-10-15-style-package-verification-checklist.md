# Joomla style / package verification

## Checks Run / Results

| Проверка | Результат |
| --- | --- |
| PHP CS Fixer 3.95.1, shared Joomla rules | Применён; итоговый dry-run exit 0, files=[] |
| PHPCBF 3.13.5, shared Joomla PSR-12 | Применён; layout проверен тем же ruleset |
| PHPCS, все 40 PHP | 0 errors, 0 warnings, 0 fixable |
| PHP 7.4.33 / 8.0 / 8.1 / 8.3.30 | 40/40 lint на каждой версии |
| Normalized AST | 40/40 equal; boundaries описаны в changed-files |
| Native Joomla 4.4.14 / PHP 7.4 | 84 + 20 + 46 = 150 passed |
| Native Joomla 5.4.7 / PHP 8.3 | 85 + 20 + 90 = 195 passed |
| Native Joomla 6.1.3 / PHP 8.3 | 85 + 20 + 90 = 195 passed |
| Installer provider/interface/application smoke | Joomla 4/5/6 passed; без install hooks |
| PhpStorm ERROR inspections | 39 изменённых файлов, ERROR 0, timeout 0 |
| PhpStorm build API | isSuccess; limited diagnostics, не заменяет CLI lint |
| PHPStan level 5 | 32 прежних сообщения, identifier counts до/после одинаковы |
| Deferred PHPUnit regressions | 3 tests / 4 assertions / 3 failures, 0 errors; следующий релиз |
| Phing target 1. Info | Версия 1.4.0, .webtolk/build/package.config.json |
| Phing target 3. Package release | BUILD FINISHED, exit 0 |
| ZIP CRC/entries/exclusions | 66 уникальных файлов, точный набор product snapshot |
| ZIP PHP | 40/40 lint PHP 7.4; executable tokens равны исходникам |
| ZIP JS | Пять JS: тела равны source после release-header normalization, syntax passed |
| ZIP metadata | Четыре manifests 1.4.0; XML/JSON parsed; deploy tokens отсутствуют |
| Source-preserving packaging | Все 66 source hashes до/после сборки совпадают |
| Старый ZIP | Backup SHA256 2e17b506ffa953f1f6d86d38175116bf5f3b2b9b2f19d3cd2c7dc0c16e2c2982 |
| Git diff --check | passed; commit/index/push не выполнялись |

Команды/exit/stdout/stderr: [evidence](2026-10-15-style-package-evidence/package-audit.json). Предыдущий failed audit срез не перезаписывался. Отдельный script сравнения AST проверяет нормализацию форматирования/импортов, а не обещает формальную эквивалентность произвольных PHP-программ.

## Checks Skipped

Реальные CDEK/HTTP/БД операции, установка/удаление и новая браузерная приёмка не выполнялись. Оператор ранее проверил ordinary Watchfield; JS не менялся, template изменён только по whitespace. Отдельного runtime Joomla 4.2.7 нет; доступный core 4.x — 4.4.14. Vendor bundle не подвергался полному аудиту поставщика.

## Open Risks / Ready For Delivery

Запрошенная локальная package delivery выполнена. Функциональные задачи отложены оператором на следующий релиз и этим форматированием не закрыты. PHPStan не объявлен clean. Установка, публикация и live/provider acceptance не заявляются.
