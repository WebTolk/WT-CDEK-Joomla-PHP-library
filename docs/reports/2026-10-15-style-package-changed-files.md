# Joomla/PSR-12: changed files

## Summary

Из 40 PHP-файлов изменены 39; task services/provider.php уже соответствовал правилам. Ранее исправленный PHP 7.4 installer сохранён и включён в новую сборку. Функциональные задачи оператор перенёс на следующий релиз.

## Files Changed

- lib_webtolk_wtcdek/layouts/fields/tariffinfo.php
- lib_webtolk_wtcdek/script.php
- lib_webtolk_wtcdek/src/Cdek.php
- lib_webtolk_wtcdek/src/CdekClientException.php
- lib_webtolk_wtcdek/src/CdekRequest.php
- lib_webtolk_wtcdek/src/Entities/AbstractEntity.php
- lib_webtolk_wtcdek/src/Entities/CalculatorEntity.php
- lib_webtolk_wtcdek/src/Entities/CheckEntity.php
- lib_webtolk_wtcdek/src/Entities/DeliveryEntity.php
- lib_webtolk_wtcdek/src/Entities/DeliverypointsEntity.php
- lib_webtolk_wtcdek/src/Entities/IntakesEntity.php
- lib_webtolk_wtcdek/src/Entities/InternationalEntity.php
- lib_webtolk_wtcdek/src/Entities/LocationEntity.php
- lib_webtolk_wtcdek/src/Entities/OauthEntity.php
- lib_webtolk_wtcdek/src/Entities/OrdersEntity.php
- lib_webtolk_wtcdek/src/Entities/PassportEntity.php
- lib_webtolk_wtcdek/src/Entities/PaymentEntity.php
- lib_webtolk_wtcdek/src/Entities/PhotoDocumentEntity.php
- lib_webtolk_wtcdek/src/Entities/PrealertEntity.php
- lib_webtolk_wtcdek/src/Entities/PrintEntity.php
- lib_webtolk_wtcdek/src/Entities/registry.php
- lib_webtolk_wtcdek/src/Entities/ReverseEntity.php
- lib_webtolk_wtcdek/src/Entities/WebhooksEntity.php
- lib_webtolk_wtcdek/src/Fields/DeliverypointsField.php
- lib_webtolk_wtcdek/src/Fields/TariffinfoField.php
- lib_webtolk_wtcdek/src/Fields/TarifflistField.php
- lib_webtolk_wtcdek/src/Helper/Watchfield.php
- lib_webtolk_wtcdek/src/Interfaces/EntityInterface.php
- lib_webtolk_wtcdek/src/Service/DeliverypointsService.php
- lib_webtolk_wtcdek/src/Traits/CacheTrait.php
- lib_webtolk_wtcdek/src/Traits/LogTrait.php
- lib_webtolk_wtcdek/src/WebAsset/AssetItem/WatchfieldAssetItem.php
- plg_system_wtcdek/services/provider.php
- plg_system_wtcdek/src/Extension/Wtcdek.php
- plg_system_wtcdek/src/Field/AccountinfoField.php
- plg_system_wtcdek/src/Field/PlugininfoField.php
- plg_task_updatewtcdekdata/src/Extension/Updatewtcdekdata.php
- plg_task_updatewtcdekdata/src/Field/PlugininfoField.php
- script.php

## Reason Per File Or Area

PHP CS Fixer применил shared Joomla rules: PSR-12, четыре пробела, braces, ordered/unused/global imports, native function qualification. PHPCBF дополнил исправления, включая PHP layout, исключённый из shared CS Fixer Finder.

Вокруг _JEXEC guards применены узкие phpcs:disable/enable PSR1.Files.SideEffects по образцу Joomla core; глобальных исключений нет. Приватный UpdateWtCdekData приведён к updateWtCdekData вместе с TASKS_MAP. Длинный SVG path разбит на конкатенацию литералов без изменения значения. Смешанные CRLF/LF нормализованы к LF.

Собственные JS, vendor widget, SQL, INI, XML и shared configs/tools не менялись.

## Contract Impact

Публичные API, сигнатуры, AJAX actions/envelope, поля, фильтры и provider/cache поведение сохранены. Все 40 normalized AST совпали со снимком до форматирования. Сравнение нормализует import resolution/native function qualification, комментарии, LF/template whitespace, constant string concatenation и явно указанное private method/TASKS_MAP rename.

## Follow-Up Notes

Packager создал новый 1.4.0 ZIP без изменения исходников. Backup и source snapshot: E:/tmp/wtcdek-style-package-20261015. [Проверки](2026-10-15-style-package-verification-checklist.md), [пакет](../release/2026-10-15-1.4.0-style-package.md). PHPStan/type cleanup и функциональные задачи остаются на следующий релиз.
