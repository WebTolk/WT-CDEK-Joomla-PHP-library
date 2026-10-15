# Установщик библиотеки: PHP 7.4

## Scope

Оператор подтвердил сохранение Joomla 4 / PHP 7.4 и разрешил исправить установщик, если остальной код совместим. На исходном HEAD 39/40 PHP-файлов прошли lint PHP 7.4.33; единственный отказ — constructor promotion с readonly в lib_webtolk_wtcdek/script.php:50. Native field/AJAX fixtures на Joomla 4.4.14 / PHP 7.4 прошли 150 проверок.

## Invariants And Public Contracts

Joomla >= 4.2.7 и PHP >= 7.4 сохраняются. Namespace, public API, DI service, тип параметра конструктора, события, layouts и manifests не меняются. Минимальную версию PHP не повышать. Остальные находки аудита оформляются задачами, без реализации.

## Implementation Plan

1. В lib_webtolk_wtcdek/script.php объявить существующее private-свойство явно, без readonly; присвоить его в обычном конструкторе с прежним AdministratorApplication.
2. Проверить все 40 PHP-файлов реальными PHP 7.4 / 8.0 / 8.1 / 8.3, загрузить provider/InstallerScriptInterface на Joomla 4 / PHP 7.4 без выполнения установки.
3. Проверить изменённый файл PhpStorm; сохранить evidence и changed-files. Подтвердить отсутствие иных продуктовых изменений и пересборки ZIP.

## Limits

Lint и offline-контракты не доказывают live acceptance всех REST-операций или installer filesystem lifecycle. Joomla 4.2.7 отдельным runtime не проверялась; локальный core — 4.4.14. Сборка/установка/публикация в scope не входят.
