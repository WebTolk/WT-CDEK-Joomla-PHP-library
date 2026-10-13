# Реализованный план 1.4.0-dev

Основание и инварианты: [scope](../briefs/2026-10-12-watchfield-140-dev-scope.md). Использована текущая legacy `.webtolk`; `.pf` отсутствует, миграция flow не выполнялась.

1. Исследован showon и native repeatable subform Joomla 4/5/6. Приняты DOM scanner, owning Form, full input names, registry origin→consumers, joomla:updated и rebasing rowX.
2. PHP экспортирует конфигурацию на контейнере. Общий WebAsset выполняет snapshot, path resolution, Script Options/AJAX, CSRF, debounce, generation и cleanup. Renderer-ассеты получают всплывающие lifecycle events и выводят данные.
3. WatchfieldAssetItem при attach экспортирует языковые ключи. Поля подключаются useScript; каталог тарифов добавлен addScriptOptions. Inline executable JS удалён.
4. Выполнены offline native core/event проверки, staged Phing package, native install и Chrome CDP E2E на joomla.local, исправлены находки, повторена приёмка финального артефакта.
5. После успешных тестов подготовлена документация. Сохранены release notes, результаты и hashes, обновлён контекст, удалена временная localhost fixture. Установленная dev-версия оставлена на стенде.
