# Deliverypoints browser verification

Executed in hidden headless Google Chrome with a separate temporary profile and local HTML fixture. Actual library watcher/deliverypoints JS is loaded from the repository. All fetch calls are replaced with controlled deferred provider responses; no CDEK/site request is made.

Four native selects share one real form: two watch the same city/zip fields, one uses literal XML-equivalent parameters and one watches a FIAS UUID field. All 23 checks passed.

Verified automatic initial loading, no refresh button, false filters and UUID POST data, saved selection, literal text labels, independent statuses/results, repeated initialization, two watchers on one source, debounce and input/change deduplication, separate request cancellation, stale results, removal cleanup, replacement initialization and UUID changes.

Evidence: `.webtolk/tmp/deliverypoints-browser/result.json` and `result.html`. This proves real browser DOM behavior in the fixture, not installation or production/provider acceptance. Native Joomla Fancy Select is not mounted in this browser fixture; its API calls and allowHTML=false are checked separately in Node.
