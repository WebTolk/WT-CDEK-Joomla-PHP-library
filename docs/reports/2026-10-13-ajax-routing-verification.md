# Единый AJAX-обработчик WT CDEK

## Результат

Системный плагин подписан только на onAjaxWtcdek. Отдельный onAjaxWtcdekdeliverypoints удалён. В onAjaxWtcdek используется switch по action:

| action | Результат |
| --- | --- |
| offices | Исходный список пунктов СДЭК для виджета |
| calculate | Расчёт тарифов для виджета |
| deliverypoints | Подготовленные value/text options для поля Deliverypoints |

Проверки только site-стороны больше нет. Все три действия работают на сайте и в админке. Поле Deliverypoints обращается к plugin=wtcdek, передавая action=deliverypoints в POST. Документация поля и актуальные ссылки в тестах обновлены.

## Совместимость

GET/POST читаются явно; POST имеет приоритет. Cookie не попадают в параметры провайдера. Для offices/calculate сохранён JSON-текст, который com_ajax выдаёт в format=raw. Для deliverypoints сохранён JSON-контракт data[0].options. Результаты добавляются в общий массив Event или через ResultAwareInterface::addResult; это работает с нативными Joomla 4 generic и Joomla 5/6 AjaxEvent.

Ветвь deliverypoints сохраняет POST-only, обязательный posted CSRF, ограничения адреса, нормализацию фильтров/ФИАС, пустой список без создания клиента и локализованные ошибки. Для виджета принимаются явные токены в POST, URL и X-CSRF-Token. При отсутствующем/неверном токене обработчик больше не продолжает вызов СДЭК, включая новую сессию. Неизвестный или отсутствующий action не создаёт клиента. Авторизация входа в админку остаётся на стороне Joomla.

## Проверки

Нативные офлайн fixtures запрещают БД и подменяют CDEK; site configuration не загружается.

| Joomla | Поле/сервис/event | Cookie | Роутинг/оба приложения/CSRF |
| --- | ---: | ---: | ---: |
| 4.4.14 | 84 | 20 | 46 |
| 5.4.7 | 85 | 20 | 90 |
| 6.1.3 | 85 | 20 | 90 |

Итого 540 checks passed. В отдельном процессе с обработчиком из финального ZIP прошли ещё 20 cookie и 90 routing checks. Проверены все action на site/administrator, generic/modern events, raw JSON виджета, JsonResponse поля, POST precedence, cookie-only action, токены в URL/POST/header и отказ без создания клиента в обычной/новой сессии.

PhpStorm ERROR inspections обоих изменённых PHP: 0 проблем. git diff --check passed. Архив: 40 PHP lint и сравнений исполняемых tokens, 3 JS syntax/body checks, 43 заголовка, 4 манифеста версии 1.4.0, оба INI с четырьмя пользовательскими пунктами. Публичный 1.3.2 и dev ZIP неизменны.

Старый DOM-fixture deliverypoints-ui.cjs был запущен и остановился на отсутствии queueMicrotask в его VM. Он не соответствует нынешнему Watchfield и не используется как свидетельство UI-приёмки. Продуктовые JS не менялись. Новая установка и браузерная/реальная API-приёмка не выполнялись; пакет передаётся для ручной проверки. Предыдущие браузерные доказательства dev остаются историческими.

## Артефакты

- `.packages/WT Cdek library package_1.4.0.zip`, 383093 байт, 66 entries.
- SHA256: `2e17b506ffa953f1f6d86d38175116bf5f3b2b9b2f19d3cd2c7dc0c16e2c2982`.
- Сборщик: `php E:/.agents/tools/phing-packager/phing-latest.phar -f phing.xml '3. Package release'`.
- Evidence: `docs/reports/2026-10-13-ajax-routing-evidence/regression.json` и `package-audit.json`.
- Предыдущая сборка 1.4.0 сохранена в `.webtolk/tmp/ajax-routing-20261013/WT Cdek library package_1.4.0.before-routing.zip`, SHA256 681653e2034431204463134f1ff41ee454a97b10ccb8125f5d7007657e6b0262.

Этот отчёт описывает финальную реализацию и заменяет архитектурные выводы отчёта ajax-cookie-verification: отдельного обработчика больше нет, прежний дефект продолжения после неверного токена устранён. F1–F5 остаются отдельным backlog. Установка и публикация не выполнялись, Git index агент не редактировал.
