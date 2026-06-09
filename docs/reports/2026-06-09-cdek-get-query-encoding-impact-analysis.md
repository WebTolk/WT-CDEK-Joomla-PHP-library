# Impact Analysis

## Trigger

New planning cycle for CDEK GET query encoding after finding that current `Uri::setQuery()` based handling is not equivalent to the old `urlencode($options['city'])` fix.

## Affected Components

- `CdekRequest::getResponse()` GET branch.
- Entity GET methods that pass query arrays to `CdekRequest`.
- Tests or smoke scripts for URL/query generation and CDEK responses.

## Domain Surface

- CDEK location city lookup by exact city name.
- CDEK location suggestions by city name.
- CDEK delivery points lookup and other GET filters.
- Query parameters with Cyrillic, spaces, punctuation, comma-separated values and numeric values.

## Runtime Surface

- Joomla HTTP client request URL construction.
- CDEK API endpoints receiving GET query strings.
- Backward-compatible facade calls such as `Cdek::getLocationCities()`.

## Data Or State Risks

- No persistent data migration expected.
- Incorrect query encoding can lead to empty city lookup results or CDEK validation errors.
- Encoding all query values can alter current behavior for comma-separated filters such as `country_codes`.

## User-Facing Risks

- Delivery calculation or pickup point workflows may fail when city lookup cannot resolve a city containing spaces.
- Existing integrations may rely on current unencoded comma behavior.
- A transport-level fix can affect endpoints outside the originally reported city lookup scenario.

## Assurance Focus

- Confirm generated GET URL for `city => 'Нижний Новгород'` is encoded.
- Confirm generated GET URL for `name => 'Новый Уренгой'` is encoded.
- Confirm `country_codes` behavior is acceptable to CDEK, including comma handling.
- Confirm UUID path segments remain encoded exactly once.
- Confirm POST JSON and OAuth POST behavior are unchanged.
