CREATE TABLE IF NOT EXISTS `#__lib_wtcdek_location_regions`
(
    `country_code`  varchar(5)   NULL,
    `country`       varchar(500) NULL,
    `region`        varchar(500) NULL,
    `region_code`   int(10)      NOT NULL DEFAULT 0 COMMENT 'CDEK region code',
    `date_modified` datetime              DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE `region_code` (`region_code`),
    KEY `idx_country` (`country`),
    KEY `idx_region` (`region`),
    KEY `idx_region_code` (`region_code`),
    KEY `idx_date_modified` (`date_modified`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__lib_wtcdek_location_cities`
(
    `code`          int(10) UNSIGNED NOT NULL,
    `city_uuid`     varchar(36)      NULL,
    `city`          varchar(500)     NULL,
    `country_code`  varchar(5)       NULL,
    `country`       varchar(500)     NULL,
    `region`        varchar(500)     NULL,
    `region_code`   int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'CDEK region code',
    `postal_codes`  text NULL DEFAULT NULL COMMENT 'Location postal codes',
    `sub_region`    varchar(500)     NULL,
    `longitude`     float            NULL,
    `latitude`      float            NULL,
    `date_modified` datetime                  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE `region_code` (`code`),
    KEY `idx_city` (`city`),
    KEY `idx_region` (`region`),
    KEY `idx_region_code` (`region_code`),
    KEY `idx_postal_codes` (`postal_codes`),
    KEY `idx_country` (`country`),
    KEY `idx_date_modified` (`date_modified`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;