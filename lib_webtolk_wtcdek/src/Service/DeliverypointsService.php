<?php

/**
 * CDEK delivery-point options for Joomla forms.
 * @package       WT Cdek library package
 * @subpackage    Webtolk/Cdekapi
 * @author        Sergey Tolkachyov, WebTolk
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @version       __DEPLOY_VERSION__
 * @license       GNU General Public License version 3 or later.
 * @link          https://web-tolk.ru
 * @since         __DEPLOY_VERSION__
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Service;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Webtolk\Cdekapi\Cdek;

/**
 * Resolve an unambiguous city and list its PVZ offices and/or parcel lockers.
 */
final class DeliverypointsService
{
    /**
     * Validate shared XML/AJAX filters. Empty flags mean no restriction.
     * Boolean flags are sent as strings so the entity's array_filter preserves false.
     *
     * @param   array  $filters  Type (ALL/PVZ/POSTAMAT), is_reception and is_handout.
     * @return  array{type:string,is_reception:string,is_handout:string}
     * @since   __DEPLOY_VERSION__
     */
    public static function normalizeFilters(array $filters): array
    {
        $type = strtoupper(trim((string) ($filters['type'] ?? 'ALL')));

        if (!\in_array($type, ['ALL', 'PVZ', 'POSTAMAT'], true)) {
            throw new \InvalidArgumentException('Invalid delivery-point type.');
        }

        $normalized = ['type' => $type, 'is_reception' => '', 'is_handout' => ''];

        foreach (['is_reception', 'is_handout'] as $flag) {
            $value = $filters[$flag] ?? '';
            $value = \is_bool($value) ? ($value ? 'true' : 'false') : strtolower(trim((string) $value));

            if ($value === '') {
                continue;
            }

            if (!\in_array($value, ['true', 'false', '1', '0'], true)) {
                throw new \InvalidArgumentException('Invalid delivery-point boolean filter.');
            }

            $normalized[$flag] = \in_array($value, ['true', '1'], true) ? 'true' : 'false';
        }

        return $normalized;
    }

    /**
     * Validate the FIAS settlement UUID supported by GET /v2/deliverypoints.
     * This identifier is not the CDEK city_uuid returned by city suggestions.
     *
     * @param   string  $guid  FIAS UUID or an empty string.
     * @return  string  Normalized UUID.
     * @since   __DEPLOY_VERSION__
     */
    public static function normalizeFiasGuid(string $guid): string
    {
        $guid = strtolower(trim($guid));

        if ($guid !== '' && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/D', $guid)) {
            throw new \InvalidArgumentException('Invalid FIAS UUID.');
        }

        return $guid;
    }

    /**
     * List matching points without choosing an arbitrary city or returning unbounded offices.
     * Empty/ambiguous city results return no options; provider failures propagate.
     *
     * @param   Cdek    $cdek        Configured client.
     * @param   string  $city        City name, optionally combined with a postal code.
     * @param   string  $postalCode  Postal code, optionally combined with a city name.
     * @param   array   $filters     Optional point type and reception/handout filters.
     * @param   string  $fiasGuid    FIAS settlement UUID; resolves points directly when supplied.
     * @return  array<int, array{value:string,text:string}>
     * @since   __DEPLOY_VERSION__
     */
    public function getOptions(Cdek $cdek, string $city, string $postalCode, array $filters = [], string $fiasGuid = ''): array
    {
        $filters  = self::normalizeFilters($filters);
        $fiasGuid = self::normalizeFiasGuid($fiasGuid);
        $query    = array_filter(['city' => trim($city), 'postal_code' => trim($postalCode)], static function ($value) {
            return $value !== '';
        });

        if ($query === [] && $fiasGuid === '') {
            return [];
        }

        if ($fiasGuid !== '') {
            $pointQuery = ['fias_guid' => $fiasGuid];
        } else {
            $cities = $cdek->location()->getCities($query + ['size' => 1000]);
            $this->checkResponse($cities);
            $codes = [];

            foreach ($cities as $item) {
                if (\is_array($item) && isset($item['code']) && filter_var($item['code'], FILTER_VALIDATE_INT) > 0) {
                    $codes[(int) $item['code']] = true;
                }
            }

            if (\count($codes) !== 1) {
                return [];
            }

            $pointQuery = ['city_code' => array_key_first($codes)];
        }

        $pointQuery += array_filter($filters, static function ($value) {
            return $value !== '';
        });
        $points = $cdek->deliverypoints()->getDeliveryPoints($pointQuery);
        $this->checkResponse($points);
        $options = [];

        foreach ($points as $point) {
            if (
                !\is_array($point) || !\is_scalar($point['code'] ?? null)
                || trim((string) $point['code']) === ''
                || !\in_array($point['type'] ?? '', ['PVZ', 'POSTAMAT'], true)
            ) {
                continue;
            }

            if ($filters['type'] !== 'ALL' && $point['type'] !== $filters['type']) {
                continue;
            }

            foreach (['is_reception', 'is_handout'] as $flag) {
                if (
                    $filters[$flag] !== ''
                    && (!\array_key_exists($flag, $point)
                        || filter_var($point[$flag], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== ($filters[$flag] === 'true'))
                ) {
                    continue 2;
                }
            }

            $code         = (string) $point['code'];
            $location     = \is_array($point['location'] ?? null) ? $point['location'] : [];
            $addressParts = array_filter([$location['city'] ?? '', $location['address'] ?? ''], 'is_scalar');
            $address      = trim(implode(', ', array_filter($addressParts, static function ($value) {
                return trim((string) $value) !== '';
            })));
            $name           = \is_scalar($point['name'] ?? null) ? (string) $point['name'] : $code;
            $options[$code] = ['value' => $code, 'text' => $code . ' — ' . ($address ?: $name)];
        }

        ksort($options, SORT_NATURAL);

        return array_values($options);
    }

    /**
     * Prevent a normalized provider error from looking like an empty directory.
     *
     * @param   array  $response  Entity response.
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    private function checkResponse(array $response): void
    {
        if (isset($response['error_code']) || isset($response['error_message']) || !empty($response['errors'])) {
            throw new \RuntimeException('CDEK delivery-point lookup failed.');
        }
    }
}
