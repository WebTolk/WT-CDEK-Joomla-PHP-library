# Watchfield Helper: changed files

## Summary

PHP-помощник общего Watchfield WebAsset перенесён из Fields в Helper по указанию оператора. Алгоритмы и атрибуты поведения сохраняются.

## Files Changed

- lib_webtolk_wtcdek/src/Fields/Watchfield.php → lib_webtolk_wtcdek/src/Helper/Watchfield.php: перенос и namespace Helper.
- lib_webtolk_wtcdek/src/Fields/{DeliverypointsField,TariffinfoField}.php: явные импорты помощника.
- .docs/fields/watchfield.md: новый namespace и пояснение WebAsset/атрибута вместо отдельного типа поля.
- .docs/index.md: ссылка на общее поведение отделена от списка типов полей.
- docs/briefs/2026-10-13-watchfield-helper-scope.md: авторизация, инварианты, контракты и план переноса.

## Reason Per File

Помощник не наследует Joomla FormField и не должен находиться среди типов полей. Документация и импорты должны совпадать с PSR-4 расположением.

## Structural Impact

Новая папка src/Helper автоматически входит в установленную библиотеку через manifest folder src и namespace Webtolk\Cdekapi. Manifest и реестр WebAsset не требуют изменений.

## Config Or Contract Changes

Только PHP-имя нового dev-помощника: Webtolk\Cdekapi\Helper\Watchfield. XML, имена ассетов и событий сохраняются. PHP-пример собственного потребителя обновлён.

## Follow-Up Required

Проверки переноса записываются в отдельном verification report. Замечания F1–F5 вчерашнего аудита остаются открытыми. Новая сборка/установка/публикация в этом шаге не выполняется; прежний dev ZIP и установленный стенд содержат прежнее расположение помощника. Исторические evidence и Git index сохраняются.
