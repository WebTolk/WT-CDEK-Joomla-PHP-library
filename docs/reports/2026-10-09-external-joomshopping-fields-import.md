# ВНЕШНЕЕ ВМЕШАТЕЛЬСТВО: поля из JoomShopping

Это изменения, внесённые **агентом другого проекта**, JoomShopping-WT-CDEK-shipping-method, 2026-10-09 по прямому поручению оператора. Агент библиотеки их ещё не принимал. Это импорт проверенного прототипа, а не завершённая интеграция или релиз библиотеки.

Import id: external-joomshopping-fields-20261009. Точный состав, donor/target пути и SHA256: [copy manifest](2026-10-09-external-fields-copy-manifest.json). [Граница работы](../briefs/2026-10-09-external-fields-import-scope.md), [поручение](../briefs/2026-10-09-external-fields-import-brief.md), [проверка импорта](2026-10-09-external-fields-import-verification.md).

## Что скопировано и в каком состоянии

В lib_webtolk_wtcdek/src/Fields добавлены Watchfield.php, WtsmcdekshipmentpointField.php, WtsmcdektariffinfoField.php и WtsmcdekorderstatusField.php. В lib_webtolk_wtcdek/media/js добавлены wt_sm_cdek_watchfield.js, wt_sm_cdek_origin_point.js и wt_sm_cdek_tariff_info.js. Существующие TariffinfoField.php, TarifflistField.php и библиотечные ресурсы не заменялись. Версии/manifest/packager config/ZIP/site не менялись.

Файлы скопированы побайтно, поэтому сохраняют namespace Webtolk\Jshopping\SmWtcdek\Fields, type wtsmcdek..., product docblock1.6.0, WAM URL components/com_jshopping/js и endpoint plugin=wtjshopcdekorigin&group=jshoppingadmin. Библиотечный PSR-4 Webtolk\Cdekapi их сейчас не обнаружит. Нельзя трактовать факт наличия файлов в src/Fields как готовую библиотечную интеграцию. Original name/namespace/docblock служат provenance и подлежат осознанной адаптации.

Watchfield не делает HTTP: PHP разрешает actual Form id/name/value, JS следит за input/change, дедуплицирует источники, имеет disconnect. Поле тарифа использует данные/layout существующего TariffinfoField без его прежнего private inline watcher. Поле ПВЗ делает запрос через существующий защищённый admin endpoint исходного проекта. Сам endpoint/OriginPickupPoints сервис сегодня не копировались. Поле статусов зависит от JoomShopping JSFactory/bootstrap: решение оставить его в JoomShopping или сделать опциональным adapter остаётся агенту библиотеки; универсальная библиотека не должна получать обязательную зависимость от com_jshopping.

## Обязательный контракт

ПВЗ: watchfield='{"zip":"city_from_zip","city":"city_from"}' — named карта parameter => имя Joomla Form field; допустимы один или оба ключа. Либо XML zip="410000" city="Саратов". Наличие любого literal атрибута zip/city, включая явно пустой, полностью отключает watchfield. Недостающий второй literal остаётся пустым; режимы не смешивать. Ошибочная карта при literals игнорируется. Без literals неправильная/отсутствующая карта показывает локализованную ошибку.

Тариф: сохранить watchfield="tariff_code" и alias tariff_field, добавить named map watchfield='{"tariff_code":"tariff_code"}'. Оба поля должны пользоваться одним generic watcher/resolver: оператор планирует дальнейший перенос механизма в Joomla core. Не зашивать jform/params/sm_params; id/name получают через owning Form, сохраняют группы и строковые leading-zero индексы.

## Задачи для релиза завтра, 2026-10-10

1. При старте прочитать эту запись и сверить SHA256 с copy manifest. Отметить принятие **внешнего** импорта в собственной истории, открыть отдельную библиотечную задачу. Текущий flow root .webtolk; .pf пока отсутствует. Оператор называет процесс Process Forge, но onboarding/миграция не выполнялись этой передачей.
2. Принять библиотечные namespace, class/file/type имена с бренд+продукт+тип. Перенести generic Watchfield в согласованный reusable слой. Сохранить публичную совместимость существующих TarifflistField/TariffinfoField и XML scalar/alias; выбрать внедрение общего watcher в существующий TariffinfoField вместо бессрочного shipping-specific subclass.
3. Настроить library media paths/WAM dependencies и зарегистрировать новые assets согласно действующему механизму библиотеки. Перенести SM-prefixed языковые сообщения в библиотечные RU/EN constants, проверить layout и несколько одновременно отрисованных полей.
4. Спроектировать библиотечный read-only endpoint/service для ПВЗ или явный provider adapter: текущее поле связано с JoomShopping admin plugin. Определить admin/frontend use cases, ACL/CSRF и получение данных город/индекс; не подменять старый endpoint без проверки. Решить судьбу JoomShopping-only orderstatus поля до упаковки.
5. Выполнить собственные runtime проверки **библиотечной** реализации: namespace/discovery и Linux case, произвольный control и groups, scalar/map, literals/empty literals/priority, missing sources, несколько полей, preserved selection, race/error, text-only labels, тарифная совместимость. Перепроверить Joomla/PHP minimums пакета: imported resolver использует array_is_list (PHP8.1+), а описание старого пакета заявляет Joomla4.2.7+. Donor проверялся на Joomla5/6, это не приёмка Joomla4.
6. На joomla.local согласованно заменить JoomShopping prototypes на поля выпущенной библиотеки, обновить XML prefixes/types/assets и проверить реальные settings/tariff формы. До такой миграции нынешний JoomShopping prototype остаётся работоспособным; удалять его раньше нельзя. Решить upgrade cleanup и отсутствие duplicate watcher/listeners, сохранить исходные настройки, не создавать shipment ради проверки полей.
7. Выбрать релизную версию и выполнить configured Phing profile. Источник релизной версии — .webtolk/build/package.config.json, сейчас1.3.2; исходные manifests/docblocks1.3.1 являются прежней практикой stamping packager. Сверить синхронизацию всех версий в готовом ZIP, dependencies, language/What's new, update/install, состав assets/layouts. Защитить packager от включения report JSON и прочих incoming artifacts из docs, поскольку *.md exclude не исключает *.json. Не менять версию произвольно только по donor docblock1.6.0.
8. Зафиксировать release notes, migration notes, package SHA256, native install/browser results и handoff библиотеки в её истории. Публикация релиза — действие оператора; сегодняшний импорт её не выполнял и не подтверждал readiness.

## Переданные доказательства и пределы

Результаты исходного проекта от 2026-10-09 сохранены рядом в [source-evidence](2026-10-09-external-fields-source-evidence). На donor Joomla5.4.7/6.1.3 прошло61 offline check каждой версии, 10 native interaction assertions и11 browser fixtures; финальный native lookup Саратова28 ПВЗ, tariff136/139/reload, settings unchanged и probes removed. Runtime stand joomla.local, JoomShopping5.9.3, installed WT CDEK1.3.2. Это проверка donor интеграции, не импортированных библиотечных namespace/assets/endpoint. Приватные credentials/cookies/CSRF/storage state не переносились.

Original audit S01/S02 другого проекта остаётся отдельной operator-deferred задачей; перенос полей не возобновляет его. История прежних выпусков библиотеки не переписывается. Import completed, library adoption/release pending.
