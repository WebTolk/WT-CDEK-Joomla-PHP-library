# Implementation Plan

## Objective

Plan a minimal source change that restores the old city-spaces behavior in `LocationEntity` without changing shared transport behavior in `CdekRequest`.

## Preconditions

- User explicitly approves code changes in a later turn.
- Current planning artifacts are accepted or adjusted.
- Joomla platform knowledge remains loaded for the implementation cycle.
- No unrelated source changes are mixed into the fix.

## Change Slices

1. Add old-style pre-encoding for non-empty `city` in `LocationEntity::getCities()`.
2. Add equivalent pre-encoding for non-empty `name` in `LocationEntity::suggestCities()`.
3. Leave `CdekRequest` unchanged in the first implementation slice.
4. Add or run focused checks for encoded city/name query parameters.
5. Confirm non-text filters such as `country_codes` remain unchanged.

## File Or Module Ownership

- Primary file: `lib_webtolk_wtcdek/src/Entities/LocationEntity.php`
- Read-only verification file:
  - `lib_webtolk_wtcdek/src/CdekRequest.php`

## Execution Order

1. Add `urlencode()` handling for `city` in `getCities()`.
2. Add `urlencode()` handling for `name` in `suggestCities()`.
3. Run PHP syntax check for changed file.
4. Run targeted smoke script or test for URL generation:
   - `/location/cities?city=Нижний Новгород`
   - `/location/suggest/cities?name=Новый Уренгой`
   - `country_codes=RU,KZ`
5. Run configured package/static checks if available.
6. Update investigation and assurance artifacts.

## Verification Hooks

- Static search confirms the only new city-name encoding is in `LocationEntity`.
- Generated URL for `Нижний Новгород` contains percent-encoded Cyrillic and encoded space or `+` according to `urlencode()`.
- Generated URL for `Новый Уренгой` is encoded.
- `country_codes=RU,KZ` remains unchanged from current behavior.
- POST JSON body behavior is unchanged.
- OAuth token POST remains `application/x-www-form-urlencoded`.
- Existing rawurlencoded UUID paths are not double-encoded.

## Rollback Considerations

- Revert the `LocationEntity` changes if CDEK rejects encoded city/name values.
- If more GET free-text parameters fail later, revisit a centralized `CdekRequest` query builder with runtime evidence.
- If already encoded user input becomes a practical issue, define raw-input expectation or add guarded encoding.

## Toolchain Contract References

- Logical tools:
  - `phpstan`
  - `phpunit`
  - `php-cs-fixer`
  - `phing`
- Run checks via configured toolchain.
- Resolve tool location through the active toolchain contract and tool policy.

## Logical Tools Used

- Planning only: PhpStorm MCP, Serena context already active, shell fallback for exact code evidence.

## Fallback Used

Yes.

## Fallback Reason

Shell fallback was used only for line-range evidence and existing local flow artifact writing. No package source code was changed.
