# Architecture

## Current State

`CdekRequest::getResponse()` builds a `Joomla\Uri\Uri` from the CDEK host and method path. For GET requests it calls `$requestUri->setQuery($data)` and passes the URI object to `$http->get()`.

Joomla URI rendering builds query strings through `urldecode(http_build_query(...))`, so this path does not preserve full URL encoding for Cyrillic and spaces.

## Target State

The first implementation restores the old behavior only for CDEK location city-name parameters. `LocationEntity` pre-encodes city-name values before passing them into the existing `CdekRequest` flow.

## Design Decisions

- Keep entity APIs unchanged.
- Keep `CdekRequest` unchanged in the first implementation slice.
- Pre-encode only known free-text location parameters:
  - `city` in `getCities()`
  - `name` in `suggestCities()`
- Keep path segment encoding in entity methods where `rawurlencode($uuid)` already exists.
- Defer any transport-level query builder until there is evidence of the same issue outside location city-name endpoints.

## Alternatives Rejected

- Transport-level query builder in the first slice: broader blast radius than needed for the confirmed problem.
- Entity-level `urlencode()` for `city` only: misses `suggestCities(name)`, which is the same domain concept.
- Encode every entity parameter manually: duplicates transport concerns and risks inconsistent behavior.
- Continue with `Uri::setQuery(array)`: confirmed not equivalent to the old fix.
- Change all request handling including POST and DELETE immediately: larger blast radius than needed for the reported GET issue.

## Interfaces And Dependencies

- `CdekRequest::getResponse(string $method, array $data, string $request_method, array $curl_options): array`
- Joomla HTTP client from `HttpFactory`.
- `Joomla\Uri\Uri` for host/path composition.
- PHP `http_build_query()`.
- Entity classes under `lib_webtolk_wtcdek/src/Entities`.

## Risk Controls

- Keep the change inside `LocationEntity`.
- Verify generated final query strings through the existing `Uri::setQuery()` path.
- Verify raw city/name input is expected.
- Confirm `country_codes` and other non-text filters stay unchanged.
- Keep broader `CdekRequest` builder as a documented fallback design, not first implementation.

## Rollout Order

1. Encode `city` in `LocationEntity::getCities()`.
2. Encode `name` in `LocationEntity::suggestCities()`.
3. Run URL-generation checks through current `CdekRequest`/`Uri::setQuery()` behavior.
4. Run limited CDEK API smoke checks if credentials/test mode are available.
5. Revisit centralized `CdekRequest` builder only if more GET free-text endpoints fail.
