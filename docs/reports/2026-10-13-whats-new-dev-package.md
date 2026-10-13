# WT CDEK 1.4.0-dev: What’s New и пакет для ручной проверки

## Summary / Changed Files

По инструкции оператора обновлены PKG_LIB_WTCDEK_WHATS_NEW в language/ru-RU/pkg_lib_wtcdek.sys.ini и language/en-GB/pkg_lib_wtcdek.sys.ini. Тексты описывают Deliverypoints, Watchfield WebAsset, Tariffinfo и новый PHP namespace Helper. Исходные переводы строк INI сохранены. Остальной продукт в этом шаге не менялся.

## Build

Штатная команда из корня проекта:

`php E:/.agents/tools/phing-packager/phing-latest.phar -f phing.xml "3. Package release"`

Использован существующий bridge phing.xml и .webtolk/build/package.config.json. Target 3 сохраняет настроенную версию 1.4.0-dev; target Package dev выдал бы 1.4.1-dev. Замена метаданных выполнялась только в staging; 67 source/config hashes после сборки совпадают с pre-build.

PF initialization_status подтвердил отсутствие .pf/process-forge.yaml. Это штатная сборка общим Phing packager через действующий project bridge, без PF-native initialization/ledger. Подключение проекта к PF в этом шаге не выполнялось.

## Output

- Пакет: .packages/WT Cdek library package_1.4.0-dev.zip.
- Размер: 381977 bytes; 66 entries.
- SHA256: 3c36a192718027de19a91c036b6a8305ec04466a2da2a7e05e1740427c903fc1.
- Предыдущий dev ZIP сохранён: .webtolk/tmp/package-20261013/WT Cdek library package_1.4.0-dev.before-20261013-121057.zip; SHA256 ea3ca0fc0091d9ce6c5dacc51e7f76bc7bb6ffd456d46bf05a71a835c741149c.
- Public 1.3.2 ZIP сохранён; SHA256 e36a0ec075990ce914942b47c650e14870a1802f8ff6fa71a77fa92c4bfe9d88.

## Verification Checklist / Findings

- ZIP integrity passed; служебные docs, flow, IDE, Git и донорские файлы отсутствуют.
- Четыре manifest имеют 1.4.0-dev; deploy-токенов нет.
- Helper/Watchfield.php присутствует; Fields/Watchfield.php отсутствует; оба поля импортируют Helper.
- RU/EN INI успешно разобраны PHP parse_ini_file; packaged files совпадают с source; What’s New содержит версию 1.4.0-dev и актуальные изменения.
- PHP lint 40/40; executable PHP bodies совпадают с source после разрешённых замен метаданных. JS syntax 3/3; packaged JS совпадают с source.
- Финальная пересборка исправляет только сохранение исходных переводов строк INI; все другие entries byte-identical к первому проверенному ZIP.
- git diff --check passed. После сборки index имел исходный SHA256 entries FB79D4B534D87D5E25CDDEA6464CD76E04873DF7C858EED0A8ECBED2906C2B9B. При финальном readback обнаружено последующее обновление index: Helper и отчёты переноса теперь staged; SHA256 D91F74744AB99CA872BF0F886025C6DE413DD83EAFAC9941645830C77E6F5D67. Агент не выполнял git add/reset/stage; новое состояние сохранено без вмешательства. Хеш пакета не изменился.
- Machine evidence: .webtolk/tmp/package-20261013/package-audit.json и source-before.json.

## Migration / Manual Handoff

Пакет передан для ручной установки/проверки оператором. PHP-потребители нового dev-помощника используют Webtolk\Cdekapi\Helper\Watchfield. Пользовательские overrides Tariffinfo требуют $watchfield_attributes на контейнере; сообщение об этом включено в What’s New. Предыдущий dev ZIP доступен для возврата к прежней редакции; новой site/DB миграции в этом шаге нет.

F1–F5 повторного аудита остаются открытыми. Сборка не закрывает их и не является повторной native browser/save приёмкой. Автоматическая установка, публикация, изменения стенда/CDEK и commit/stage не выполнялись; ручная проверка остаётся за оператором.
