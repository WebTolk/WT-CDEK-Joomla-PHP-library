# Investigation Report

## Question

Find the old commit that fixed CDEK city lookups when a city name contains spaces, such as "Нижний Новгород" or "Новый Уренгой", and check how it relates to GitHub release `v.1.3.0`.

## Context

The user requested read-only investigation. Package source code was not changed.

## Evidence

- GitHub release `v.1.3.0` exists and points to release page `https://github.com/WebTolk/WT-CDEK-Joomla-PHP-library/releases/tag/v.1.3.0`.
- Local tag `v.1.3.0` resolves to commit `0e8d6e8e7f44d60a96682715b0fe92f1ab53827c` with message `Правка README.md`.
- Commit `a066be13bf1bfed44cc3a338dbfab94515912b53` has message `Исправлена ошибка запроса городов, у которых в названиях пробелы. Начата работа над плагином планировщика для обновления данных сдек в локальной базе данных`.
- `git merge-base --is-ancestor a066be1 v.1.3.0` confirms that `a066be1` is included in the history of `v.1.3.0`.
- GitHub API for `a066be1` reports a 4-line change in `lib_webtolk_wtcdek/src/Cdek.php` adding `urlencode($options['city'])` inside `getLocationCities()`.
- In `v.1.3.0`, `Cdek::getLocationCities()` is a deprecated wrapper to `location()->getCities()`, and `LocationEntity::getCities()` delegates GET query building to `CdekRequest::getResponse()`.

## Findings

The old explicit fix is commit:

`a066be13bf1bfed44cc3a338dbfab94515912b53`

GitHub link:

`https://github.com/WebTolk/WT-CDEK-Joomla-PHP-library/commit/a066be13bf1bfed44cc3a338dbfab94515912b53`

The changed code was in the old monolithic method `lib_webtolk_wtcdek/src/Cdek.php::getLocationCities()`.

The relevant added logic:

```php
if(array_key_exists('city', $options) && !empty($options['city']))
{
    $options['city'] = urlencode($options['city']);
}
```

## Confirmed Root Cause

The original issue was unencoded spaces in the `city` GET query parameter for `/location/cities`.

## Remaining Unknowns

- Whether the entity-based `v.1.3.0` transport layer preserves the same behavior through `Joomla\Uri\Uri::setQuery($data)` for all city strings must be verified by runtime/API request evidence.
- The old explicit `urlencode($options['city'])` is not present in `LocationEntity::getCities()` in `v.1.3.0`.

## Current Version Check

Current working tree source inspection found no direct analogue of the old `urlencode($options['city'])` fix.

- Regex search for non-`raw` `urlencode(` under `lib_webtolk_wtcdek/src` returned no matches.
- Existing `rawurlencode()` usages are for UUID/path segments in entity methods, not for `city` or `name` query parameters.
- `LocationEntity::getCities()` merges request options and passes them unchanged to `CdekRequest::getResponse('/location/cities', $options, 'GET')`.
- `LocationEntity::suggestCities()` builds `['name' => $city_name]` and passes it unchanged to `CdekRequest::getResponse('/location/suggest/cities', $request_options, 'GET')`.
- `CdekRequest::getResponse()` handles GET and DELETE query parameters through `$requestUri->setQuery($data)`.

This means the current library relies on `Joomla\Uri\Uri::setQuery()` for query encoding instead of pre-encoding the `city`/`name` values in the entity method.

## Recommendation

If the current task is only to locate the old fix, use commit `a066be1`.

If the next task is to ensure the behavior still works after the `v.1.3.0` entity refactor, route to `code-assurance` and verify generated GET URLs or real API responses for city names with spaces.
