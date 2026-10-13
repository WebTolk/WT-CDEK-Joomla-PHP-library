# Продолжение: dev checkpoint 13.10.2026

Оператор: «Коммит и пуш в dev. Потом заполни все артефакты PF. Завтра продолжим». Работа остановлена по его указанию; новый implementation запускать только после возобновления.

## Состояние

- Repo E:/dev/WT-CDEK-Joomla-PHP-library; ветка dev отслеживает origin/dev.
- Product commit d4a137e7b17e6da3185c10d50ad6c07014feffae отправлен и подтверждён ls-remote; main 887e4ff не менялся.
- Полный комплект артефактов docs/closeout/2026-10-13-dev/index.md; итоговый documentation commit добавляется после product push и также отправляется в dev.
- Пакет .packages/WT Cdek library package_1.4.0.zip, 383093 байт, SHA256 2e17b506ffa953f1f6d86d38175116bf5f3b2b9b2f19d3cd2c7dc0c16e2c2982. Сборка не установлена/не опубликована. Public release по историческому отчёту 1.3.2; новых release/tag действий нет.
- Joomla6.1.3 joomla.local пока содержит более раннюю установленную 1.4.0-dev. Сегодняшний browser normal-path evidence относится к ней.

## Финальная архитектура и выполненное

Watchfield — общий WebAsset/Helper, а не FormField. Поля Deliverypoints/Tariffinfo используют generic scanner/transport, специализированные renderers. Для deliverypoints XML literals отключают watchers по наличию атрибута, в том числе пустого; поддержаны FIAS/filters.

Один onAjaxWtcdek: action offices/calculate/deliverypoints. Separate onAjaxWtcdekdeliverypoints и subscription удалены; field POST обращается plugin=wtcdek/action=deliverypoints. Нет site-only guard. GET/POST явно объединяются с POST precedence, cookies исключены. Widget token URL/POST/header проверяется; failed/new-session token не вызывает CDEK. Raw widget JSON и field data[0].options проверены на native events.

Файловые PHP/JS docblocks применены из PF global template, собственные JS добавлены в textExtensions. RU/EN Whats New четыре коротких пользовательских пункта. Версия config/manifest 1.4.0. Локальный build config snapshot в docs/closeout; active config .webtolk/build/package.config.json.

## Проверка

540 assertions по трём Joomla cores и 110 assertions на archived handler. PHP40 lint/tokens, JS3 syntax/body, headers43, manifests4, parsedINI2; IDE ERROR0 до закрытия PhpStorm. Доказательства docs/reports/2026-10-13-ajax-routing-evidence.

Исторический real admin browser: 28 Saratov/424 Moscow, FancySelect, empty address, tariff136->333; 0 pageerror, no save. Не выдавать за acceptance новой установки. Old DOM-fixture VM queueMicrotask failure не product verdict. Полный phpstan/phpunit/style gate не запускался.

## Открытые задачи

F1 P2 retry теряет saved PVZ без смены адреса; F2 P2 chain stale после programmatic change; F3 P2 legacy Tariffinfo overrides; F4 P2 native validate=options отвергает dynamic choices; F5 P3 повторная конфигурация target. Приоритет F1/F2/F4/F3/F5. Исходный findings report 12.10.2026 и доказательства сохранены; этим checkpoint не закрыты.

## Первые действия завтра

1. Прочитать только E:/.agents/AGENTS.md, проверить .pf/AGENTS.md.
2. Сверить HEAD/status и origin/dev, прочитать bundle index/ADR и report unified AJAX.
3. Получить результаты ручной установки пакета оператора. При новой browser-проверке проверить unified URL/action и widget/site/admin/token/cookie paths.
4. Выбрать отдельный scope для F1–F5; не возобновлять fixes автоматически на основании этой заметки.
5. Отдельно определить PF onboarding, если требуется native ledger.

## PF и инструменты

.pf отсутствует. pf_resolve и initialization_status вновь вернули missing .pf/process-forge.yaml. PF-native artifacts/ledger не записаны; fake snapshots не создавались. Фактические context/logs/cursor .webtolk заполнены, все человеческие artifacts versioned в docs/closeout. Применены только глобальные навыки E:/.agents/skills/development-flow-legacy; scripts/orchestrator.py отсутствует.

PhpStorm transport на closeout недоступен; shell fallback записан. Browser закрыт, пароли не сохранены в публичных evidence. .playwright-mcp ignored. Git stage/commit/push сегодня разрешены явно; ранее index не менялся агентом. Shared tools/templates/global memory не изменялись.
