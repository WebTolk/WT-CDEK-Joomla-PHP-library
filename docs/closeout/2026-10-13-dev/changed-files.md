# Changed Files

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Files Changed

Машинный перечень changes.json в этом комплекте содержит полный product commit diff; он включает product/docs/evidence/.gitignore. Локальные build/context/log/cursor/tests .webtolk игнорируются Git.

## Reason Per File

PHP/JS собственные headers -> PF template; field/helper/service/assets -> neutral fields; Wtcdek -> unified route/input/CSRF; languages/manifests -> 1.4.0; docs/evidence -> воспроизводимость; .gitignore -> не публиковать raw Playwright runtime.

## Structural Impact

Добавлены Helper/Watchfield, DeliverypointsService, WatchfieldAssetItem и JS renderer/transport; один AJAX handler.

## Config Or Contract Changes

Все манифесты 1.4.0; локальный package config textExtensions=[js] и runtime excludes; POST action=deliverypoints. Снимок build config сохранён в комплекте.

## Follow-Up Required

Manual install/UI/provider verification; F1–F5; отдельно решить PF onboarding, если он нужен.
