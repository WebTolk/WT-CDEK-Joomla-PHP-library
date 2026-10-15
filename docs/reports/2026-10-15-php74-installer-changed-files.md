# PHP 7.4 installer: changed files

## Summary

По прямому разрешению оператора устранён единственный найденный parser-blocker PHP 7.4/8.0. Joomla 4 / PHP 7.4 остаются официальным минимумом; новые возможности PHP 8 не добавлены.

## Files Changed

- **Продукт:** lib_webtolk_wtcdek/script.php — явно объявлен прежний private AdministratorApplication, promotion/readonly заменены обычным конструктором и присваиванием.
- **Документы:** 2026-10-15-php74-installer-scope.md, этот changed-files, prerelease findings/checklist, docs/tasks/2026-10-15-prerelease/.
- **Evidence:** docs/reports/2026-10-15-prerelease-evidence/ — результаты инструментов, безопасные offline runners/probes, исходные и конечные SHA256.
- **Локальная очистка:** четыре ignored временных дерева перенесены вне checkout; точный mapping cleanup.json. Shared configs/tools не редактировались.

## Reason Per File Or Area

Явное private typed-свойство поддерживается PHP 7.4, сохраняет тип/видимость и DI-передачу приложения. Свойство по-прежнему не читается — существующая IDE рекомендация не расширяла эту узкую правку.

## Contract Impact

Конструктор принимает прежний AdministratorApplication. Namespace, InstallerScriptInterface, lifecycle hooks, минимальные версии, manifests, CDEK API и JS не менялись. Новая архитектура/ADR не потребовались: исправление возвращает реализацию к уже принятому контракту.

## Validation

Все 40 PHP проходят lint на 7.4/8.0/8.1/8.3; provider/interface/injected application smoke на Joomla 4/5/6 passed. PhpStorm ERROR 0. Product hash diff содержит ровно один файл. Проверено git diff --check. Остальные native 540 checks относятся к неизменённым полям/AJAX и прошли в этом аудите. Открытые failures PHPUnit по независимым дефектам не закрывались.

## Follow-Up Notes

ZIP не пересобран, существующий пакет не содержит правку. Полная установка/обновление/удаление и provider acceptance ещё нужны после остальных утверждённых исправлений. Git commit/push и изменение стенда не выполнялись.
