# WT CDEK: сохранённые артефакты и продолжение

Оператор остановил работу 12.10.2026 до продолжения завтра. Исправления после повторного аудита не запускались.

- [Передача задачи / точка продолжения](../../.webtolk/continuations/2026-10-12-fields-audit.md)
- [Повторный аудит: четыре P2 и ограничение P3](./2026-10-12-fields-reaudit-review-findings.md)
- [Checklist и готовность](./2026-10-12-fields-reaudit-verification-checklist.md)
- [Браузерные reproductions](./2026-10-12-fields-reaudit-browser-verification-report.md)
- [Полный evidence manifest](./2026-10-12-fields-reaudit-evidence/manifest.json): исходники probes, JSON результатов, screenshot, 12 файлов code snapshot, baseline hashes, checks, контекст на паузе.
- [Ограничение PF-сохранения](./2026-10-12-fields-reaudit-evidence/pf-save-status.json): проект не подключён, .pf/process-forge.yaml отсутствует; сохранено в действующем docs/.webtolk без имитации PF состояния.
- [Предыдущая native приёмка 1.4.0-dev](./2026-10-12-watchfield-140-dev-verification.md), [план](./2026-10-12-watchfield-140-dev-implementation-plan.md), [scope](../briefs/2026-10-12-watchfield-140-dev-scope.md), [review](./2026-10-12-watchfield-140-dev-review-findings.md).
- [Документация полей](../../.docs/index.md), [release notes 1.4.0-dev](../release/2026-10-12-1.4.0-dev.md).
- [Проверка fias_guid](./2026-10-12-cdek-fias-guid-deprecation-check.md).
- [Публичный релиз 1.3.2](./2026-10-12-github-1.3.2-release.md), [donor provenance](./2026-10-09-external-fields-copy-manifest.json).

Все предыдущие отчёты и локальные native screenshots/JSON/backups остаются на своих местах. Новые evidence лежат в постоянной reports-директории и не зависят только от tmp. Продукт, dev/stable ZIP, установленная Joomla и пользовательский index в этом audit не изменены. Прежние 39 native E2E не закрывают новые findings; при продолжении ориентироваться на повторный audit.
