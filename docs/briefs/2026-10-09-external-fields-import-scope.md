# Scope

## In Scope

Source-exact копии четырёх PHP файлов в lib_webtolk_wtcdek/src/Fields и трёх JS в lib_webtolk_wtcdek/media/js. Документы происхождения и завтрашних задач в docs/reports, запись в действующем .webtolk/context/project-context.yaml и append-only logs.

## Out Of Scope

Библиотечная адаптация namespace/type, подключение ресурсов и endpoint, замена существующего TariffinfoField, изменение manifests/версий/packager config, сборка, установка, внешние запросы, git commit/push. JoomShopping source/site prototype не меняется.

## Affected Areas

Новые WtsmcdekshipmentpointField.php, WtsmcdektariffinfoField.php, WtsmcdekorderstatusField.php, Watchfield.php; wt_sm_cdek_origin_point.js, wt_sm_cdek_tariff_info.js, wt_sm_cdek_watchfield.js; история и контекст библиотеки.

## Non-Goals

Не объявлять библиотеку готовой к релизу после копирования. Не мигрировать .webtolk в .pf и не продолжать предыдущий выпуск1.3.2 как будто импорт был частью его работы.

## Risk Boundaries

Прототипы сохраняют namespace Webtolk\Jshopping\SmWtcdek\Fields, пути components/com_jshopping/js и JoomShopping admin AJAX endpoint. До адаптации они не являются discoverable библиотечными полями. Manifest уже включает src/media/js целиком: автоматическая упаковка включит новые файлы, поэтому перед релизом агент обязан принять/адаптировать или исключить их. Существующие поля/ресурсы не перезаписываются.

## Required Artifacts

Этот scope, brief, внешний handoff, copy manifest с SHA256, verification report, ссылки на source evidence; task/agent/verification logs, telemetry и context external_interventions.

## Exit Criteria

Копии и syntax checks passed, прежние исходники/пакеты сохранены, handoff обнаруживается через context/logs. Import completed; library integration/release pending operator's next task.
