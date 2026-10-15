# REL-003: Сохранять справочники при ошибках обновления

Приоритет: **P1**. Статус: **Открыта**.

## Подтверждение

Подтверждено PHPUnit: memory DB фиксирует TRUNCATE до ответа-ошибки, затем TypeError. В main есть тот же путь.

## Места в коде

- `plg_task_updatewtcdekdata/src/Extension/Updatewtcdekdata.php:145`
- `plg_task_updatewtcdekdata/src/Extension/Updatewtcdekdata.php:258`
- `plg_task_updatewtcdekdata/src/Extension/Updatewtcdekdata.php:342`
- `plg_task_updatewtcdekdata/src/Extension/Updatewtcdekdata.php:430`

## Проблема

Старый справочник удаляется до успешной загрузки. Ответ ['error_code'=>503,...] обрабатывается как список ПВЗ и приводит к TypeError после очистки. Аналогичный порядок очистки используется для регионов и городов.

## Требуемое изменение

Проверять error envelope и элементы ответа; сохранять прежний завершённый набор при сетевой ошибке, ошибке следующей страницы или БД. Выбрать staging/атомарную замену; простой transaction вокруг MySQL TRUNCATE не обеспечивает rollback. Валидировать размер страницы и параметры стран.

## Критерии приёмки

- [ ] Тест testSchedulerPreservesExistingPointsWhenProviderReturnsAnError проходит.
- [ ] Ошибки первой/промежуточной страницы и записи БД не уничтожают прежний набор.
- [ ] Успешное обновление всех страниц заменяет набор целиком; валидный пустой результат имеет явную политику.
- [ ] Ошибка задачи возвращает KNOCKOUT и корректный журнал; работает PHP 7.4.

## Доказательства

- [phpunit.stdout](../../reports/2026-10-15-prerelease-evidence/phpunit.stdout)
- [scheduler-exception.txt](../../reports/2026-10-15-prerelease-evidence/scheduler-exception.txt)
- [ReleaseContractsTest.php](../../reports/2026-10-15-prerelease-evidence/ReleaseContractsTest.php)

## Ограничения

Сохранить Joomla 4.2.7+ и PHP 7.4. Не запускать реальные запросы CDEK, менять стенд, выпускать ZIP/релиз или исправлять соседние задачи без отдельного scope. Этот файл — проектная задача; native PF ledger отсутствует, проект работает через .webtolk.


## Планирование релиза

Оператор перенёс эту задачу на следующий релиз 15.10.2026; номер версии ещё не назначен. Реализация не начата. Места/строки исходного аудита относятся к срезу до форматирования. [Текущий пакет](../../release/2026-10-15-1.4.0-style-package.md).
