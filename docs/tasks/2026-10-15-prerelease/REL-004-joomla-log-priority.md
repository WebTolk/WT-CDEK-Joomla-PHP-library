# REL-004: Передавать Joomla Log числовой приоритет

Приоритет: **P2**. Статус: **Открыта**.

## Подтверждение

Подтверждено PHPUnit с настоящим Joomla LogEntry; присутствует в main.

## Места в коде

- `lib_webtolk_wtcdek/src/Traits/LogTrait.php:42`
- `lib_webtolk_wtcdek/src/Traits/LogTrait.php:57`

## Проблема

Log::add получает строку 'Log::ERROR' вместо Log::ERROR. Joomla нормализует неизвестное значение к INFO: 64 вместо ожидаемых 8.

## Требуемое изменение

Сохранить публичный string-параметр saveToLog, явно преобразовать допустимые названия в числовые константы Joomla с определённым fallback. Отдельно согласовать названия типов enqueueMessage, если требуется показ в UI.

## Критерии приёмки

- [ ] testErrorPriorityReachesJoomlaLogger проходит.
- [ ] ERROR/WARNING/NOTICE/INFO записываются с правильными уровнями.
- [ ] Неизвестное имя имеет предсказуемый fallback; отключённый show_library_errors не выводит сообщение.
- [ ] Сигнатуры вызывающего кода и PHP 7.4 совместимы.

## Доказательства

- [phpunit.stdout](../../reports/2026-10-15-prerelease-evidence/phpunit.stdout)
- [phpstan.stdout](../../reports/2026-10-15-prerelease-evidence/phpstan.stdout)
- [ReleaseContractsTest.php](../../reports/2026-10-15-prerelease-evidence/ReleaseContractsTest.php)

## Ограничения

Сохранить Joomla 4.2.7+ и PHP 7.4. Не запускать реальные запросы CDEK, менять стенд, выпускать ZIP/релиз или исправлять соседние задачи без отдельного scope. Этот файл — проектная задача; native PF ledger отсутствует, проект работает через .webtolk.


## Планирование релиза

Оператор перенёс эту задачу на следующий релиз 15.10.2026; номер версии ещё не назначен. Реализация не начата. Места/строки исходного аудита относятся к срезу до форматирования. [Текущий пакет](../../release/2026-10-15-1.4.0-style-package.md).
