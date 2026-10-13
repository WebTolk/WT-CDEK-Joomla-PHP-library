# Повторный аудит: verification checklist

## Checks Run / Results

- [x] Проверено отсутствие .pf/AGENTS.md, прочитаны E:/.agents/AGENTS.md, code-assurance, существующие scope/context/implementation artifacts.
- [x] Serena отсутствует среди callable tools. PhpStorm inspections выполнены: Watchfield JS и четыре PHP класса, ошибок нет. Для source/core сравнения и тестов использован узкий shell.
- [x] Прочитаны все четыре класса в src/Fields, сервис, WebAssetItem, registry, layout, три JS и новый AJAX handler.
- [x] Восемь PHP lint passed, три node --check passed, git diff --check без дефектов whitespace.
- [x] Native offline core/event gates: Joomla 4.4.14 83; Joomla 5.4.7 84; Joomla 6.1.3 84. При отдельном запуске старого bootstrap присутствует предупреждение HTTP_HOST отсутствует в CLI; audit PHP probe задаёт synthetic server context и выполняется без него. Это недостаток fixture, не продуктового поля на сайте.
- [x] В Chrome воспроизведены retry loss, stale chained dependency, ignored reconfiguration/options refresh и пустой legacy layout.
- [x] Native PHP OptionsRule probe на Joomla 4/5/6 воспроизвёл отказ корректного нового Deliverypoints и публичного Tarifflist.
- [x] Сравнение SHA256 product-before: продуктовые файлы не изменены. Сайт, архивы, Git index не изменялись; отдельный тестовый Chrome закрыт.
- [x] Доказательства перенесены из tmp в постоянный evidence directory; сохранены исходные reproductions, результаты, screenshot и source snapshot/hash manifest.

## Checks Skipped

- Повторная установка ZIP/полный запуск старых 39 native E2E на joomla.local: в этом audit не переустанавливался пакет и не возвращалась удалённая fixture. Прежняя приёмка сохранена отдельно и не считается доказательством отсутствия новых дефектов.
- Реальные CDEK calls, создание отправлений, изменения настроек или БД: не требовались для reproductions и не выполнялись.
- Nested subform, реальное сохранение формы стороннего магазина, administrator browser path: дополнительных проверок в этом audit нет.
- Полный static analyzer/phpunit quality gate не настроен в проекте; IDE/lint/native probes не заменяют его.

## Open Risks / Ready for Delivery

**Полная готовность к публичному выпуску 1.4.0 не подтверждена.** Открыты F1–F4 (P2), F5 (P3). Перед выпуском закрыть state/lifecycle и server validation, определить BC для layout overrides, добавить regression coverage. Dev-пакет остаётся установленным и не публикуется.

## Process Forge / пауза

Оператор попросил сохранить артефакты «по .pf» и продолжить завтра. `pf_context` и `pf_project_initialization_status` возвращают `.pf/process-forge.yaml not found`; .pf отсутствует. Артефакты сохранены в действующем docs/.webtolk, без имитации PF state и без скрытого project-onboard. PF-native запись недоступна до подключения проекта. Отдельный [handoff](../../.webtolk/continuations/2026-10-12-fields-audit.md) фиксирует это ограничение и точку продолжения. Новая работа сегодня не запускается.
