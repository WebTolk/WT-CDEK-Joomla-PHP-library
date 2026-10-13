# Watchfield WebAsset: зависимости и источники данных

С **1.4.0-dev** Tariffinfo и Deliverypoints используют общий декларативный обработчик — WebAsset `wtcdek.watchfield`. Он сканирует `[data-watchfield]`, находит источники по полным именам из Joomla Form и подписывается на изменения. Отдельный JS для подключения каждого поля не нужен.

Как и showon, Watchfield задаёт поведение существующего поля через атрибуты. `watchfield="…"` указывается на поле-потребителе; отдельный тип поля `type="watchfield"` не требуется и не предоставляется библиотекой. Ассеты Tariffinfo и Deliverypoints подключают общий обработчик через зависимости WebAssetManager.

`watchfield` принимает имя поля или именованную JSON-карту:

```xml
watchfield="tariff_code"
watchfield='{"tariff_code":"delivery.tariff_code"}'
watchfield='{"city":"city","zip":"postal_code"}'
```

PHP-помощник `Webtolk\Cdekapi\Helper\Watchfield` разрешает имена через owning Form и формирует `data-*` атрибуты для WebAsset. Он не является Joomla FormField. Не нужно передавать HTML ID или писать префикс `jform`. Допустимые ключи карты определяет конкретное поле: Deliverypoints принимает `city`, `zip`, `fias_guid`; Tariffinfo выбирает код по `tariff_code`, `value` или первому ключу.

## AJAX и данные страницы

| XML-атрибут | Действие |
| --- | --- |
| `watchfield-data-source="scriptoptions"` | Читает JSON, добавленный через `Document::addScriptOptions()` |
| `watchfield-data-source="onpage"` | Alias `scriptoptions` |
| `watchfield-data-options="shop.cdek"` | Точный ключ `Joomla.getOptions('shop.cdek')` |
| `watchfield-data-path="nested.catalog"` | Путь внутри объекта; числовой сегмент обращается к элементу массива |
| `watchfield-data-source="ajax"` | Выполняет POST на URL того же origin |
| `watchfield-data-url="…"` | Адрес AJAX endpoint |

Точки **в ключе Script Options** не разделяют путь. `watchfield-data-options="shop.cdek"` и `watchfield-data-path="nested.catalog"` означают `Joomla.getOptions('shop.cdek').nested.catalog`. Пустой путь возвращает весь объект. Для стандартного Deliverypoints путь — `data.0.options`.

При AJAX обработчик передаёт снимок всех связанных значений, статические параметры поля и CSRF-токен Joomla. Используется POST с `credentials: same-origin`. Endpoint обязан проверить токен и вернуть JSON. HTTP-ошибка, `success: false`, неверный JSON или отсутствующий путь приводят к событию ошибки. Переводы доступны через `Joomla.Text._()`.

## Жизненный цикл

Поиск и первоначальная загрузка выполняются после готовности DOM и зависимых WebAsset. На одном HTML-источнике установлен один обработчик `input` и один `change`, даже при нескольких потребителях. Область поиска — ближайшая форма. Radio возвращает выбранное значение, группа checkbox и multiple select — массив.

Для AJAX `input` имеет debounce 250 мс; `change` ставит обновление без этой задержки. Одинаковый снимок не вызывает повторную загрузку. Изменение данных отменяет предыдущий запрос; счётчик поколений защищает от поздних ответов. Удаление поля освобождает подписки и отменяет запрос.

`joomla:updated` повторно сканирует форму. Строки native repeatable subform получают источники своего индекса по `data-base-name`/`data-group`, как в showon Joomla. `Joomla.Watchfield.scan()` можно вызвать после собственного обновления разметки. Изменять значения программно следует с событием `input` или `change`.

## Текстовые поля

Источниками Watchfield могут быть обычные текстовые `input` и `textarea`, указанные в атрибуте `watchfield` поля-потребителя. Например, Deliverypoints может наблюдать за текстовыми полями города и индекса через `watchfield='{"city":"city","zip":"postal_code"}'`.

- `input` сообщает об изменении при вводе, вставке и удалении текста. Для AJAX загрузка выполняется через 250 мс после последнего такого события; для Script Options этой задержки нет.
- `change` запускает обновление без задержки 250 мс. У текстового поля это событие обычно возникает после потери фокуса, если значение изменилось.
- Значения читаются с `trim()`: пробелы в начале и конце удаляются перед сравнением и передачей данных. Если после этого значение осталось прежним, повторное обновление не требуется; пробелы внутри текста сохраняются.

Присваивание через JavaScript само по себе не создаёт событий `input` или `change`. После изменения `.value` отправьте одно из этих событий. Пример для поля города с именем `jform[params][city]`:

```javascript
const cityInput = document.querySelector('input[name="jform[params][city]"]');

if (cityInput) {
  cityInput.value = 'Саратов';
  cityInput.dispatchEvent(new Event('change', { bubbles: true }));
}
```

Используйте полное HTML-имя своего источника в селекторе. Событие `change` подходит для готового значения, например после выбора города в подсказках; `input` применяет обычную задержку AJAX при последовательном вводе.

## Подключение собственного потребителя

```php
use Joomla\CMS\Factory;
use Webtolk\Cdekapi\Helper\Watchfield;

$document = Factory::getApplication()->getDocument();
$wa = $document->getWebAssetManager();
$wa->getRegistry()->addRegistryFile('media/lib_wtcdek/joomla.assets.json');
$wa->useScript('wtcdek.watchfield');
$sources = Watchfield::resolve($form, '{"city":"city"}', 'address');
$attributes = Watchfield::attributes($sources, [
    'source' => 'scriptoptions', 'options' => 'shop.cdek', 'path' => 'catalog',
]);
// Выведите $attributes на контейнере своего поля.
```

Обработчик отображения подключается отдельным WebAsset с зависимостью `wtcdek.watchfield` и слушает события контейнера или их всплытие:

```javascript
document.addEventListener('watchfield:data', (event) => {
  if (!event.target.matches('.my-field')) return;
  const { values, data, initial } = event.detail;
  // values — снимок всех именованных источников; data — данные после применения пути.
  // initial — первоначальная загрузка. Отобразите данные своего поля.
});
```

События: `watchfield:invalidate`, `watchfield:loading`, `watchfield:data`, `watchfield:error`, `watchfield:empty`. Все содержат `values` и `initial`; `data` содержит полученный набор, `error` — `message`. `invalidate` немедленно сообщает об изменении источников, до debounce и AJAX; Deliverypoints сразу сбрасывает устаревший выбор. `empty` означает отсутствие требуемого адреса перед AJAX-поиском Deliverypoints.

В шаблонах и строках subform конфигурация хранится рядом с контейнером поля. Глобальной карты по ID нет. Это позволяет клонировать строки стандартными средствами Joomla без ручной регистрации новых ID.
