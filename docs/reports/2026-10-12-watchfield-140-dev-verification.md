# Приёмка WT CDEK 1.4.0-dev

**Результат: passed в запрошенном объёме.** Пакет собран, установлен на `joomla.local`, проверен в настоящем Chrome через DevTools Protocol. Документация полей подготовлена после первого успешного полного E2E; окончательная сборка также прошла проверки.

## Среда и доказательства

- Стенд: `E:/OSPanel/home/joomla.local/public`, установленная Joomla **6.1.3**.
- Тестовая страница: `http://joomla.local/_wtcdek_e2e_140.php`, localhost-only, удалена после приёмки. Она рендерит установленную библиотеку через native Joomla Form, WebAssetManager, Script Options и сессию; исходники из рабочего дерева в страницу не подставляются.
- Браузер: установленный Chrome, headless, DevTools Protocol; desktop 1280×900 и mobile viewport 390×844. Browser MCP в данной сессии отсутствовал, поэтому использован непосредственно CDP. Скриншоты проверены визуально.
- Исходный тест: `.webtolk/tests/watchfield-native-fixture.php`; сценарий CDP: `.webtolk/tests/watchfield-native-e2e.cjs`.
- Результат: [browser-report.json](../../.webtolk/tmp/watchfield-140-dev/browser-report.json), **39 checks / 0 uncaught JS exceptions**.
- Скриншоты: [initial](../../.webtolk/tmp/watchfield-140-dev/native-initial.png), [updated](../../.webtolk/tmp/watchfield-140-dev/native-updated.png), [mobile](../../.webtolk/tmp/watchfield-140-dev/native-mobile.png).

## Проверки

| Область | Результат |
| --- | --- |
| Публичный Tarifflist и Tariffinfo | Скалярный watchfield, alias tariff_field и named map работают |
| Несколько потребителей | Пять карточек одного тарифа; ровно один input и один change на источнике |
| Изоляция форм | Даже при одинаковых именах контролов обновляется только owning Form |
| Script Options | Точный ключ с точкой + nested path, alias onpage, тарифы и пункты |
| AJAX | Общий POST-провайдер работает для тарифов и пунктов, передаёт снимок всех значений |
| XML-адрес | Наличие city/zip/fias_guid, включая пустые, полностью отключает watchfield |
| Реальный endpoint библиотеки | Native com_ajax возвращает 28 ПВЗ Саратова по city+zip, literal city и FIAS UUID |
| Фильтры CDEK | POSTAMAT и reception=false/handout=true возвращают штатный пустой список для выбранного города |
| Выбор | Сохранённый отсутствующий код не теряется при первом ответе; смена адреса немедленно сбрасывает выбор до debounce |
| Асинхронность | Серия input даёт один запрос; отменённый медленный ответ не заменяет быстрый |
| Ошибки | 502, неверный JSON, пустой результат и пустой адрес; последующее восстановление |
| Tariffinfo states | Пустое значение, неизвестный код, ошибочный путь |
| Native FancySelect | AJAX-варианты синхронизируются с Choices, подписи остаются текстом |
| Native subform | Добавление строки, изменение тарифа только в этой строке, удаление и снятие подписок |
| Повторный scan | joomla:updated не дублирует подписки |
| Универсальные источники | Radio, группа checkbox, multiple select |
| Защита | Missing CSRF и GET отклонены реальным com_ajax; foreign-origin URL блокируется до передачи токена; HTML-подписи не исполняются |
| Языки | RU и EN выгружены через WatchfieldAssetItem, доступны Joomla.Text |

Для ошибок и гонок использован временный localhost AJAX-provider, который работает с настоящими fetch и сессией Joomla. Для CDEK выполнены только чтения справочников через настроенную библиотеку; штатное кэширование библиотеки сохранялось. Создание отправлений, заказов и изменения у провайдера не выполнялись. Пустые выборки по фильтрам не доказывают наличие постаматов/пунктов только выдачи в Саратове; включение и отбор таких записей отдельно подтверждены контрольными ответами.

## PHP, package, настройки

Контрактные проверки `.webtolk/tests/deliverypoints.php` на настоящих core-классах Joomla, с запрещённым доступом к БД и stub CDEK: Joomla 4.4.14 — **83**, Joomla 5.4.7 — **84**, Joomla 6.1.3 — **84**. Они проверяют фильтры, FIAS, разрешение Form, приоритет XML, reject до создания клиента и формы Joomla 4/5/6 event results. Это офлайн совместимость; браузерная установка проверена на Joomla 6.1.3.

[Аудит ZIP](../../.webtolk/tmp/watchfield-140-dev/package-audit.json): **66 файлов**, **40 PHP lint passed**; три JS прошли `node --check`. Тела PHP совпадают с исходниками после разрешённых преобразований метаданных Phing, JS совпадают с ZIP и установленными файлами. Четыре manifest имеют `1.4.0-dev`; deploy-токенов, донорских Wtsmcdek-полей, docs, flow, IDE и Git-файлов в ZIP нет.

Снимки БД [before](../../.webtolk/tmp/watchfield-140-dev/stand-before.json) / [after](../../.webtolk/tmp/watchfield-140-dev/stand-after.json) содержат только SHA256 параметров, версии и состояние enabled: параметры и enabled сохранены. Joomla при переустановке библиотеки меняет её extension_id; идентичность проверяется по element/type, а не временному ID. Все четыре расширения имеют версию `1.4.0-dev`. Резервная копия исходных файлов стенда сохранена в ignored `.webtolk/tmp/watchfield-140-dev/stand-backup`.

ZIP: `.packages/WT Cdek library package_1.4.0-dev.zip`.

SHA256: `ea3ca0fc0091d9ce6c5dacc51e7f76bc7bb6ffd456d46bf05a71a835c741149c`.

Публичный ZIP **1.3.2** неизменен: `e36a0ec075990ce914942b47c650e14870a1802f8ff6fa71a77fa92c4bfe9d88`.

## Границы

Проверена библиотечная форма на установленном стенде, а не весь checkout стороннего магазина. Нативная браузерная приёмка Joomla 4/5 и авторизованного administrator UI отдельно не выполнялась. Новая dev-версия не опубликована на GitHub; публичный [1.3.2](https://github.com/WebTolk/WT-CDEK-Joomla-PHP-library/releases/tag/v.1.3.2) уже выпущен отдельно.

Конкретные исправления, найденные при проверке: запуск defer-сканера до renderer-ассетов; немедленная invalidation выбора; snapshot вариантов Choices до clearStore и allowHTML=false; URL поиска относительно корня клиента Joomla. Ошибки тестового bootstrap сессии и области переменных CDP исправлены в fixture, в продукт они не входили.
