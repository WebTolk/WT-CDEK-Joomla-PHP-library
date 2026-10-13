# Deliverypoints: changed files and adoption handoff

## Summary

Operator correction of the 2026-10-09 external prototype import. Two erroneous PHP fields and the unused tariff JS are removed. The retained point selector is a neutral library field with offices/lockers filters, FIAS UUID support and automatic updates.

## Files changed

- Removed `lib_webtolk_wtcdek/src/Fields/WtsmcdektariffinfoField.php`, `WtsmcdekorderstatusField.php` and `media/js/wt_sm_cdek_tariff_info.js`.
- Renamed/adapted `WtsmcdekshipmentpointField.php` to `src/Fields/DeliverypointsField.php`: library namespace/type, literal/map priority, filters, FIAS input, library languages/assets, no refresh button.
- Adapted `src/Fields/Watchfield.php` to the library namespace; no new PHP 8.1-only array_is_list dependency.
- Renamed/adapted `wt_sm_cdek_watchfield.js` to `media/js/wtcdek-watchfield.js`.
- Replaced `wt_sm_cdek_origin_point.js` with `media/js/wtcdek-deliverypoints.js`: per-element instances, per-field debounce/requests, stale-response protection, safe labels, removal cleanup and idempotent initialization.
- Added `src/Service/DeliverypointsService.php`: unambiguous city resolution or direct FIAS UUID lookup, ALL/PVZ/POSTAMAT and reception/handout filtering, natural sort/deduplication, safe provider errors.
- Updated `media/joomla.assets.json` and both `language/*/lib_webtolk_cdekapi.ini` files.
- Added a dedicated read-only `onAjaxWtcdekdeliverypoints` handler to `plg_system_wtcdek/src/Extension/Wtcdek.php`. POST/token/input checks happen before client construction. Generic Joomla 4 and result-aware Joomla 5/6 results are supported. The old widget handler is unchanged.
- Added `.docs/fields/deliverypoints.md` and its index link; updated current project context with this adoption report and corrected local documentation paths.
- Offline PHP, Node and real-browser fixtures are kept under ignored `.webtolk/tests/`; evidence under `.webtolk/tmp/deliverypoints-browser/`.

## Contract impact

New XML type `deliverypoints`, prefix `Webtolk\Cdekapi\Fields`. Only unpublished donor prototype names are removed/renamed. Existing public `TarifflistField` and `TariffinfoField` retain their exact Git blobs. The originating JoomShopping checkout/stand is untouched.

Address attributes `city`, `zip`, `fias_guid` select literal mode by presence, including empty values. Otherwise a named JSON `watchfield` map resolves actual Form fields. Filters are independent of that mode. UUID means FIAS settlement GUID, not CDEK city_uuid. Default includes PVZ/POSTAMAT and pickup-only offices; XML flags can restrict the results.

The operator's final clarification supersedes the earlier request to retain the refresh button: both modes initialize automatically; watched sources also trigger input/change refresh.

## Release boundary

Existing 1.3.2 was published separately from the June ZIP and existing commit. These new source changes are local, uncommitted and excluded from that release. Do not rebuild/overwrite the published 1.3.2 artifact from this worktree; choose a subsequent version for a later field release.

Historical copy manifests/source evidence remain unchanged and describe the original transfer. The current context points to this superseding adoption record.

## Follow-up

The new field has offline native-Joomla and browser fixture evidence. Real CDEK acceptance and Joomla package update/install are not performed. Before a subsequent release, exclude docs/.docs incoming artifacts from package payload, select its version and verify installation/assets against an identified site. Shared OpenAPI knowledge is an official export dated 2026-02-18; no live provider call was used.
