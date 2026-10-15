# Предрелизный аудит WT CDEK, 15.10.2026

**Вердикт: публичный выпуск требует исправлений.** Сохранены Joomla 4 и PHP 7.4. Единственная реализованная продуктовая правка — совместимый с PHP 7.4 конструктор установщика. Остальные находки оформлены [отдельными задачами](../tasks/2026-10-15-prerelease/index.md).

## Scope And Sources

Проверен checkout dev, исходный HEAD 38e43d8515d8e563ee1986756db92e6f841175e7: 40 PHP-файлов пакета, три собственных JS, manifests, API transport/cache, Joomla DI/events/Form/WAM, installer и task plugin. Сторонние bundles виджета не рефакторились и не подвергались полному аудиту поставщика.

Прочитаны E:/.agents/AGENTS.md, локальные skills code-assurance/artifact-policy/implementation, реестр tools.yaml и E:/.agents/toolchains/php/toolchain.json, Joomla platform contract и локальные architecture rules. Использованы реальные core sources Joomla 4.4.14, 5.4.7 и установленный 6.1.3. Советы из правил для новых PHP/Joomla применялись с учётом обязательного PHP 7.4; readonly/promotion/typed constants не предлагаются как универсальные quick fixes.

Проект по-прежнему без .pf/AGENTS.md и .pf/process-forge.yaml. Нативный PF ledger не создан. Задачи и evidence сохранены в действующей структуре проекта; shared PHP QA взят из реестра PF. Serena не exposed, поэтому semantic fallback — PhpStorm inspections и узкое чтение исходников/CLI. Нового onboarding и глобальных изменений нет.

## Findings / Severity / Evidence / Recommended Action

### REL-001 — P1, исправлено в исходниках: PHP 7.4 installer

При официальном minimumPhp=7.4 библиотечный installer содержал constructor promotion с readonly (требование PHP 8.1). На реальных PHP 7.4.33/8.0 lint падал только на этом файле; main содержит тот же дефект. Это не доказательство регрессии последних Watchfield-изменений.

По разрешению оператора в [script.php](../../lib_webtolk_wtcdek/script.php:49) свойство объявлено явно и присваивается обычным конструктором. Поддержка Joomla 4.2.7+/PHP 7.4 не повышена. После правки 40/40 файлов проходят lint на PHP 7.4/8.0/8.1/8.3. Provider/interface/application injection проверены на Joomla 4/5/6 без выполнения установки. [Задача](../tasks/2026-10-15-prerelease/REL-001-php74-installer.md).

**Blocked or Not:** исходный blocker снят для исходников; имеющийся ZIP не пересобран и содержит прежний installer. Lint/provider smoke не доказывают все REST-операции или файловый lifecycle установки.

### REL-002 — P1, открыто: общие token/cache keys

[CdekRequest.php:365](../../lib_webtolk_wtcdek/src/CdekRequest.php:365) и [store:465](../../lib_webtolk_wtcdek/src/CdekRequest.php:465) используют один wt_cdek без аккаунта/окружения. PHPUnit записывает два production-аккаунта и sandbox, получает одну запись вместо трёх. При поддерживаемых явных credentials это позволяет использовать токен другого контекста. В CalculatorEntity:36 также общий calculator_alltariffs для договорного каталога.

Нужен namespace аккаунта/среды для всех get/store/remove и договорных данных. Не помещать открытые секреты в cache keys. [Задача](../tasks/2026-10-15-prerelease/REL-002-cache-account-isolation.md).

**Blocked or Not:** блокирует корректную поддержку нескольких credentials/окружений. Реальные операции чужого аккаунта не выполнялись. Основной token-дефект есть в main.

### REL-003 — P1, открыто: scheduler очищает данные до успеха

[Updatewtcdekdata.php:342](../../plg_task_updatewtcdekdata/src/Extension/Updatewtcdekdata.php:342) выполняет TRUNCATE до получения ПВЗ. Memory-only PHPUnit фиксирует TRUNCATE, затем подставной error envelope вызывает TypeError в [строке 430](../../plg_task_updatewtcdekdata/src/Extension/Updatewtcdekdata.php:430). Тот же порядок очистки применён к регионам/городам (:145/:258).

Нужно проверять envelope/элементы и заменять завершённый набор только после успешной загрузки/записи всех страниц. MySQL TRUNCATE нельзя считать откатываемой частью обычной транзакции. [Задача](../tasks/2026-10-15-prerelease/REL-003-scheduler-safe-refresh.md).

**Blocked or Not:** блокирует безопасное обновление справочников при отказе провайдера. Реальная БД не очищалась; дефект воспроизведён на mock. Присутствует в main.

### REL-004 — P2, открыто: ERROR превращается в INFO

[LogTrait.php:57](../../lib_webtolk_wtcdek/src/Traits/LogTrait.php:57) передаёт строку 'Log::ERROR' вместо числового Log::ERROR. Настоящий Joomla LogEntry нормализует её к INFO=64 вместо ERROR=8; подтверждено PHPUnit с memory logger. Нужен явный mapping при сохранении публичного string API. [Задача](../tasks/2026-10-15-prerelease/REL-004-joomla-log-priority.md).

**Blocked or Not:** ошибка диагностики и фильтрации журнала, присутствующая в main; обычные успешные запросы не блокирует.

### REL-005 — P2, открыто: installer игнорирует false от layouts helpers

[uninstall](../../lib_webtolk_wtcdek/script.php:88) и [postflight](../../lib_webtolk_wtcdek/script.php:136) возвращают true независимо от removeLayouts/parseLayouts. PhpStorm отмечает неиспользованный return. Нужно передавать результат/ошибку жизненного цикла, сохранив отдельную политику необязательного отсутствия layout. Не превращать методы в void quick fix ради исчезновения предупреждения. [Задача](../tasks/2026-10-15-prerelease/REL-005-installer-error-propagation.md).

**Blocked or Not:** источник проверен; реальный failure copy/delete в этом аудите не выполнялся. Риск ложного успеха установки/удаления требует адресных тестов.

### REL-006 — P2, открыто: AccountinfoField не проверяет форму ответа

[AccountinfoField.php:46](../../plg_system_wtcdek/src/Field/AccountinfoField.php:46) читает response[0]['code'] при возможных [] или error envelope. Нужна обработка отсутствующих ключей до отображения успеха, без PHP notices/warnings. [Задача](../tasks/2026-10-15-prerelease/REL-006-accountinfo-error-response.md).

**Blocked or Not:** источник проверен; отказ реального CDEK при открытии настроек не воспроизводился. Успешное подключение этой находкой не опровергается.

### REL-007 — P3, открыто: style/type/documentation cleanup

Полный PHPCS-срез до узкой правки: **4915 style errors, 60 warnings**, 39/40 файлов, 4897 fixable. 4080 сообщений — табы в отступах/выравнивании; также braces, imports, EOL и несколько statements на строке. Это нарушения выбранного Joomla PSR-12 профиля, не 4915 runtime-дефектов. CS Fixer dry-run предлагает 38 файлов; layouts исключены Finder согласно shared policy.

PHPStan level 5 выдал **32 сообщения**, включая реальные type/doc ошибки и контекстные ограничения Joomla. Нужен отдельный cleanup, без массового подавления и без применения PHP 8+ подсказок к PHP 7.4. [Задача](../tasks/2026-10-15-prerelease/REL-007-php-quality-cleanup.md).

В частности, исправить неверно разрешающийся PHPDoc WebAssetRegistry, @return mixed при array, TASKS_MAP/@var, пустые getLabel с обещанным string, unused imports/private helpers. Удалять UpdateWtCdekData как unused нельзя: вызов идёт через TASKS_MAP.

**Blocked or Not:** единый профиль сейчас не проходит; функциональные blockers оцениваются отдельно. Не исправлялось в audit scope.

### REL-008 — P3, открыто: deprecated CMS HttpFactory

[CdekRequest.php:217](../../lib_webtolk_wtcdek/src/CdekRequest.php:217) использует Joomla CMS HttpFactory. PhpStorm и core 6.1.3 подтверждают deprecated since 6.0 / removal 8.0, с направлением на Joomla Framework HttpFactory. Миграция должна сохранить минимальную Joomla 4.2.7. [Задача](../tasks/2026-10-15-prerelease/REL-008-httpfactory-deprecation.md).

**Blocked or Not:** само предупреждение не блокирует поддерживаемые Joomla 4/5/6. Это будущая совместимость, не причина повышения минимальных требований.

## Что означают результаты PhpStorm/PHPStan

PhpStorm проверил все 40 PHP-файлов на уровне WEAK_WARNING: **ERROR 0, WARNING 15, WEAK_WARNING 433, timeout 0**. Это сырой срез, собранный в ходе аудита и очистки, не итоговый счёт после всех будущих fixes. Девять WARNING — отсутствие настроенного SQL data source, не доказанный SQL-дефект. Остальные — неверный PHPDoc namespace, missing return, alt и unused property. Нулевой ERROR не обнаруживает несовместимость 7.4, когда IDE language level стоит 8.3; именно поэтому использованы реальные CLI 7.4/8.0.

После удаления временных копий повторная инспекция Cdek.php сократилась с 52 до 7 сообщений. Остаточные дубли связаны также с сохранённой документацией и офлайн-fixtures; их нельзя приравнивать к дублированию в установленном пакете.

В PHPStan не считать автоматически runtime-дефектами:

- layout-переменную displayData, предоставляемую Joomla renderer;
- id/name/value FormField, доступные через __get;
- getDocument на широком application interface без уточнения web-контекста;
- динамические TASKS_MAP/method calls;
- три сообщения implode: DatabaseInterface::quote документирует string, но реализация DatabaseDriver возвращает массив для массива.

Нужна адресная типизация/framework stubs, а не assert/@var, скрывающие ошибку, и не глобальный ignore. Первоначально общий CS Fixer config не запускался из-за Finder, привязанного к отсутствующему config/administrator. Для dry-run сохранён тот же shared rules array, заменён только Finder в evidence/fixer-scope.php; глобальный config не изменён.

## Прежние задачи F1–F5 и ручная приёмка

Оператор 15.10 подтвердил работу Watchfield. Это учитывается как ручная проверка обычного сценария; сведения о старых пограничных сценариях сохранены отдельно. Новый browser-прогон этих сценариев в этом аудите не выполнялся.

| Задача | Что требуется | Приёмка |
| --- | --- | --- |
| [F1](../tasks/2026-10-15-prerelease/F1-watchfield-retry-selection.md) | Повторить ошибку первого fetch и scan того же адреса; отделить retry от смены адреса | Saved PVZ не теряется при retry; реальная смена адреса очищает выбор |
| [F2](../tasks/2026-10-15-prerelease/F2-watchfield-dependent-chain.md) | Проверить цепь город → ПВЗ → consumer; уведомлять о programmatic value changes | Нет stale значения, лишних запросов и циклов |
| [F3](../tasks/2026-10-15-prerelease/F3-tariffinfo-layout-bc.md) | Сохранить работу старого Tariffinfo override | Старый публичный layout работает без переписывания |
| [F4](../tasks/2026-10-15-prerelease/F4-dynamic-options-validation.md) | Определить server validation ПВЗ/тарифа и поведение при отказе API | Допустимое новое значение проходит submit, недопустимое отвергается |
| [F5](../tasks/2026-10-15-prerelease/F5-watchfield-reconfiguration-contract.md) | Выбрать reconfigure или immutable target | Документированный lifecycle; обычный static XML не блокируется |

Это пять отдельных задач с историческими evidence от 12.10, не новые закрытые/повторно подтверждённые браузером результаты.

## Остаточные проверки перед выпуском

Сначала REL-002/REL-003 и остальные выбранные fixes, затем адресная regression assurance. После утверждённых исправлений потребуется новая сборка: нынешний ZIP 1.4.0 SHA256 2e17b506ffa953f1f6d86d38175116bf5f3b2b9b2f19d3cd2c7dc0c16e2c2982 не изменён и не содержит исправленный installer. Полная установка/обновление/удаление на PHP 7.4/Joomla 4, текущих Joomla 5/6, реальные provider acceptance и новая browser acceptance в этом запуске не выполнялись.

Полный [checklist](2026-10-15-prerelease-verification-checklist.md), [changed-files](2026-10-15-php74-installer-changed-files.md), [машинная сводка](2026-10-15-prerelease-evidence/summary.json), [задачи](../tasks/2026-10-15-prerelease/index.md).
