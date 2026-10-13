# Сохранённое состояние и дальнейшая работа

2026-10-09: поля готовы и проверены на joomla.local. Сначала названный rcsh.local отменён оператором; этот сайт не менялся. В библиотеку поля сегодня не переносились. Оператор планирует библиотечный релиз завтра; начать только отдельную задачу после нового поручения.

Читать по порядку: scope-watchfield-addendum.md, implementation.md, assurance.md, delivery.md, этот handoff, evidence-index.json и generated PF run summary/handoff. Original scope-plan.md сохранён как ранняя версия; исполняемый JS после дальнейшего watchfield уточнения намеренно изменён. Последняя граница работы сегодня — поля.

## Контракт для переноса

ПВЗ принимает named JSON watchfield карту zip/city с именами Joomla Form fields. Не зашивать jform/params/id. Либо XML literals zip="410000" city="Саратов". Наличие любого literal attribute, включая пустое значение, полностью игнорирует map, даже ошибочную. Режимы не смешивать. Поддержаны city-only/zip-only XML и map. Поле информации о тарифе принимает scalar watchfield и named map tariff_code; оба поля используют один resolver/watcher. Группы разрешаются через Form.

## Готовый результат

PHP classes в components/com_jshopping/src/Fields; namespace Webtolk\Jshopping\SmWtcdek\Fields. Подключаются в configform.php/shippingpriceform.php. Штатное поле ПВЗ params_shipment_point, реальный control params; tariff info sm_params_tariff_info_tariffinfo, control sm_params. Native admin routes проверены: shippingextprice task=edit id=4 и shippingsprices task=edit sh_pr_method_id=3. На стенде остаётся рабочая точечная установка новых файлов без probe XML и без сохранения настроек.

Repo библиотеки WT-CDEK-Joomla-PHP-library clean, source версия1.3.1; installed local library1.3.2 не редактирована. Текущая версия shipping extension1.6.0 сохранена. При переносе учесть различие версий, native library assets/namespace/layout и принадлежность endpoint JoomShopping admin plugin. Реальный package install и Linux verification сегодня не выполнены, поэтому их надо планировать отдельно в scope релиза.

## Evidence и восстановление

Приватные diagnostics/harness/screenshots/baselines/backups находятся в .pf/tmp/cdek-fields-assets-20261009. JSON diagnostics перечислены в assurance.md. source-snapshot содержит13 source files; final-validation.json содержит их SHA256 и три удалённых path, installed-library-hashes.json фиксирует текущее установленное состояние библиотеки. evidence-index.json перечисляет артефакты с hash; telemetry — .pf/runtime/telemetry/20261009-cdek-fields-complete.json. Не переносить приватные auth/storage/cookie файлы в продукт/релиз.

Откат только по site-baseline.private.json и site-backup, без удаления общих директорий. Резервные копии не перезаписывать. Browser fixtures сняты, headless test session завершён после сохранения доказательств. Поля не требуют автоматического monitor/retry. Security audit S01/S02 остаётся operator-deferred, его старый continuation waiting не возобновлять как часть библиотечного релиза автоматически.

## Evolution capture

Вывод: JoomShopping namespace не подразумевает src; class discovery надо проверять настоящим FormHelper и manifest реальным FileAdapter. Для переносимого watcher источники следует разрешать через owning Form, передавать actual id/name и держать сетевое действие вне общего watcher. Explicit empty XML attrs требуют отдельной проверки приоритета. Fancy Select native select скрыт, тестировать attached state и widget UI.

updated-rules-or-extensions: not_applicable. instruction-update-proposal: not_applicable. knowledge-update-proposal: not_applicable. reason: задача ограничена полями; глобальные инструкции/знания/память не обновляются. evidence: этот handoff фиксирует необходимые знания для дальнейшей отдельной работы. evolution-captured: passed, выводы сохранены без изменения реестров.
