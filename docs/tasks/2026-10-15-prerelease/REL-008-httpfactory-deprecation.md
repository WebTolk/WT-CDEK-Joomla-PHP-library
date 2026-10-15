# REL-008: Запланировать переход с CMS HttpFactory на Framework API

Приоритет: **P3**. Статус: **Открыта; не блокирует поддерживаемые Joomla 4/5/6**.

## Подтверждение

PhpStorm и установленный Joomla 6 HttpFactory: deprecated с 6.0, removal 8.0.

## Места в коде

- `lib_webtolk_wtcdek/src/CdekRequest.php:22`
- `lib_webtolk_wtcdek/src/CdekRequest.php:217`

## Проблема

Транспорт использует deprecated Joomla\CMS\Http\HttpFactory. Сейчас API ещё доступен; предупреждение не означает отказ Joomla 4/5/6.

## Требуемое изменение

Проверить Framework Joomla\Http\HttpFactory на минимальной Joomla 4.2.7; затем мигрировать с сохранением Registry options, adapters, timeout и ResponseInterface. Не повышать минимум PHP/Joomla.

## Критерии приёмки

- [ ] Подтверждено наличие выбранного Framework API на Joomla 4.2.7.
- [ ] Offline-тесты GET/POST/DELETE, OAuth, headers, timeout и response mapping проходят на поддерживаемых cores.
- [ ] В транспорте нет вызова deprecated CMS factory; PHP 7.4 сохранён.

## Доказательства

- [phpstorm.json](../../reports/2026-10-15-prerelease-evidence/phpstorm.json)

## Ограничения

Сохранить Joomla 4.2.7+ и PHP 7.4. Не запускать реальные запросы CDEK, менять стенд, выпускать ZIP/релиз или исправлять соседние задачи без отдельного scope. Этот файл — проектная задача; native PF ledger отсутствует, проект работает через .webtolk.


## Планирование релиза

Оператор перенёс эту задачу на следующий релиз 15.10.2026; номер версии ещё не назначен. Реализация не начата. Места/строки исходного аудита относятся к срезу до форматирования. [Текущий пакет](../../release/2026-10-15-1.4.0-style-package.md).
