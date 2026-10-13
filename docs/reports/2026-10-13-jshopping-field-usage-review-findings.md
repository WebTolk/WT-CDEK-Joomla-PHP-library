# JoomShopping: использование библиотечных полей

## Scope / Contracts

Read-only review по запросу оператора: E:/OSPanel/home/joomla.local/public/components/com_jshopping/shippings/sm_wtcdek/{params.xml,configform.php}. Для проверки путей вызова дополнительно прочитаны shippingpriceform.php, showConfigForm/showShippingPriceForm, внешний administrator template и установленные классы/ассеты библиотеки. Исправления, установка и provider calls не входят в scope.

## Findings / Severity / Evidence

Ошибок использования библиотечных полей в двух запрошенных файлах не обнаружено.

- params.xml:15–23: shipment_point использует deliverypoints и библиотечный prefix Webtolk\Cdekapi\Fields. Карта zip→city_from_zip, city→city_from ссылается на существующие текстовые поля. Literal city/zip/fias_guid не заданы, поэтому watchers не отключены. PVZ + is_reception=true соответствует выбору пункта приёма отправления; is_handout не ограничен.
- params.xml:314–322: tarifflist и tariffinfo имеют правильный prefix, а watchfield="tariff_code" связывает карточку с настоящим полем тарифа.
- configform.php:23–31: control=params, loadFile и bind согласованы; JoomShopping prefix зарегистрирован по фактически существующему components/com_jshopping/src/Fields. HTMLHelper::_('form.csrf') добавляет именно csrf.token в Joomla Script Options.
- configform.php:61: renderFieldset basic_setting вызывает getInput установленного DeliverypointsField; само поле регистрирует wtcdek.deliverypoints и зависимость wtcdek.watchfield. Ручной JS/отдельное поле Watchfield не требуются.
- shippingpriceform.php:83/92 рендерит tariff_code/tariff_info под control=sm_params. Поэтому отсутствие тарифного fieldset в глобальном configform.php не является потерей полей.
- administrator/components/com_jshopping/tmpl/shippingext/edit.php:16/61: configform включён внутри adminForm; источники и потребитель принадлежат одной HTML-форме.

## Blocked or Not / Recommended Action

Исправления этих XML/PHP ради подключения полей не требуются. Полная browser/save приёмка не заявляется.

Известные F1–F5 библиотеки не относятся к ошибкам чужого params.xml/configform.php. F1 затрагивает здесь retry ПВЗ после сетевой ошибки; поля не используют validate="options", поэтому известный F4 не активируется этим XML. Вопрос серверной проверки сохранённого кода принадлежит save handler и не закрывается проверкой отображения.

## Evidence / Integrity

params.xml SHA256 2ED6C9C0112CEDA801C2F16175C156820AA5630F85C323CFB5F302B5B848E919.

configform.php SHA256 4E9CE1D33AA98780E91F38A402C35513F6C0265D6197467344A6280FD582B7EF.

Offline probe и результат сохранены в 2026-10-13-jshopping-field-usage-evidence/. Probe запускается из корня библиотеки командой php docs/reports/2026-10-13-jshopping-field-usage-evidence/probe.php; использует существующий offline bootstrap, установленную библиотеку, CDEK stub и запрещённую БД. Fixture Document дополняет FancySelect API и воспроизводит array_replace_recursive из core Document::addScriptOptions; это не browser/provider acceptance.
