[![Version](https://img.shields.io/github/release/WebTolk/WT-CDEK-Joomla-PHP-library.svg)]() [![PHP](https://img.shields.io/badge/PHP-7.4+-green.svg)]() [![JoomlaVersion](https://img.shields.io/badge/Joomla-4.2.7+-orange.svg)]() [![JoomlaVersion](https://img.shields.io/badge/Joomla-5.x-orange.svg)]() [![Version](https://img.shields.io/badge/Documentation-blue.svg)](https://web-tolk.ru/dev/biblioteki/wt-cdek-library-for-joomla-developers?utm_source=github)
# WT CDEK Joomla PHP library
Небольшая нативная PHP Joomla библиотека для работы с API службы доставки CDEK. Пакет состоит из плагина для хранения настроек, PHP-библиотеки и виджета карты для выбора пунктов выдачи заказа. Библиотека представляет собой клиент для авторизации в CDEK API по OAuth, работы с некоторыми методами API: получения ряда данных и расчета стоимости доставки. Поддерживается Joomla 4.2.7 и выше.
![image](https://github.com/WebTolk/WT-CDEK-Joomla-PHP-library/assets/6236403/ff2d142d-c602-41fc-afa8-dc3490fc929f)
# Описание
В составе пакета
- библиотека WT CDEK library
- плагин настроек для подключения к API CDEK System - WT Cdek
- [официальный виджет выбора типа доставки от CDEK]([url](https://widget.cdek.ru/))

>Библиотека представляет собой клиент для подключения к API CDEK и несколько методов для работы с ним с предварительной проверкой структуры данных, а также обработку ошибок при запросе. Для работы с библиотекой под рукой необходимо иметь официальную документацию CDEK API.
>
> [https://api-docs.cdek.ru/]([url](https://api-docs.cdek.ru/))

# Подключение библиотеки в своё расширение для Joomla
```php
<?php
use Webtolk\Cdekapi\Cdek;

defined('_JEXEC') or die('Restricted access');

// Значения из настроек плагина
$cdek = new Cdek();
// или 
$test_mode = true;
$client_id = 'adkjhakjaukajds';
$client_secret = 'adkjhakjaukajds';
$cdek = new Cdek($test_mode, $client_id, $client_secret);

// Индекс получателя
$index_to = 410012;

// Название населенного пункта
$city = 'Саратов';

// Массив параметров запроса к CDEK API
$cdek_city_options = ['size' => 1];

if (!empty($index_to))
{
	$cdek_city_options['postal_code'] = trim((string) $index_to);
}
if (!empty($city))
{
	$cdek_city_options['city'] = trim((string) $city);
}
$cdek_city = $cdek->getLocationCities($cdek_city_options);
```
## Пример ответа
```
Array
(
    [0] => Array
        (
            [code] => 428
            [city_uuid] => 7e54a0b3-76f0-41e2-92e0-f1e600ad84fd
            [city] => Саратов
            [fias_guid] => bf465fda-7834-47d5-986b-ccdb584a85a6
            [country_code] => RU
            [country] => Россия
            [region] => Саратовская область
            [region_code] => 47
            [fias_region_guid] => df594e0e-a935-4664-9d26-0bae13f904fe
            [sub_region] => городской округ Саратов
            [longitude] => 46.034266
            [latitude] => 51.533562
            [time_zone] => Europe/Saratov
            [payment_limit] => -1
        )

)
```
# Список методов библиотеки
### getDeliveryPoints
Метод предназначен для получения списка действующих офисов СДЭК. Если одновременно указаны параметры city_code, postal_code, fias_guid, то для определения города всех стран присутствия СДЭК приоритет отдается city_code, затем fias_guid.
### getLocationRegions
Список регионов. Метод предназначен для получения детальной информации о регионах. Список регионов может быть ограничен характеристиками, задаваемыми пользователем. В список параметров запроса не добавлены параметры, помеченные устаревшими.
### getLocationCities
Список населенных пунктов. Метод предназначен для получения детальной информации о населенных пунктах. Список населенных пунктов может быть ограничен характеристиками, задаваемыми пользователем. В список параметров запроса не добавлены параметры, помеченные устаревшими.
### getCalculatorTariff
Калькулятор. Расчет по коду тарифа. Метод используется для расчета стоимости и сроков доставки по коду тарифа.
### getCalculatorTarifflist
Калькулятор. Расчет по всем доступным тарифам. Метод используется клиентами для расчета стоимости и сроков доставки по всем доступным тарифам.
### subscribeToWebhook
Подписка на вебхуки (Webhooks). Методы предназначены для управления подпиской на получение вебхуков на URL клиента. Так как тестовый аккаунт СДЭК является общим для всех клиентов, для тестирования вебхуков необходимо использовать только боевой URL СДЭК. В запросе на добавление подписки укажите свой тестовый URL, куда будут приходить вебхуки. После завершения тестирования поменяйте его на свой боевой URL. Если у клиента уже есть подписка с указанным типом, то старый url перезаписывается новым.
### createOrder
Запрос на регистрацию заказа.
### getTariffListShop
Массив с тарифами CDEK для типа "интернет-магазин", актуальный на момент выхода версии библиотеки.
### getTariffListDostavka
Массив с тарифами CDEK для типа "доставка", актуальный на момент выхода версии библиотеки.
### getLocationPostalCodes
Метод получает список почтовых индексов для населенного пункта по его коду.

> Все методы принимают в качестве аргумента массив параметров запроса `$request_options`, структура которого должна соответствовать параметрам запроса документации CDEK API.

# Официальный виджет выбора типа доставки от CDEK (выбор пунктов выдачи заказа на карте)
![image](https://github.com/WebTolk/WT-CDEK-Joomla-PHP-library/assets/6236403/d7b4dec1-1984-484f-8014-73a2d329af88)
![image](https://github.com/WebTolk/WT-CDEK-Joomla-PHP-library/assets/6236403/4714470e-a7ce-4a3a-82a2-04992ae1bdb6)
## Подключение JavaScript виджета CDEK в Joomla
Javascript виджета оформлен как Joomla Web Asset. В своём коде подключаем его с помощью WebAssetManager следующим образом:
```php
<?php
use Joomla\CMS\Factory;

defined('_JEXEC') or die('Restricted access');

$doc  = Factory::getApplication()->getDocument();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $doc->getWebAssetManager();
$wa->useScript('cdek-widget-umd');
```
В остальном при настройке следуем документации виджета.
## Service.php виджета
Виджет представляет собой Яндекс.карту, которая по ajax получает список пунктов выдачи заказа. Для работы с данной библиотекой нужно при инициализации виджета указать параметр `servicePath` - url для ajax-запроса. По умолчанию в комплекте с виджетом идёт файл **service.php**, который является точкой входа для ajax-запроса. В данной библиотеке функционал этого файла (получение списка ПВЗ и калькуляции тарифов) перенесён в системный плагин Joomla.
```php
<?php
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

defined('_JEXEC') or die('Restricted access');

$service_url = new Uri(Uri::base());
$service_url->setPath('/index.php');
$service_url->setQuery([
    'option'                => 'com_ajax',
    'plugin'                => 'wtcdek',
    'group'                 => 'system',
    'format'               => 'raw',
    'city_code'          => $city_code, // CDEK код города для получения списка ПВЗ только для выбранного города
    Session::getFormToken() => 1
]);
// URL string
$service_url->toString();
```
Для javascript используем либо Joomla Script options, либо php echo, в зависимости от структуры вашего расширения.

## Копирование и обновление данных CDEK в локальную базу данных
Добавлен плагин стандартного планировщика задач Joomla (появился в Joomla 4.1), который позволяет копировать и обновлять по расписанию списки стран и регионов доставки, населенных пунктов, а так же пунктов выдачи заказа. Эти данные вы можете использовать затем в своих расширениях. 
Рекомендуется настроить выполнение задач планировщика Joomla с помощью серверного CRON, так как некоторые справочники довольно большого объёма и их обновление может занимать продолжительное время.
Чтобы запустить выполнение задач планировщика с помощью CLI Вам нужно подключиться к своему серверу по SSH и выполнить команду:
```
php /path/to/site/public_html/cli/joomla.php scheduler:run
```
Если требуется запустить конкретную задачу, то посмотреть список можно с помощью команды
```
php /path/to/site/public_html/cli/joomla.php scheduler:list 
```
а затем запустить задачу по её `id`
```
php /path/to/site/public_html/cli/joomla.php scheduler:run --id=XXX
```
Также будьте внимательны, на некоторых хостингах существует ограничение на занимаемый объём базы данных. 