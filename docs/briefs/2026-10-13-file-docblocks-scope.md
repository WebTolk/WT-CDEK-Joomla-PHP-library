# PHP/JS файловые док-блоки

## Scope / Plan

Оператор запросил нормальные файловые док-блоки по глобальным шаблонам Process Forge. Изменить первые docblocks всех 40 собственных PHP-файлов пакета и трёх wtcdek-*.js. Шаблон: E:/.agents/templates/files/PHP-doc-block-template/php-class-level-doc-bloc-template.md; правила адаптации: E:/.agents/templates/files/README.md. Путь подтверждён registries/templates.yaml рабочего места PF; проект не подключён к PF-native snapshot, миграция не выполняется.

## Invariants / Contracts

Тела файлов, комментарии классов/методов, namespace, алгоритмы и исторические @since сохраняются. Сторонние cdek-widget.es.js/cdek-widget.umd.js и d.ts не изменять. User Git index, ZIP, установленный сайт и глобальные шаблоны не менять.

## Implementation

Адаптировать описание, package/author/copyright/link из package.config.json, subpackage по Joomla element из manifest. Версия файлов — __DEPLOY_VERSION__ из глобального образца. Для JS использовать тот же метаданный заголовок; отдельного JS-образца реестр не предоставляет. Добавить textExtensions [js] в project build config, чтобы штатный сборщик обрабатывал новые JS теги/токены.

## Verification

Сравнить executable PHP tokens и JS body с pre-edit, а также байты вне первого docblock; PHP lint/Node syntax; теги и применённый template; проверка преобразования метаданных установленным Packager на изолированных копиях. Сборка/установка/browser не требуются для изменения комментариев.
