# Scope

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## In Scope

Документирование нейтральных полей, Watchfield WebAsset/helper, cookie fix, единого AJAX и сборки 1.4.0; commit/push и закрытие текущей сессии.

## Out Of Scope

Исправление F1–F5, новые операции СДЭК, сохранение настроек сайта, публикация release, подключение проекта к PF.

## Affected Areas

PHP поля/helper/service/system plugin; собственные JS/assets/layouts; RU/EN языки; манифесты; локальная конфигурация сборки; документация и артефакты.

## Non-Goals

Не объявлять dev checkpoint production-ready. Не выполнять повторную разработку вместо сохранения выполненного состояния.

## Risk Boundaries

Ручная установка финального 1.4.0 и реальный ответ СДЭК ещё не проверены. Четыре P2 F1–F4 и P3 F5 остаются открытыми; checkpoint dev не является публичным релизом.

## Required Artifacts

Все 17 типов из .webtolk/config/config.yaml required_by_stage, плюс policy domain-model/surface-map/invariants/contracts/verification/ADR, evidence index и handoff.

## Exit Criteria

Оба коммита отправлены в dev; комплект проходит проверку заполненности/ссылок; состояние остановлено по указанию оператора.
