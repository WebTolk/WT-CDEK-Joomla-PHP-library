# Review 1.4.0-dev

Закрытых продуктовых замечаний: преждевременная initial notification при defer; устаревший выбор во время debounce; Choices clearStore до snapshot; возможность HTML-подписей Choices; относительный AJAX URL на вложенных SEF-страницах. Исправления проверены на окончательном ZIP и установленной библиотеке.

При ревью подтверждено: каждый source-element имеет одну пару подписок, повторные scan не накапливают handlers, removed target освобождает registry и отменяет fetch. Request generation меняется сразу при смене источников. JSON path обращается только к собственным свойствам и исключает prototype/constructor. Foreign-origin URL не получает CSRF. Endpoint отвергает GET/invalid token и invalid filter/FIAS до обращения к CDEK. Текст ответа провайдера не раскрывается клиенту при ошибках.

Публичные XML Tarifflist и Tariffinfo работают без миграции. Пользовательские layout overrides Tariffinfo должны вывести новое `$watchfield_attributes` на контейнере; стандартный layout уже обновлён. На тестовом стенде tariffinfo overrides не обнаружены. Shared CDEK docs уточнены по официальному экспорту; deprecated fias_region_guid не смешан с fias_guid населённого пункта.

Открытых блокирующих замечаний в проверенном объёме нет. Границы приёмки и пустые реальные выборки фильтров указаны в [verification](./2026-10-12-watchfield-140-dev-verification.md).
