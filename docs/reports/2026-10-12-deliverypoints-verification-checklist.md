# Deliverypoints verification checklist

## Checks run

| Check | Result |
| --- | --- |
| Joomla 4.4.14 native Form/provider/event fixture | 83 passed |
| Joomla 5.4.7 native fixture | 84 passed |
| Joomla 6.1.3 native fixture | 84 passed |
| Node actual JS with DOM fixture | 31 passed |
| Headless Chrome real DOM fixture | 23 passed |
| Changed/new PHP syntax | 4 passed |
| New JS syntax | 2 passed |
| Library assets, RU/EN language keys, removed filenames | Passed |
| Public TariffinfoField/TarifflistField Git blobs, legacy widget handler | Unchanged |
| Git diff whitespace (Windows cr-at-eol) | Passed |
| Release 1.3.2 uploaded asset and actual download SHA256 | Match June final archive |

## Commands and evidence

- `php -d error_reporting=8191 .webtolk/tests/deliverypoints.php <Joomla-source-root>`; results: `.webtolk/tmp/deliverypoints-native.json`.
- `node .webtolk/tests/deliverypoints-ui.cjs`.
- Headless Chrome opened `.webtolk/tests/deliverypoints-browser.html` with mocked fetch and a separate temporary profile; results: `.webtolk/tmp/deliverypoints-browser/result.json` and `result.html`.
- Static preservation checks: `.webtolk/tmp/deliverypoints-static.json`.
- Existing/remote-downloaded release ZIP SHA256: e36a0ec075990ce914942b47c650e14870a1802f8ff6fa71a77fa92c4bfe9d88.

The deprecated API notices of the old Joomla fixture dependencies were disabled via PHP error_reporting; warnings/errors remained enabled. The fixture reads core sources, never loads configuration.php, and cannot connect to a database. No CDEK requests are made.

## Checks skipped and practical limits

Fresh install/update of the new field package, real site forms, real provider acceptance and native Fancy Select browser behavior are not tested. The publication uses existing June release/install evidence plus current archive verification. Neither donor stand nor library stand was modified.

## Ready for delivery

Yes for the requested local implementation and the separate public 1.3.2 release. A later field release requires its own version, package hygiene, build and install/provider acceptance.
