# AJAX-параметры и cookie: исправление и проверка

## Причина

Исследован предоставленный архив n3t Cookie Consent 4.4.0 (334226 байт, SHA256 `749f926e72e3edf8b83a2c4bd1adae14133b51fcf5fe50ab85266d47806c8d8f`). В `media/js/cookieconsent.js:25` задано имя `cc_cookie`, в строке 1136 состояние сериализуется через JSON.stringify, в строках 2094–2119 записывается document.cookie. Это обычный cookie; его формат менять не требуется.

В локальном Joomla Framework Input конструктор без источника использует `$_REQUEST`. Состав этого массива зависит от конфигурации PHP; при включении cookie root getArray() возвращает и их. Старый onAjaxWtcdek отправлял весь этот массив DeliverypointsEntity или CalculatorEntity. DeliverypointsEntity объединяет параметры с defaults, не удаляя неизвестные ключи; CalculatorEntity после валидации передаёт параметры запросу. Таким образом cookie мог дойти до СДЭК. Отказ реального API сообщён оператором; здесь воспроизведена именно утечка параметров без обращения к провайдеру.

## Изменение

В `plg_system_wtcdek/src/Extension/Wtcdek.php` root getArray() заменён на `array_replace($input->get->getArray(), $input->post->getArray())`. При совпадении ключей POST имеет приоритет. Вложенные массивы адресов/мест и прежняя фильтрация Joomla сохраняются. Значения JSON, намеренно переданные через GET/POST, не вырезаются. Решение не зависит от имени cookie. Исправлен устаревший комментарий метода о Telegram.

Entities, CdekRequest и n3t не изменены. Новый обработчик onAjaxWtcdekdeliverypoints уже читает только явно заданные POST-поля и в этой правке не менялся. Прежний контракт результатов, маршрутизация AJAX и проверка токена сохранены.

## Проверка

Регрессионный fixture `.webtolk/tests/ajax-cookie.php` использует настоящие классы Joomla Input/Event/Plugin/Session, CDEK подменён, подключение БД запрещено, site configuration не загружается.

- До исправления: воспроизводимый fail `get cookie leaked`.
- После исправления: по 20 checks на Joomla 4.4.14, 5.4.7 и 6.1.3; всего 60 passed.
- Проверены GET/POST для offices и calculate, вложенные адреса и массив packages, числовые значения, прежняя фильтрация false, JSON как явный параметр, приоритет POST, произвольные имена cookie, cookie-only action и попытка cookie подменить action/city_code.
- Ещё 20 checks прошли с PHP-файлом, извлечённым из финального ZIP.
- PhpStorm ERROR inspections: 0 проблем. PHP lint прошёл.
- Машинные результаты: `2026-10-13-ajax-cookie-evidence/regression.json` и `package-audit.json`.

Это офлайн проверка передачи данных; установка нового пакета и реальный ответ СДЭК остаются ручной проверкой оператора. Raw JSON body не добавлялся к источникам данных: эта правка сохраняет существующую обработку параметров GET/POST. Отдельная ранее существовавшая проблема: ветка неверного Session::checkToken() записывает ошибку без return; она не исправлялась в рамках устранения cookie.

## Пакет

Финальный ZIP — `.packages/WT Cdek library package_1.4.0.zip`, VERSION 1.4.0, 383088 байт, 66 entries.

SHA256: `681653e2034431204463134f1ff41ee454a97b10ccb8125f5d7007657e6b0262`.

Штатный сборщик: `php E:/.agents/tools/phing-packager/phing-latest.phar -f phing.xml '3. Package release'`. Исходный ZIP 1.4.0 до этой правки сохранён в `.webtolk/tmp/ajax-cookie-20261013/WT Cdek library package_1.4.0.before-cookie-fix.zip`; его SHA256 `f5849366b33d98dc96bb389957e86cec5fa1ca8a31bb73f4c3c30ad99715996d`. Он заменён новым финальным пакетом, а не считается содержащим исправление.

Публичный 1.3.2 и прежний 1.4.0-dev ZIP остались неизменными. Публикация/установка не выполнялись. Старые findings F1–F5 не входят в эту правку.
