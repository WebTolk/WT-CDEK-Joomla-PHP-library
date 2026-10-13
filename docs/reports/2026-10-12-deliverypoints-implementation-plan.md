# Deliverypoints field: scope and implementation plan

## Scope

Operator request: publish the existing 1.3.2 package; remove the mistakenly imported tariff subclass and JoomShopping order-status field; retain the pickup-point field under a neutral library name and check its input logic.

The 1.3.2 release is pinned to commit 887e4ff7b55ace23394f837245ed640ddcef4b03 and the previously verified June ZIP. Field changes belong to the subsequent development worktree, not that published package. No version bump, product commit/push or live CDEK request is part of this field task.

## Public contracts and invariants

- Existing public TarifflistField and TariffinfoField remain unchanged.
- The new field is DeliverypointsField, XML type deliverypoints, namespace Webtolk\Cdekapi\Fields.
- Operator's later clarification supersedes the donor filter: by default include PVZ and POSTAMAT, including pickup-only offices. XML point_type selects ALL/PVZ/POSTAMAT; optional is_reception and is_handout select true/false (also 1/0). Missing/empty flags impose no restriction. False filters must survive entity array_filter and be rechecked against provider results.
- Operator confirmed on 2026-10-12: the presence of XML zip, city or fias_guid, including an empty value, selects literal mode and completely disables watchfield. Never combine the modes.
- Otherwise watchfield is a named JSON map of zip, city and/or fias_guid to Joomla Form source names. Field IDs/names/groups come from the owning Form.
- UUID support is fias_guid (FIAS settlement UUID) according to shared official OpenAPI knowledge. city_uuid is not a supported deliverypoints query parameter. A nonempty FIAS UUID bypasses city-name/postal resolution.
- The last operator clarification removes the refresh button in both modes. Literal addresses load on initialization; watched addresses update on input (250 ms debounce) and change. JS state, cancellation, cleanup and statuses belong to each rendered field independently.
- Do not choose an arbitrary city when the lookup is ambiguous, or silently replace a stored point when it is no longer returned.
- Keep historic 2026-10-09 copy evidence immutable. Record the new disposition separately.

## Implementation

1. Remove WtsmcdektariffinfoField, WtsmcdekorderstatusField and the unused prototype tariff JS.
2. Rename and adapt the pickup-point field, common resolver and two JS assets to the library namespace/media/language conventions.
3. Supply the missing library read-only provider and dedicated system-plugin AJAX event. Do not reuse or alter the legacy widget handler.
4. Use POST, Joomla session token validation, bounded string inputs and structured Joomla AJAX results. Support the existing Joomla 4 generic event and Joomla 5/6 result-aware event.
5. Verify literal/map priority, grouped forms, ambiguous/empty/provider-failure cases, selection preservation, races and safe text rendering offline. No provider or stand configuration changes.

## Tools and limits

PhpStorm read_file was attempted for this repository and rejected: only the donor project is open. Serena is not exposed in this session. Authorized shell/apply_patch fallback is used and recorded in project telemetry. Joomla toolkit was resolved inside E:/.agents/docs/joomla/core/joomla-toolkit; the older direct path in project context no longer exists.
