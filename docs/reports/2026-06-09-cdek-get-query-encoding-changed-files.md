# Changed Files: CDEK GET Query Encoding

- `lib_webtolk_wtcdek/src/Entities/LocationEntity.php`
  - Added `urlencode()` import.
  - Encoded `getCities()` `city` option before passing options to `CdekRequest`.
  - Encoded `suggestCities()` `name` option before passing options to `CdekRequest`.

- `.webtolk/build/package.config.json`
  - Set release input version to `1.3.2`.
  - Added `.webtolk/` and `.apidoc.cdek.ru/` to package excludes after release ZIP inspection showed those working directories entering the archive.
  - This is builder configuration, not a manual source-manifest version bump.

- `.packages/WT Cdek library package_1.3.2.zip`
  - Rebuilt release package via shared Phing packager.

- `.webtolk/tmp/cdek-location-smoke.php`
  - Temporary Joomla runtime smoke script for `joomla.local`.

Notes:

- Existing `.gitignore` modification is pre-existing migration work and not part of the CDEK functional fix.
- Source XML/docblock versions were not manually bumped; package metadata version `1.3.2` was applied by the packager in the ZIP.
