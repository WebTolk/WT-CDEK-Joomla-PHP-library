# Поля WT CDEK: реализация 2026-10-09

Последняя граница оператора: сегодня поля и проверка на joomla.local; перенос и релиз библиотеки — отдельная работа завтра. Этот документ вместе со scope-watchfield-addendum.md supersedes исходные ограничения source-only/no-site и прежнее намерение немедленного переноса. Прежние документы сохранены как история.

## Changed files и change summary

Исходники: jshopping_sm_wtcdek. origin-point.js перенесён в components/com_jshopping/js/wt_sm_cdek_origin_point.js, подключение обновлено через WebAssetManager. Общий wt_sm_cdek_watchfield.js используется полями ПВЗ и тарифа; wt_sm_cdek_tariff_info.js заменяет отдельный inline watcher только у нового прототипа. Во всех новых PHP/JS применён файловый блок по разрешённому глобальному шаблону php-class-level-docblock; адаптация шаблона к файлу соответствует прямому поручению оператора.

components/com_jshopping/src/Fields содержит Watchfield.php, WtsmcdekshipmentpointField.php, WtsmcdekorderstatusField.php и WtsmcdektariffinfoField.php. Первые два прежних глобальных field-класса удалены из shippings/sm_wtcdek/fields. Namespace Webtolk\Jshopping\SmWtcdek\Fields регистрируется штатным JLoader::registerNamespace в configform.php и shippingpriceform.php; params.xml задаёт addfieldprefix и новые type. JoomShopping 5.9.3 не предоставляет автоматический PSR-4 для выбранного src/Fields, поэтому namespace ядра не используется для собственных классов. Статусы используют namespaced JSFactory.

Манифест jshopping_sm_wtcdek.xml перечисляет конкретные language, CSS, JS и PHP файлы и только собственные папки sm_wtcdek, sm_wtcdek_form, smwtcdek. Общие components/administrator/src/Fields не объявлены папками расширения. Native FileAdapter подтверждает полное покрытие 27 файлов payload без дублирования целей.

В двух административных language-файлах добавлены сообщения неправильной конфигурации watchfield и пустой карточки тарифа. Остальной продукт, plugin/service endpoints, библиотека и версии не менялись.

## Контракт и архитектура

Watchfield::resolve разрешает реальные id/name/value через owning Joomla Form, включая текущую fields group и dotted имя другой группы. Одиночное имя совместимо с прежним тарифным полем, JSON-объект задаёт parameter => имя поля. Общий JS подключает change/input у найденных элементов своего form, объединяет повторяющиеся зависимости и поддерживает disconnect; сетевые действия принадлежат полю.

ПВЗ: watchfield='{"zip":"city_from_zip","city":"city_from"}'. Допустимы один или оба ключа. XML zip="410000" city="Саратов" выбирают альтернативный literal mode. Наличие любого атрибута zip/city, даже пустого, полностью отключает watchfield. Отсутствующий второй литерал остаётся пустым и не берётся из карты. Ошибочные карты показывают локализованную ошибку настройки поля. Индекс в механизме сохраняется строкой.

Информация о тарифе: watchfield="tariff_code" либо watchfield='{"tariff_code":"tariff_code"}'. WtsmcdektariffinfoField наследует catalogue/layout установленной библиотеки, используя общий resolver/watcher вместо прежнего inline JS. ПВЗ по-прежнему загружаются существующим защищённым admin endpoint через определение единственного города и фильтр reception PVZ; поведение провайдера вне границы изменений.

## Локальная установка и откат

На joomla.local точечно скопированы 13 файлов; старые 3 файла удалены по конкретным путям. Оригиналы, включая installed manifest, сохранены в .pf/tmp/cdek-fields-assets-20261009/site-backup; исходное наличие и SHA256 — site-baseline.private.json. XML временно дополнялся 11 probe-полями; после теста восстановлен из исходников. Окончательная installed копия всех 13 файлов совпадает с source побайтно, старые 3 отсутствуют. Прототип остаётся установленным для оператора. Настройки формы не сохранялись.

Изменённые файлы и SHA256 сохранены в final-validation.json; source-snapshot содержит полный набор 13 новых/изменённых файлов. Резервные копии нельзя перезаписывать при следующем staging. Для отката восстанавливать только перечисленные original paths из backup; новые файлы удалять только по paths с existed=false. Никакие общие папки не удалять.

Scope gate: passed. Библиотека source/installed не редактировалась; source git clean. Package, commit, push, заказные/платёжные/CDEK mutation операции не выполнялись. Старый security audit остаётся operator-deferred.
