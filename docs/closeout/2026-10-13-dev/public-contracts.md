# Public Contracts

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## AJAX Contract

plugin=wtcdek; action=offices/calculate returns encoded payload suitable for format=raw. action=deliverypoints POST returns data[0].options in format=json. Single onAjaxWtcdek on site/admin.

## Input Contract

Explicit GET+POST, POST overrides GET. Field POST city/postal_code/fias_guid/type/is_reception/is_handout plus Joomla token; widget token may be URL/POST/header. Cookie-only action ignored.

## Field Contract

Deliverypoints XML supports literal address, watchfield mapping, filters/FIAS/custom providers. Helper class is Webtolk\Cdekapi\Helper\Watchfield. Native returned option items have string value/text.

## Lifecycle Contract

Watchfield subscribes input/change, trims source values, debounces input AJAX 250ms, invalidates before fetch and ignores stale responses; programmatic value setter needs input/change event. F2/F5 documented exceptions remain open.

## BC Notes

Removed separate onAjaxWtcdekdeliverypoints; caller URL/action must be updated together. F3 legacy attributes and F4 validate=options require review before release.
