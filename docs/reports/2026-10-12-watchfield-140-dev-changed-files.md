# Изменённые файлы 1.4.0-dev

- `src/Fields/Watchfield.php`: разрешение зависимостей Joomla Form и декларативный контракт источников данных.
- `src/Fields/DeliverypointsField.php`, `src/Service/DeliverypointsService.php`, `plg_system_wtcdek/src/Extension/Wtcdek.php`: нейтральное поле ПВЗ, XML-фильтры, FIAS и собственный com_ajax handler с CSRF.
- `src/Fields/TariffinfoField.php`, `layouts/fields/tariffinfo.php`: сохранены публичные XML type/alias и template layout; каталог в Script Options, inline JS удалён.
- `src/WebAsset/AssetItem/WatchfieldAssetItem.php`, `media/joomla.assets.json`, три `media/js/wtcdek-*.js`: общий scanner/provider и отдельные renderers, языковые константы при attach.
- RU/EN INI: сообщения состояния и ошибок.
- Package/library/system/task manifests: версия `1.4.0-dev`.
- `.docs/fields/*.md`, `.docs/index.md`, `.docs/entities/deliverypoints.md`, README: описания полей, XML, провайдеров и миграции после E2E.
- `.webtolk/build/package.config.json`: точная dev-версия, исключены docs; Phing target `3. Package release` пакует явно заданный prerelease. Стандартный `5. Package dev` автоматически увеличивает patch и дал бы неверную 1.4.1-dev.
- Ignored `.webtolk/tests`, `.webtolk/tmp`, контекст и логи: воспроизводимые контракты/E2E, результаты, backups и closeout.

Ошибочные `WtsmcdektariffinfoField`, `WtsmcdekorderstatusField` и старый donor JS удалены из рабочего дерева ранее в рамках этого запроса; они никогда не входили в публичный пакет 1.3.2. `TarifflistField` и исходные widget-ассеты сохранены. Git index не перезаписывался, commit/push новой версии не выполнялся.
