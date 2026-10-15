# Joomla/PSR-12 formatting and package scope

## Objective

Оператор разрешил применить PHP quality tools, привести исходники к Joomla/PSR-12 и собрать штатным PF packager. Найденные функциональные дефекты отложены оператором на следующий релиз; это supersedes прежнюю рекомендацию сначала исправить их. Поддержка Joomla 4/PHP 7.4 сохраняется.

## Scope And Invariants

40 PHP-файлов продукта: root installer, библиотека, system/task plugins и PHP layout. Автоматический formatter следует shared Joomla rules; PHPCS/PHPCBF дополняют PSR-12 checks. Для _JEXEC guard допустима только узкая Joomla-core annotation о side effects, без глобального исключения правила. Не исправлять token cache, scheduler, logging priority, field validation/lifecycle, provider flow или публичные сигнатуры. Собственные JS/сторонний widget не меняются. Установщик уже исправлен для PHP 7.4 в предыдущем шаге и входит в пакет.

## Implementation Plan

1. Сохранить полный source snapshot и предыдущий ZIP с SHA256 вне project tree.
2. Применить общий PHP CS Fixer rules array с Finder для продукта; PHP template обработать PHPCBF согласно shared exclusion у Fixer.
3. Проверить остаточный PHPCS вывод; исправить только форматирование/размещение header/use/guard и допустимые Joomla-core annotations. Убедиться в идемпотентности formatter.
4. Проверить lint PHP 7.4/8.x, native Joomla 4/5/6 fixtures, PhpStorm, PHPStan drift и публичные сигнатуры/данные. Открытые функциональные tests остаются failures следующего релиза.
5. Штатная команда: php E:/.agents/tools/phing-packager/phing-latest.phar -f phing.xml "3. Package release". Активная config: .webtolk/build/package.config.json, версия 1.4.0. Никакого version bump, установки, публикации или commit/push.
6. Проверить ZIP: состав/исключения, версии, deploy tokens, PHP 7.4, executable tokens относительно исходников, неизменность исходников после packager. Записать новый digest и continuation.

## Delivery Boundary

Результат — локальная сборка по запросу оператора. Открытые функциональные дефекты не скрываются и не закрываются этим форматированием; native PF ledger отсутствует, используется действующая .webtolk. Shared tools/configs не меняются.
