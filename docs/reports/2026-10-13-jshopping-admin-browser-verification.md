# WT CDEK: проверка полей в administrator UI

Результат: поля работают в проверенных штатных сценариях на установленной Joomla 6.1.3. Проверка проведена через Playwright и настоящий Chrome с реальными com_ajax ответами. Настройки не сохранялись; код продукта не изменялся.

## Проверенные страницы

- Глобальная конфигурация WT CDEK: /administrator/index.php?option=com_jshopping&controller=shippingextprice&task=edit&id=4. Здесь params.xml/configform.php рендерят Deliverypoints.
- Цена доставки «СДЭК ПВЗ на карте»: /administrator/index.php?option=com_jshopping&controller=shippingsprices&task=edit&sh_pr_method_id=3&shipping_id_back=. Здесь общий params.xml используется shippingpriceform.php для Tarifflist/Tariffinfo.

## Checks Run / Results

| Сценарий | Результат |
| --- | --- |
| Начальная загрузка Саратов / 410000 | com_ajax POST 200, success=true, 28 ПВЗ + пустой вариант |
| Подключение Watchfield и CSRF | Joomla.Watchfield присутствует, csrf.token присутствует; sources params[city_from]/params[city_from_zip], form adminForm |
| Native FancySelect | choicesInstance готов; пункт SAR3 выбран через видимый UI и отображается в native select и Choices |
| Ввод Москвы в текстовое поле | input меняет снимок источников и очищает ранее выбранный SAR3; отображается состояние загрузки |
| Ввод индекса 101000 | После смены обоих источников POST 200/success=true; 424 ПВЗ Москвы + пустой вариант; предложен повторный выбор |
| Пустые город и индекс | Список очищен; сообщение «Укажите город, почтовый индекс или UUID населённого пункта ФИАС.» |
| Перезагрузка глобальной формы | Вернулись исходные Саратов / 410000 / пустой shipment_point; 28 пунктов загрузились снова |
| Tarifflist | 271 вариант, исходное значение 136 |
| Tariffinfo | Для 136 отображается «Посылка» и соответствующие ограничения |
| Изменение тарифа 136 → 333 | Без перезагрузки карточка меняется на «Посылочка», код 333, вес 0–18 и соответствующие габариты/типы контрагентов |
| Перезагрузка тарифной формы | Восстановлены сохранённый 136 и его карточка |
| Завершение | Обе формы закрыты через «Отменить»; тестовый Chrome закрыт |

Непойманных pageerror: 0. В console присутствует отдельное предупреждение COOP: Chrome игнорирует соответствующий HTTP-заголовок для http://joomla.local. Оно повторяется на страницах сайта и не является ошибкой Watchfield. Ошибок UI-полей в проверенных сценариях не обнаружено.

## Evidence

- 2026-10-13-jshopping-admin-browser-evidence/result.json: снимки состояний формы и очищенные сетевые результаты (без токенов/паролей).
- global-initial.png, global-moscow.png: начальная загрузка и результат смены адреса.
- tariff-initial.png, tariff-changed.png: карточки 136 и 333.
- runner.cjs: диагностический Playwright runner; учётные данные получает только из process env и не записывает в evidence.
- manifest.json: SHA256 сохранённых доказательств.

## Checks Skipped / Limits

Сохранение/серверная валидация, retry после ошибки, цепочки потребителей, legacy overrides, reconfigure, mobile и другие тарифные строки отдельно не проверялись. Эта проверка не закрывает F1–F5 из повторного аудита библиотеки и не является полной приёмкой выпуска.

Реальные запросы касались чтения каталога и справочников через настроенную библиотеку; штатное кэширование могло выполняться. Отправления/заказы и настройки не изменялись. Код сайта, Git index и ZIP не редактировались. После reload подтверждены исходные значения без save/apply.

## Browser Connection / Diagnostics

Пользователь сначала разрешил autoConnect для Chrome DevTools; изменён только chrome_devtools.args в C:/Users/Сергей/.codex/config.toml, backup блока сохранён в .webtolk/tmp/chrome-mcp-connect-20261013/. Remote debugging в пользовательском Chrome автоматически не включался.

Затем пользователь явно выбрал Playwright и предоставил тестовую учётную запись. Выполнен вход на тот же локальный сайт. Первый MCP diagnostic callback упал из-за URLSearchParams, отсутствующего в MCP VM; это ошибка диагностического кода, не продукта. Итоговая проверка выполнена установленным Playwright напрямую с node:url и официальным уже установленным Chrome, без установки нового ПО. После завершения браузер закрыт; оставшийся stdin диагностического runner завершён. Replay runner дополнен lines.close() для штатного выхода.
