# Migration Notes: WT Cdek Library Package 1.3.2

## When Migration Is Required

Install package `1.3.2` when the site needs the restored CDEK city lookup behavior for city names containing spaces.

## Preconditions

- Joomla extension install permissions must allow PHP to delete temporary extraction files.
- The target site should already be compatible with the existing `1.3.x` package line.
- No database schema migration is introduced by this fix.

## Steps

- Install `.packages/WT Cdek library package_1.3.2.zip` through Joomla extension installation or Joomla CLI.
- Prefer running Joomla CLI outside the Codex sandbox for this package on the local stand, because sandbox PHP `unlink()` can fail during installer cleanup.
- Confirm the package, library, system plugin and task plugin records show `1.3.2`.
- Optionally run a city lookup smoke for a city with spaces, for example `Нижний Новгород`.

## Backward Compatibility

- The fix restores a behavior that existed in the older `Cdek::getLocationCities()` implementation.
- No public method signatures changed.
- Shared transport behavior in `CdekRequest` was intentionally left unchanged.

## Data Or Config Impact

- No database schema changes.
- No user configuration changes.
- Release package hygiene changed: `.webtolk/` and `.apidoc.cdek.ru/` are excluded from archives.

## Rollback Strategy

- Reinstall the previous package version `1.3.1` if a regression is detected.
- Remove stale `.packages/install_*` directories from failed local sandbox install attempts as cleanup only; they are not runtime state.

## Toolchain Contract References

- `phing` via configured toolchain.
- `php` via configured toolchain.
- Joomla CLI runtime install/verification.
