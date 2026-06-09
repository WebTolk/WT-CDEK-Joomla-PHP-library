# Test Cases: CDEK GET Query Encoding

## TC-01 PHP Syntax

- Command: `php -l lib_webtolk_wtcdek\src\Entities\LocationEntity.php`
- Result: passed.
- Evidence: `No syntax errors detected`.

## TC-02 Joomla Query Encoding Model

- Command: `php -r '$q=["city"=>urlencode("Нижний Новгород"),"name"=>urlencode("Новый Уренгой"),"country_codes"=>"RU,KZ"]; echo urldecode(http_build_query($q, "", "&")), PHP_EOL;'`
- Result: passed.
- Evidence: output kept encoded city/name values and preserved `country_codes=RU,KZ`.

## TC-03 Release Package Build

- Command: `php E:\.agents\tools\phing-packager\phing-latest.phar -f phing.xml "3. Package release"`
- Result: passed.
- Evidence: `.packages/WT Cdek library package_1.3.2.zip` created.

## TC-04 Release Package Contents

- Commands:
  - `tar -tf ".packages\WT Cdek library package_1.3.2.zip"`
  - `tar -xOf ".packages\WT Cdek library package_1.3.2.zip" pkg_lib_wtcdek.xml`
  - `tar -xOf ".packages\WT Cdek library package_1.3.2.zip" lib_webtolk_wtcdek/Cdekapi.xml`
- Result: passed after package exclude correction.
- Evidence: no `.webtolk/` or `.apidoc.cdek.ru/`; package and library manifests contain version `1.3.2` and creation date `09.06.2026`.

## TC-05 Joomla CLI Install

- Commands:
  - `php cli\joomla.php extension:install --path="...\WT Cdek library package_1.3.2.zip"`
  - `php cli\joomla.php extension:install --path="...\WT-Cdek-library-package-1.3.2.zip" -vvv`
- Result: blocked.
- Evidence:
  - First path failed in `InstallerHelper::cleanupInstall()` with `Joomla\Filesystem\File::delete: Failed deleting pkg_lib_wtcdek.xml`.
  - Joomla extension records remained at `1.3.1`.

## TC-06 Runtime Smoke on `joomla.local`

- Setup: backed up installed `LocationEntity.php`, then copied the changed file into `E:\OSPanel\home\joomla.local\public\libraries\Webtolk\Cdekapi\src\Entities\LocationEntity.php`.
- Command: `php .webtolk\tmp\cdek-location-smoke.php`
- Result: passed.
- Evidence:
  - `getCities("Нижний Новгород")` returned CDEK city code `414`.
  - `suggestCities("Новый Уренгой")` returned a matching CDEK suggestion for Russia.
