# Decision Log

## Decision

Plan a minimal location-level encoding fix first, using the same pattern as the old city-spaces fix, rather than starting with a centralized GET query builder in `CdekRequest`.

## Context

The old fix added `urlencode($options['city'])` in the legacy monolithic city lookup method. The current entity-based architecture routes GET requests through `CdekRequest::getResponse()`, where query arrays are currently passed to `$requestUri->setQuery($data)`.

Local Joomla core inspection shows `Uri::setQuery(array)` stores the array, while later query rendering calls `buildQuery()`, which returns `urldecode(http_build_query($params, '', '&'))`. Therefore it does not preserve encoded Cyrillic/space query values in the final query string.

## Options Considered

1. Add `urlencode()` back only in `LocationEntity::getCities()` and maybe `suggestCities()`.
2. Add a centralized encoded query builder inside `CdekRequest` for GET requests.
3. Replace Joomla `Uri` usage with a PSR-7 URI/query builder.
4. Do nothing and rely on `Uri::setQuery()`.

## Chosen Direction

Option 1: apply the old pre-encoding pattern only where the domain problem is known: CDEK location city-name query parameters.

The planned first implementation should:

- encode `city` in `LocationEntity::getCities()` before calling `CdekRequest`;
- encode `name` in `LocationEntity::suggestCities()` before calling `CdekRequest`;
- leave `CdekRequest` unchanged in the first slice.

This works with Joomla URI behavior because `Uri::buildQuery()` does one `urldecode(http_build_query(...))`; pre-encoded values survive that final decode as encoded query values.

## Consequences

- The first fix is narrow and easier to verify.
- Entity method APIs stay unchanged.
- Transport behavior remains unchanged for non-location endpoints.
- This intentionally does not solve every possible GET query encoding case.
- Caller-provided pre-encoded city/name values could be encoded again; this matches the old fix's simple behavior and should be documented as raw input expected.

## Revisit Trigger

Revisit the design if:

- another CDEK GET endpoint with free-text query values shows the same problem;
- runtime evidence shows `suggestCities(name)` behaves differently from `getCities(city)`;
- duplicated location-level encoding starts to spread beyond one entity;
- a later broader transport fix becomes safer after targeted release evidence.
