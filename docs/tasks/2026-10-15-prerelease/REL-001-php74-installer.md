# REL-001: Сохранить PHP 7.4 в установщике библиотеки

Приоритет: **P1**. Статус: **Выполнено в исходниках и включено в пакет 1.4.0**.

## Подтверждение

Подтверждено реальными PHP 7.4/8.0; исправление разрешено оператором.

## Места в коде

- `lib_webtolk_wtcdek/script.php:49`
- `script.php:69`

## Проблема

Конструктор с promotion и readonly требовал PHP 8.1 при официальном минимуме PHP 7.4. Конструкция уже присутствовала в main.

## Требуемое изменение

Выполнено: явно объявлено private typed-свойство, конструктор принимает прежний AdministratorApplication и присваивает его свойству. Минимальные версии и API сохранены.

## Критерии приёмки

- [x] 40/40 файлов проходят lint на PHP 7.4, 8.0, 8.1 и 8.3 — выполнено.
- [x] Provider и InstallerScriptInterface загружаются на Joomla 4.4.14/PHP 7.4, Joomla 5.4.7 и 6.1.3 — выполнено.
- [x] PhpStorm: ERROR 0; иных продуктовых изменений нет — выполнено.
- [x] Пересобран ZIP 1.4.0 с исправленным installer, проверены executable tokens и PHP 7.4 lint.
- [ ] Полная установка/обновление/удаление на реальном стенде — отдельная приёмка, не проверка синтаксиса.

## Доказательства

- [post-fix-php-7.4-lint.json](../../reports/2026-10-15-prerelease-evidence/post-fix-php-7.4-lint.json)
- [post-fix-source.json](../../reports/2026-10-15-prerelease-evidence/post-fix-source.json)
- [installer-smoke-j4.stdout](../../reports/2026-10-15-prerelease-evidence/installer-smoke-j4.stdout)
- [phpstorm-installer-post-fix.json](../../reports/2026-10-15-prerelease-evidence/phpstorm-installer-post-fix.json)

## Ограничения

Сохранить Joomla 4.2.7+ и PHP 7.4. Не запускать реальные запросы CDEK, менять стенд, выпускать ZIP/релиз или исправлять соседние задачи без отдельного scope. Этот файл — проектная задача; native PF ledger отсутствует, проект работает через .webtolk.
