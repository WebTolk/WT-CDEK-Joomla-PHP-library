# Browser audit probes

Chrome headless / DevTools Protocol, отдельный профиль и порт 9340. Страница file:// в ignored audit directory загружает неизменённые JS рабочего дерева. Native browser DOM, Option, MutationObserver, AbortController и CustomEvent используются реально; только транспорт fetch подставлен. Никаких сетевых calls к CDEK или изменений joomla.local.

Результат: **четыре воспроизведённых сценария, 0 uncaught JS exceptions**. Отсутствие исключений не означает правильную бизнес-логику.

| Сценарий | Наблюдение |
| --- | --- |
| First request fails → retry scan | SAVED → пусто при том же Old address |
| City input → point invalidation → chained consumer | Native select пустой, snapshot consumer остаётся KNOWN |
| Same target changes config / Script Options updates | Provider остаётся A; новые options не дают событие при прежних values |
| Legacy public layout markup | render-target остаётся пустым |

Программа: [probe HTML](./2026-10-12-fields-reaudit-evidence/browser-probe.html), [CDP runner](./2026-10-12-fields-reaudit-evidence/run-browser.cjs). [Результат](./2026-10-12-fields-reaudit-evidence/browser-result.json), [screenshot](./2026-10-12-fields-reaudit-evidence/browser.png). Оригинальные запускаемые пути и команды приведены в handoff; evidence copies не переопределяют предыдущие native E2E artifacts.
