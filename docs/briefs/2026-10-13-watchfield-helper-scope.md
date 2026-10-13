# Watchfield: перенос PHP-помощника

## Scope

Авторизация оператора: перенести Watchfield из Fields в Helper, обновить обращения и документацию. Watchfield остаётся общим WebAsset; отдельный type="watchfield" не вводится.

## Project Invariants

Сохранить алгоритмы помощника, XML-атрибуты, имена WebAsset, JS, рендеринг и зависимости полей. Не изменять пользовательский Git index, исторические отчёты/evidence, ZIP или установленный стенд. Замечания F1–F5 повторного аудита остаются открытыми; их исправление не входит в перенос.

## Public Contracts

PHP-класс dev-версии получает имя Webtolk\Cdekapi\Helper\Watchfield вместо Webtolk\Cdekapi\Fields\Watchfield. Обновить импорты TariffinfoField/DeliverypointsField и пример собственного потребителя. Stable 1.3.2 не содержит этого нового помощника. Никакого alias отдельного поля не добавлять.

## Implementation Plan

1. Перенести src/Fields/Watchfield.php в src/Helper/Watchfield.php, изменить namespace и пояснение класса.
2. Добавить явные импорты в оба поля. Обновить namespace и описание поведения в документации.
3. Проверить PSR-4 загрузку существующими offline Joomla Form checks, PHP lint, IDE inspections, отсутствие старых рабочих ссылок и сохранность index/evidence/ZIP.

Manifest уже включает весь src и namespace Webtolk\Cdekapi; отдельное объявление Helper не требуется. Browser/install/release проверки не применяются к этому переносу, поскольку JS и HTML-контракт остаются прежними.
