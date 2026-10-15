# REL-002: Разделить кэш по аккаунту и окружению CDEK

Приоритет: **P1**. Статус: **Открыта**.

## Подтверждение

Подтверждено PHPUnit без HTTP и БД; присутствует в main.

## Места в коде

- `lib_webtolk_wtcdek/src/CdekRequest.php:365`
- `lib_webtolk_wtcdek/src/CdekRequest.php:465`
- `lib_webtolk_wtcdek/src/Entities/CalculatorEntity.php:36`

## Проблема

Все клиенты используют token key wt_cdek в общей группе. Записи двух аккаунтов и sandbox перезаписывают друг друга; возможен выбор токена другого договора/окружения. Каталог тарифов также имеет общий key calculator_alltariffs.

## Требуемое изменение

Согласованно выделить стабильный namespace для аккаунта и API-окружения в token get/store/remove и кэше договорных тарифов. Не включать открытые client_secret/токены в ключи или журнал. Старую общую запись не использовать как валидную запись любого аккаунта.

## Критерии приёмки

- [ ] Три клиента (два production-аккаунта и sandbox) имеют независимые записи; тест testTokenCacheSeparatesAccountsAndEnvironments проходит.
- [ ] Очистка/просрочка одного токена не меняет остальные.
- [ ] Смена credentials не использует токен прежнего контекста; одинаковые credentials повторно используют свой кэш.
- [ ] Тарифы разных договоров изолированы; выполнены offline-regressions и PHP 7.4 lint.

## Доказательства

- [phpunit.stdout](../../reports/2026-10-15-prerelease-evidence/phpunit.stdout)
- [ReleaseContractsTest.php](../../reports/2026-10-15-prerelease-evidence/ReleaseContractsTest.php)
- [summary.json](../../reports/2026-10-15-prerelease-evidence/summary.json)

## Ограничения

Сохранить Joomla 4.2.7+ и PHP 7.4. Не запускать реальные запросы CDEK, менять стенд, выпускать ZIP/релиз или исправлять соседние задачи без отдельного scope. Этот файл — проектная задача; native PF ledger отсутствует, проект работает через .webtolk.


## Планирование релиза

Оператор перенёс эту задачу на следующий релиз 15.10.2026; номер версии ещё не назначен. Реализация не начата. Места/строки исходного аудита относятся к срезу до форматирования. [Текущий пакет](../../release/2026-10-15-1.4.0-style-package.md).
