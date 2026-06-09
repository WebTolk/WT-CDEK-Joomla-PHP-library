# Brief

## Task

Plan a new development cycle for fixing CDEK GET query encoding after confirming that `Joomla\Uri\Uri::setQuery()` does not preserve URL-encoded query values.

## Requested Outcome

A planning-only handoff that defines the target design, affected surface, implementation order, and verification requirements. The preferred first slice is a minimal location-level fix. No package source code changes in this cycle.

## Problem Statement

The old city lookup fix encoded city names with spaces via `urlencode($options['city'])` in the monolithic `Cdek::getLocationCities()` method. The current entity-based library no longer has an equivalent explicit encoding step. Current GET handling delegates query parameters to `Uri::setQuery($data)`, but Joomla URI building uses `urldecode(http_build_query(...))`, so city names such as `Нижний Новгород` can remain unencoded in the rendered query string.

## Stakeholders

- Maintainer: WebTolk / Sergey Tolkachyov.
- Library consumers using CDEK location/city, delivery points, order lookup and other GET endpoints.
- Joomla sites relying on `lib_webtolk_wtcdek`.

## Constraints

- Planning only; do not modify source code yet.
- Prefer the smallest fix that restores the confirmed old behavior.
- Preserve backward-compatible entity APIs.
- Avoid double-encoding already rawurlencoded path segments.
- Use Joomla platform rules and existing project flow.

## Inputs Provided

- User instruction to start a new cycle and plan only.
- Investigation report: `docs/reports/2026-06-09-cdek-city-space-commit-investigation.md`.
- Current code evidence from `CdekRequest::getResponse()`, `LocationEntity::getCities()` and `LocationEntity::suggestCities()`.
- Local Joomla core evidence showing `AbstractUri::buildQuery()` returns `urldecode(http_build_query($params, '', '&'))`.

## Assumptions

- GET query values are passed into `CdekRequest::getResponse()` as raw, unencoded scalar values unless an entity explicitly documents otherwise.
- CDEK accepts standard URL-encoded query strings.
- The first implementation should avoid entity-level `urlencode()` calls unless central handling proves unsafe.

## Success Criteria

- The implementation plan has one preferred design and explicit alternatives.
- File/module ownership is bounded.
- Assurance cases include city names with spaces and Cyrillic text.
- The plan records that implementation is blocked until the user explicitly allows code changes.
