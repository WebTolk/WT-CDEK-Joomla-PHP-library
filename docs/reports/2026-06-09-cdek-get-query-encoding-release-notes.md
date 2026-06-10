# Release Notes: WT Cdek Library Package 1.3.2

## Release Scope

Release package `1.3.2` for the Joomla package containing the WebTolk CDEK API library, `System - WT CDEK` plugin, and scheduled task plugin.

## User-Visible Changes

- Restores successful CDEK city lookup for city names containing spaces.
- Verified examples include `Нижний Новгород` and `Новый Уренгой`.

## Internal Changes

- `LocationEntity::getCities()` now encodes non-empty `city` query values before delegating to the request layer.
- `LocationEntity::suggestCities()` now encodes the `name` query value before delegating to the request layer.
- `country_codes` comma-separated behavior remains unchanged.
- `.webtolk/build/package.config.json` now excludes `.webtolk/` and `.apidoc.cdek.ru/` from release archives.

## Verification Status

- PHP lint passed for the changed entity.
- Query smoke confirmed encoded city/name values while preserving `country_codes=RU,KZ`.
- Final ZIP `.packages/WT Cdek library package_1.3.2.zip` was built with the shared Phing packager.
- ZIP inspection confirmed package and library manifests stamped as `1.3.2`.
- Joomla runtime smoke on `joomla.local` passed.
- Elevated Joomla CLI install completed successfully and updated extension records to `1.3.2`.

## Risks And Caveats

- Sandbox-run Joomla CLI install failed because PHP `unlink()` was blocked during temporary extraction cleanup. Elevated/out-of-sandbox install succeeded; the failure was not a package defect.
- Two stale `.packages/install_*` directories from failed sandbox install attempts may remain until normal cleanup.

## Rollback Notes

- Roll back by reinstalling the previous known package version `1.3.1`.
- If only the runtime overlay was applied during testing, restore `.webtolk/tmp/joomla-local-backup/LocationEntity.1.3.1.php` to the installed library path.

## Toolchain Contract References

- `phing` via configured toolchain.
- `php` via configured toolchain.
- `joomla-cli` as runtime verification command.
