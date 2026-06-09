# Test Plan: CDEK GET Query Encoding

## Static Checks

- Lint changed PHP file.
- Confirm `Joomla\Uri\Uri::setQuery()` compatible encoded query output using `urldecode(http_build_query(...))`.
- Confirm package config JSON is readable and release version is consumed by the packager.

## Package Checks

- Build release package with shared Phing packager.
- Inspect ZIP contents for accidental working directories.
- Inspect package and library manifests inside ZIP for version/date metadata.

## Runtime Checks on `joomla.local`

- Attempt normal Joomla CLI extension installation of the release ZIP.
- If CLI install is blocked by local installer cleanup, apply a test-only overlay of the changed entity file to `libraries/Webtolk/Cdekapi`.
- Run Joomla bootstrap smoke script with test-mode CDEK API credentials.
- Verify `getCities()` handles `Нижний Новгород`.
- Verify `suggestCities()` handles `Новый Уренгой`.

## Exit Criteria

- `php -l` passes.
- Encoded query smoke keeps percent-encoded city/name values after Joomla-style query handling.
- Release ZIP is clean and has `1.3.2` metadata.
- `joomla.local` smoke returns valid CDEK API data for both city names with spaces.
