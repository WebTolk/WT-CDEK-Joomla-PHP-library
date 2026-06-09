# Change Summary: CDEK GET Query Encoding

## Scope

Restore the old `v.1.3.0` behavior for CDEK city lookups with spaces without changing the shared `CdekRequest` transport layer.

## Implementation

- `LocationEntity::getCities()` now applies `urlencode()` to a non-empty `city` option.
- `LocationEntity::suggestCities()` now applies `urlencode()` to the `name` query option.
- `country_codes` normalization remains unchanged, preserving comma-separated country code behavior.

## Release

- Release version input is `1.3.2` in `.webtolk/build/package.config.json`.
- Release ZIP was created by the shared Phing packager:
  - `.packages/WT Cdek library package_1.3.2.zip`
- Package inspection confirmed:
  - no `.webtolk/`
  - no `.apidoc.cdek.ru/`
  - `pkg_lib_wtcdek.xml` version `1.3.2`
  - `lib_webtolk_wtcdek/Cdekapi.xml` version `1.3.2`

## Stand Notes

Joomla CLI package installation on `joomla.local` failed in Joomla cleanup/install paths. Runtime smoke was still executed on `joomla.local` by applying a test-only overlay of the changed `LocationEntity.php` to the installed library path after backing up the original file.
