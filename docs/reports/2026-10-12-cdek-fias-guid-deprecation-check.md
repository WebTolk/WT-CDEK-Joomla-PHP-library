# CDEK FIAS GUID: current documentation verification

## Question

Operator asked to verify whether fias_guid is deprecated before relying on the new field's UUID lookup.

## Source and freshness

Retrieved the live official OpenAPI export on 2026-10-12:
https://gateway.cdek.ru/api-cdek-docs/web/docs/merged?sectionId=api_v2_integration

HTTP 200, OpenAPI 3.0.1. Snapshot: `.webtolk/tmp/cdek-docs-audit-20261012/live-merged.txt`; selected evidence: `summary.json` in the same directory. SHA256: d699a8cf90dbb334f39d2ba77fa7fc1353f4088bcbf1a8aabc44aa43842ddcab.

The earlier shared knowledge was exported on 2026-02-18. This audit uses a fresh export rather than assuming that snapshot is current. Shared knowledge files are not modified.

## Findings

| Identifier and scope | Official status |
| --- | --- |
| fias_guid query in GET /v2/deliverypoints | Declared UUID filter for FIAS settlement; no deprecated flag |
| fias_guid query in GET /v2/location/cities | Declared FIAS settlement UUID; no deprecated flag |
| fias_region_guid query in GET /v2/location/cities and /v2/location/regions | deprecated=true; description warns that regional FIAS values may be outdated |
| V2LocationCityDto.fias_guid and OfficeLocationDto.fias_guid | No deprecated flag |
| V2LocationCityDto.fias_region_guid | deprecated=true |
| city_uuid query in GET /v2/deliverypoints | Absent; city_uuid is present in response DTOs |

No evidence in the current specification marks the settlement fias_guid used by this field as deprecated. The explicit deprecation applies to the distinct regional fias_region_guid.

## Effect on implementation

The implemented deliverypoints filter uses fias_guid. No product-code change is required on the basis of the deprecation question. Absence of a deprecated flag is documentation evidence, not proof of live provider behavior. No CDEK API delivery-point/city request, shipment operation, site installation or release modification was performed.
