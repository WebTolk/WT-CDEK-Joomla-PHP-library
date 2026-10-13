# Surface Map

Срез на 13.10.2026; dev checkpoint, финальный публичный релиз не объявляется.

## Entry Points

Joomla field render/WAM scan; onAjaxWtcdek action switch; helper XML map and custom data providers.

## Source Layers

Fields -> Helper/Watchfield options -> WebAsset scanner/transport -> renderer. AJAX -> DeliverypointsService -> library entities/request.

## Consumers

Site and administrator Joomla forms, JoomShopping settings/price rows, CDEK widget raw service requests.

## External State

Provider reference reads/cache; persisted form values owned by Joomla; snapshot/debounce/abort owned by Watchfield. No order mutations in assurance.
