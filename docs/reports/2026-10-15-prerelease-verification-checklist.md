# Предрелизный checklist, 15.10.2026

## Checks Run / Results

| Проверка | Результат | Граница |
| --- | --- | --- |
| PhpStorm inspections, 40 PHP | ERROR 0; WARNING 15; WEAK_WARNING 433; timeout 0 | Сырой срез аудита; дубли/SQL-конфигурация отделены от дефектов |
| Повторная инспекция Cdek.php после cleanup | 52 → 7 сообщений | Сохранены документальные/fixture-дубли |
| PHP 8.3.30 lint до правки | 40/40 passed | Не доказывает PHP 7.4 |
| PHP 7.4.33 и 8.0 lint до правки | 39/40 passed | Единственный отказ — installer; тот же синтаксис есть в main |
| PHP 7.4/8.0/8.1/8.3 lint после правки | По 40/40 passed | Отдельные post-fix JSON; исходные failed results сохранены |
| Installer provider smoke Joomla 4.4.14 / PHP 7.4 | passed | Реальный provider/interface/DI; install hooks и файловые операции не выполнялись |
| Installer provider smoke Joomla 5.4.7 / PHP 8.3 | passed | Та же граница |
| Installer provider smoke Joomla 6.1.3 / PHP 8.3 | passed | Та же граница |
| Native Joomla 4.4.14 / PHP 7.4 | 84 + 20 + 46 = 150 passed | Поле/сервис, cookie exclusion, AJAX/site/admin/CSRF; CDEK stub, DB connect запрещён |
| Native Joomla 5.4.7 / PHP 8.3 | 85 + 20 + 90 = 195 passed | Аналогично |
| Native Joomla 6.1.3 / PHP 8.3 | 85 + 20 + 90 = 195 passed | Аналогично |
| Три собственных JS, node --check | 3/3 passed | Syntax only |
| PHPCS 3.13.5, shared Joomla PSR-12 | failed: 4915 style errors / 60 warnings; 39 файлов | До узкой правки installer; не runtime verdict |
| PHP CS Fixer 3.95.1 dry-run | failed style gate: 38 файлов; exit 8 | Shared rules; Finder адаптирован только для scope; исправления не применялись |
| PHPStan 2.1.47 level 5 / PHP target 8.3 | 32 file messages, 0 global errors | Native Joomla symbols/bootstrap; реальные ошибки и context noise разделены в findings |
| PHPUnit 9.6.34 адресные regressions | 3 tests, 4 assertions, **3 failures**, 0 errors | Подтверждают REL-002/003/004; memory cache/logger/DB; не выдаются за passed |
| XML manifests/form | 5 XML parsed | Синтаксис метаданных; не lifecycle acceptance |
| WebAsset JSON | parsed | Синтаксис |
| ZIP 1.3.2 / 1.4.0-dev / 1.4.0 | SHA256 + CRC checked | Не пересобирались; финальный 1.4.0 совпадает с прежним hash |
| Product SHA256 before/after | Менялся только lib_webtolk_wtcdek/script.php | Остальные 39 PHP неизменны; Git diff дополнительно проверен |

Команды, exit codes и stdout/stderr находятся в [evidence](2026-10-15-prerelease-evidence/summary.json). Проверяемые исходники и reports привязаны к dev HEAD 38e43d8 до локальной правки.

## Проверки исправленного файла

PhpStorm после правки installer: ERROR 0, WARNING 1 (ранее существовавшее неиспользуемое private application), WEAK_WARNING 10. Build API вернул isSuccess, но предупредил об ограниченной диагностике PHP-проекта; это не заменяет реальный CLI lint. Новая архитектура/публичный API не вводились; [scope и план](2026-10-15-php74-installer-scope.md).

Адресный PHPStan после правки installer: одно сообщение property.onlyWritten о том же ранее существовавшем свойстве app; новых ошибок типов/сигнатур нет. Full style/PHPStan результаты выше сохранены как исходный аудиторский срез, а не объявлены пройденными gates.

## Harness Corrections

- Исходный shared CS Fixer config пытается читать config/administrator, которого нет. Сохранён failed probe; локальная audit-копия меняет только Finder, rules array сохранён.
- Первый deliverypoints fixture на Joomla 6 не имел HTTP_HOST для Uri::base в CLI. После задания localhost/SCRIPT_NAME/PHP_SELF в launcher unchanged fixture прошёл 85 checks.
- В первом memory scheduler probe отсутствовала регистрация DatabaseInterface в тестовом контейнере; добавлена mock-регистрация. Окончательный результат — product TypeError после TRUNCATE, а не ошибка DI.
- Первый provider smoke регистрировал application как non-shared, и Joomla DI клонировал его. В launcher исправлено shared=true; все три core прошли. Продукт не менялся из-за этих harness corrections.
- В проекте не было PHPUnit config/suite; configuration probe выдал usage, что отмечено как отсутствие configured suite. Новые три audit regressions запускаются явным путём к ReleaseContractsTest.php.

## Cleanup

По разрешению оператора **перенесены, не уничтожены** игнорируемые временные деревья:

- .webtolk/tmp целиком: snapshots, browser profiles, archive extracts, backups;
- .packages/.stage;
- .packages/install_6a2842e51e4f6;
- .packages/install_6a28433376bd7.

Архив: `E:/tmp/wtcdek-prerelease-cleanup-20261015/`, с сохранением относительных путей. Исходные абсолютные source/target записаны в [cleanup.json](2026-10-15-prerelease-evidence/cleanup.json). Ни одного tracked-файла в этих целях не было; source/target были проверены до Move-Item. Старые ссылки на .webtolk/tmp нужно разрешать через этот mapping; исторические отчёты не переписывались. Версионные evidence, .webtolk/tests, context/continuations и ZIP-пакеты сохранены. Сгенерированный PHPStan cache также вынесен в архив, конфигурация указывает на внешний qa-cache.

## Checks Skipped / Open Risks

- Новая browser-проверка не выполнялась. Оператор сообщил, что Watchfield работает; F1/F2/F5 остаются отдельными edge/lifecycle задачами со старым evidence.
- Полная установка/update/uninstall на реальном стенде, отказ filesystem и сохранение формы с dynamic validation не выполнялись.
- Реальных HTTP/CDEK/payment/order вызовов и реальной БД не было.
- Joomla 4.2.7 отдельным runtime не проверялась; доступный Joomla 4 core — 4.4.14.
- Статический анализ проведён с native Joomla 6 symbols; это не отдельный исчерпывающий PHPStan-прогон всех Joomla 4 API.
- Аудит не доказывает правильность всех возможных провайдерских payload/операций или стороннего widget bundle.
- Пересборка ZIP, commit/push и публикация не выполнялись. Доступный ZIP 1.4.0 ещё содержит старый installer.

## Ready for Delivery

**Нет, для публичного релиза остаются открытые дефекты.** Узкая правка PHP 7.4 готова в исходниках, дополнительные дефекты оформлены [13 отдельными задачами](../tasks/2026-10-15-prerelease/index.md): одна реализована, 12 открыты. Это project task records; .pf ledger отсутствует и не имитировался.
