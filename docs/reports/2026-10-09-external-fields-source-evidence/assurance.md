# Проверка полей WT CDEK

## Test plan и test cases

Проверены реальные Joomla Form ID/name/value, группы, неправильные карты, literal priority, несколько одновременно отрисованных полей, изменение зависимостей, scalar/map tariffinfo, сохранённая selection, запоздавшие и ошибочные ответы, безопасный текст Fancy Select, покрытие manifest и отсутствие изменения сохранённых настроек. Область — поля; assurance прежнего lifecycle/security audit не возобновлялась.

## Test report

- По 61 проверке в offline.php на Joomla 5.4.7 из PF knowledge и установленной Joomla 6.1.3. Используются реальные Form и FileAdapter, SQLite :memory: для dependency injection без соединения с сайтом; внешних HTTP/DB операций нет. Иной control different_control, leading-zero 041000, текущая/явная группа, scalar watchfield, пять неверных карт, полный 27-file fileset и только owned folders подтверждены.
- 13 команд final-source.py: шесть PHP lint, пять node --check, git diff --check и существующий tests/origin-pickup-points.php с семью assertions. Все exit=0. CRLF warnings Git не являются ошибками. Старых ссылок origin-point.js и addFieldPath(...fields) в product source нет.
- 10 assertions interactions-browser.js на настоящей admin форме: несколько полей, literal с заведомо отсутствующими watchfield sources, пустые атрибуты, локализованная ошибка неправильной карты, независимость literal полей, изменение map city/zip, scalar/map тарифы. Все passed.
- 11 assertions mock-browser.js на той же реальной форме с перехваченными AJAX ответами: leading-zero, корректный список, безопасные labels, сохранение выбранного/исчезнувшего ПВЗ, warning, race, failure, literal независимость, deduplicated listeners и disconnect. Все passed. Эти assertions доказывают UI поведение с фикстурами, а не provider acceptance.
- Штатная tariff price форма sh_pr_method_id=3: source sm_params[tariff_code], сохранённый136, временно выбран139; карточка обновилась, после reload снова136. Исходный тариф не сохранялся и остался прежним.
- Окончательная settings форма shippingextprice id=4: временные probe поля отсутствуют, все шесть контрольных исходных значений совпадают с baseline; statuses create/sent/cancel имеют8/7/7 options; единственный штатный pickup field получил28 точек; browser pageerrors=[] и PHP fatal/warnings отсутствуют.

## Browser verification и provider scope

Stand только joomla.local, Joomla6.1.3/JoomShopping5.9.3/WT CDEK1.3.2. Нативный администратор авторизован существующими приватными local test credentials. Никакие пароли/token/storage state не включены в публичные отчёты. Public lookup выполнен существующим AJAX/CDEK клиентом; это чтение, не создание shipment.

Первоначально watchfield, literal zip+city и city-only вернули28 ПВЗ Саратова; пустые literals —0. zip-only410000 в этом прогоне дал0: существующий сервис требует однозначного города и не выбирает произвольный результат. Один промежуточный повтор city+zip тоже дал0, при следующем повторе и финальной загрузке снова28; сохраняется как наблюдение изменяемого read-only ответа, код библиотеки/handler не менялся. Точное содержимое provider lookup не используется в fixture assertions о race/failure.

Нативные lookups и конфигурации без CSRF token сохранены в native-inspection.json, native-interactions.json; fixture результаты browser-fixtures.json, штатная tariff форма native-tariff.json, финальная проверка native-final.json. Скриншоты native-tariff.png/native-final.png находятся в приватном tmp. Исходники тестов и диагностические ошибки также сохранены: два первых offline запуска требовали настройки DI самого Form, первый browser wait ошибочно ожидал visible у скрытого select Fancy Select; проверочные сценарии исправлены, финальные проверки passed. Это ошибки harness, не полей.

## Review findings и вывод

Неправильный XML watchfield даёт явное предупреждение вместо PHP fatal/тихого fallback. ID/name берутся из настоящего Joomla Form, prefix params/sm_params не зашит в JS. Любые литералы полностью приоритетны. WAM dependency core -> shared watcher -> поле проверена браузером. Исчезнувшая selection сохраняется при refresh, а изменение адреса очищает зависимый список. No unresolved field defects found within tested scope.

Linux runtime отдельно не запускался; filename/class case проверен сопоставлением с FormHelper ucfirst(type)+Field, при подготовке библиотечного релиза потребуется его собственная проверка. Native installer package update не запускался: fileset проверен реальным FileAdapter, stand обновлён точечно. Повторный install может оставить старые файлы, поэтому решение upgrade cleanup относится к завтрашней отдельной миграции. Полный frontend/lifecycle/security/API release assurance сюда не включён.

Assurance gate: passed для полей и локального прототипа. Library/package release acceptance: not_applicable по последней границе оператора.
