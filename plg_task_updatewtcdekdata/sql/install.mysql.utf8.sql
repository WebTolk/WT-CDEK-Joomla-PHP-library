CREATE TABLE IF NOT EXISTS `#__lib_wtcdek_location_regions`
(
    `country_code`  VARCHAR(5)   NULL,
    `country`       VARCHAR(500) NULL,
    `region`        VARCHAR(500) NULL,
    `region_code`   INT(10)      NOT NULL DEFAULT 0 COMMENT 'CDEK REGION CODE',
    `date_modified` DATETIME              DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
    `code`          INT(10) UNSIGNED NOT NULL,
    `city_uuid`     VARCHAR(36)      NULL,
    `city`          VARCHAR(500)     NULL,
    `country_code`  VARCHAR(5)       NULL,
    `country`       VARCHAR(500)     NULL,
    `region`        VARCHAR(500)     NULL,
    `region_code`   INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'CDEK REGION CODE',
    `postal_codes`  TEXT             NULL     DEFAULT NULL COMMENT 'LOCATION POSTAL CODES',
    `sub_region`    VARCHAR(500)     NULL,
    `longitude`     FLOAT            NULL,
    `latitude`      FLOAT            NULL,
    `date_modified` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE `region_code` (`code`),
    KEY `idx_city` (`city`),
    KEY `idx_region` (`region`),
    KEY `idx_region_code` (`region_code`),
    KEY `idx_country` (`country`),
    KEY `idx_date_modified` (`date_modified`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__lib_wtcdek_delivery_points`
(
    `code`                     VARCHAR(500) NOT NULL,
    `name`                     TEXT         NULL DEFAULT NULL COMMENT 'Derpecated field. Use location field data instead',
    `uuid`                     VARCHAR(36)  NULL COMMENT 'OFFICE ID IN CDEK SYSTEM',
    `location`                 TEXT         NULL DEFAULT NULL,
    `address_comment`          TEXT         NULL DEFAULT NULL,
    `nearest_station`          TEXT         NULL DEFAULT NULL,
    `nearest_metro_station`    TEXT         NULL DEFAULT NULL,
    `work_time`                TEXT         NULL DEFAULT NULL,
    `phones`                   TEXT         NULL DEFAULT NULL,
    `email`                    VARCHAR(500) NULL DEFAULT NULL,
    `note`                     TEXT         NULL DEFAULT NULL,
    `type`                     VARCHAR(100) NULL DEFAULT NULL COMMENT 'PVZ OR POSTAMAT',
    `owner_code`               VARCHAR(500) NULL DEFAULT NULL,
    `take_only`                INT UNSIGNED NULL DEFAULT NULL,
    `is_handout`               INT UNSIGNED NULL DEFAULT NULL,
    `is_reception`             INT UNSIGNED NULL DEFAULT NULL,
    `is_dressing_room`         INT UNSIGNED NULL DEFAULT NULL,
    `have_cashless`            INT UNSIGNED NULL DEFAULT NULL,
    `have_cash`                INT UNSIGNED NULL DEFAULT NULL,
    `have_fast_payment_system` INT UNSIGNED NULL DEFAULT NULL,
    `allowed_cod`              INT UNSIGNED NULL DEFAULT NULL,
    `is_ltl`                   INT UNSIGNED NULL DEFAULT NULL,
    `fulfillment`              INT UNSIGNED NULL DEFAULT NULL,
    `site`                     TEXT         NULL DEFAULT NULL,
    `office_image_list`        TEXT         NULL DEFAULT NULL,
    `work_time_list`           TEXT         NULL DEFAULT NULL,
    `work_time_exception_list` TEXT         NULL DEFAULT NULL,
    `weight_min`               FLOAT        NULL DEFAULT NULL,
    `weight_max`               FLOAT        NULL DEFAULT NULL,
    `dimensions`               TEXT         NULL DEFAULT NULL,
    `date_modified`            DATETIME          DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE `code` (`code`),
    UNIQUE `uuid` (`uuid`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;