# Patch

## Patch Id

`2026-06-09-cdek-get-query-encoding`

## Source Task

Restore CDEK city lookup behavior for city names with spaces and build package `1.3.2`.

## Problem Or Learning

The old `v.1.3.0` fix encoded `city` before requesting CDEK city data. After the entity/request refactor, current code passed `city` and `name` through `Joomla\Uri\Uri::setQuery()` without an equivalent explicit encode step at the location entity boundary.

Release archive inspection also showed that local flow and API documentation working directories can enter the ZIP after migration to `.webtolk` unless the package exclude list is updated.

## Proposed Reusable Change

For this project:

- Keep the CDEK location free-text query encoding at the `LocationEntity` boundary for the minimal `1.3.2` fix.
- Keep `.webtolk/` and `.apidoc.cdek.ru/` in `.webtolk/build/package.config.json` excludes.
- Treat Joomla CLI installer cleanup failures inside Codex sandbox as a permissions/runtime issue until verified outside sandbox.

## Target Layer

Project-local implementation and project-local packaging configuration.

## Files To Update

- `lib_webtolk_wtcdek/src/Entities/LocationEntity.php`
- `.webtolk/build/package.config.json`
- `.webtolk/evolutions/cursor.json`
- `docs/reports/2026-06-09-cdek-get-query-encoding-evolution.md`

## Compatibility Considerations

- No public API signature changes.
- No database migration.
- No shared request-layer behavior changed.
- Comma-separated `country_codes` behavior remains unchanged.

## Approval Status

Applied locally and verified.

## Toolchain Contract References

- `phing` via configured toolchain.
- `php` via configured toolchain.
