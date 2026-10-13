# Deliverypoints review findings

## Findings and severity

No blocking findings in the implemented local scope after verification.

Resolved during implementation: donor namespace/assets/AJAX dependencies; arbitrary old sender-only restriction; false filters being dropped; standard com_ajax array result shape; stale responses and address changes; ambiguous cities; provider errors masquerading as empty lists; missing POST token before provider construction.

The current UUID path uses documented `fias_guid` (FIAS settlement UUID). `city_uuid` is absent from the deliverypoints query schema and is not accepted as a synonym. Knowledge source: shared official OpenAPI export dated 2026-02-18, E:/.agents/docs/rest-api/cdek-api/api_v2_integration.md.

## Evidence

- Native Joomla 4.4.14: 83 passing checks. Joomla 5.4.7 and 6.1.3: 84 passing checks each. Actual Form discovery/layouts and generic/result-aware AJAX events; substituted CDEK client; database connections explicitly forbidden.
- Node scripts: 31 passing UI/watcher checks.
- Hidden headless Chrome with native DOM: 23 passing checks on four fields, including shared sources, automatic/debounced refresh, stale-response cancellation, safe Option labels, idempotent initialization, removal and replacement.
- PHP lint 4/4; Node syntax 2/2; asset/language consistency, removed donor filenames and preserved public tariff Git blobs verified. Legacy widget handler unchanged.
- Git whitespace check passed with cr-at-eol enabled for existing Windows CRLF files; real trailing-space checks remain enabled.

## Residual risks and follow-up

The new field is not released or installed on a site. Real CDEK responses, fresh Joomla package install/update and native Fancy Select runtime are not accepted by these tests. Fancy Select synchronization is covered by the Node fixture; native browser fixture uses standard selects. Cloned repeatable subform rows need corresponding new script options/source descriptors, not just copied DOM.

Before a subsequent release, set the subsequent version and exclude docs/.docs report payloads from packaging. Do not overwrite or rebuild the published 1.3.2 archive from the modified worktree.

## Delivery verdict

Requested source correction and offline verification complete. Existing 1.3.2 publication complete and verified separately. Field product commit/push, site install, real API lookup and later field release are not performed.
