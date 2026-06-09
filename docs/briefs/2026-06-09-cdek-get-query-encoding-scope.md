# Scope

## In Scope

- Plan a minimal location-level query encoding change in `lib_webtolk_wtcdek/src/Entities/LocationEntity.php`.
- Identify affected GET endpoints that pass query arrays through `CdekRequest::getResponse()`.
- Define verification cases for city names with spaces, Cyrillic names, country code filters and existing rawurlencoded path segments.
- Decide whether `DELETE` query handling should stay unchanged or be included later.

## Out Of Scope

- Editing PHP source code in this cycle.
- Running real CDEK API requests.
- Changing public entity method signatures.
- Changing path segment encoding with existing `rawurlencode($uuid)` usage.
- Release packaging or version bump.

## Affected Areas

- `lib_webtolk_wtcdek/src/Entities/LocationEntity.php`
- Existing GET transport in `lib_webtolk_wtcdek/src/CdekRequest.php` as read-only context.
- GET consumers in entity classes, especially:
  - `LocationEntity::getCities()`
  - `LocationEntity::suggestCities()`
  - `DeliverypointsEntity::getDeliveryPoints()`
  - order/payment/passport/check GET lookups
- Documentation and assurance artifacts for the next implementation cycle.

## Non-Goals

- Do not reintroduce scattered `urlencode()` calls into every entity method; keep the first fix limited to location city-name parameters.
- Do not alter POST JSON body handling.
- Do not alter OAuth `application/x-www-form-urlencoded` POST handling.
- Do not rely on `Uri::setQuery($array)` as the final encoder.

## Risk Boundaries

- Location-level encoding is narrow and may not fix future free-text GET parameters outside `LocationEntity`.
- `country_codes` is currently converted to a comma-separated string; a generic query encoder may encode comma as `%2C`.
- Pre-encoding assumes callers pass raw city names, not already encoded strings.

## Required Artifacts

- `brief`: `docs/briefs/2026-06-09-cdek-get-query-encoding-brief.md`
- `scope`: `docs/briefs/2026-06-09-cdek-get-query-encoding-scope.md`
- `decision-log`: `docs/reports/2026-06-09-cdek-get-query-encoding-decision-log.md`
- `impact-analysis`: `docs/reports/2026-06-09-cdek-get-query-encoding-impact-analysis.md`
- `architecture`: `docs/reports/2026-06-09-cdek-get-query-encoding-architecture.md`
- `implementation-plan`: `docs/reports/2026-06-09-cdek-get-query-encoding-implementation-plan.md`

## Exit Criteria

- Planning artifacts are complete.
- Implementation handoff is explicit.
- Source code remains unchanged.
