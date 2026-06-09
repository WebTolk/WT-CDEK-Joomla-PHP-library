# Runtime Verification Report: `joomla.local`

## Environment

- Joomla root: `E:\OSPanel\home\joomla.local\public`
- PHP: `E:\OSPanel\modules\PHP-8.3\php.exe`, PHP `8.3.30`
- Library path: `E:\OSPanel\home\joomla.local\public\libraries\Webtolk\Cdekapi`

## Installation Attempt

- Before install, Joomla CLI listed:
  - `WT Cdek library package` version `1.3.1`
  - `WebTolk Cdekapi library` version `1.3.1`
- Installing release ZIP through Joomla CLI failed:
  - `Joomla\Filesystem\File::delete: Failed deleting pkg_lib_wtcdek.xml`
  - Stack trace points to `InstallerHelper::cleanupInstall()`.
- Installing from an extracted directory also failed with `Unable to install extension`.
- Follow-up root-cause investigation confirmed this was a sandbox restriction on PHP `unlink()`, not a package defect.
- Running the same Joomla CLI install outside sandbox succeeded and updated the stand to `1.3.2`.

## Test Overlay

- Backed up installed file:
  - `.webtolk/tmp/joomla-local-backup/LocationEntity.1.3.1.php`
- Copied changed file to:
  - `E:\OSPanel\home\joomla.local\public\libraries\Webtolk\Cdekapi\src\Entities\LocationEntity.php`
- Verified installed file contains `urlencode()` for `city` and `name`.

## Smoke Result

- Script: `.webtolk/tmp/cdek-location-smoke.php`
- Result: passed.
- `getCities("Нижний Новгород")` returned:
  - `code`: `414`
  - `city`: `Нижний Новгород`
  - `country_code`: `RU`
- `suggestCities("Новый Уренгой", "RU")` returned:
  - `full_name`: `Новый Уренгой, городской округ Новый Уренгой, Ямало-Ненецкий автономный округ, Россия`
  - `country_code`: `RU`

## Browser Verification

Not applicable for this library-level CDEK API fix. The verification target is backend GET query behavior, so the runtime proof used Joomla CLI bootstrap and CDEK test API calls.

## Final Stand State

- `WT Cdek library package`: `1.3.2`
- `WebTolk Cdekapi library`: `1.3.2`
- `System - WT CDEK`: `1.3.2`
- `PLG_UPDATEWTCDEKDATA`: `1.3.2`
