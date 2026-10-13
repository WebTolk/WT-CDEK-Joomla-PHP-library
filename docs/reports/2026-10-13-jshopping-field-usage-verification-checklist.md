# JoomShopping fields: verification checklist

## Checks Run / Results

- params.xml разобран XML parser: passed; библиотечные атрибуты проверены.
- configform.php PHP lint: passed.
- PhpStorm ERROR inspections обоих файлов: 0 проблем; без timeout.
- Прочитаны установленные Deliverypoints/Tarifflist/Tariffinfo, Helper, renderer/layout и native Joomla CSRF/Form/Document контракты.
- Offline probe с настоящим Form и исходным params.xml: оба control params/sm_params выбирают установленные библиотечные классы, а orderstatus — JoomShopping класс.
- Сформированы реальные data-watchfield имена: params[city_from]/params[city_from_zip], sm_params[city_from]/sm_params[city_from_zip]; zip преобразован в postal_code.
- Фильтры нормализованы в type=PVZ/is_reception="true"/is_handout=""; FancySelect и сохранённый fixture-код SAVED присутствуют в HTML.
- Tariffinfo разрешает params[tariff_code] и sm_params[tariff_code]; Script Options и список тарифа сформированы по синтетическому каталогу.
- Ассеты потребителей, FancySelect и Choices подключены рендерерами; общий Watchfield включается через зависимости registry.
- Evidence: probe.php/result.json в 2026-10-13-jshopping-field-usage-evidence/.

## Checks Skipped

Реальный administrator browser path, AJAX/CDEK, БД/сохранение параметров, установленный plugin enabled и значения реального каталога не проверялись. Чтение формы в браузере могло бы запустить provider; review выполнен без таких вызовов.

## Open Risks / Ready for Delivery

Использование полей в запрошенных файлах соответствует текущему контракту; правки не нужны. Это source/native-offline verdict, не подтверждение live provider или save lifecycle. F1–F5 библиотеки остаются отдельным backlog.

## Tools / Probe Boundaries

Serena не exposed; чтение/inspections через PhpStorm MCP, reference/CLI checks через reviewed shell. Первые попытки probe выявили ограничения тестового Document (getScriptOptions, numeric-key merge) и ошибочное boolean-ожидание проверочного кода; fixture адаптирован к core, ожидаемый фильтр исправлен на строку. Изменений продукта при этом не выполнялось.
