<?php

/**
 * Joomla Form selector for CDEK pickup points and parcel lockers.
 * @package       WT Cdek library package
 * @author        WebTolk
 * @copyright     Copyright (c) 2021 - 2026 WebTolk. All rights reserved.
 * @version       __DEPLOY_VERSION__
 * @license       GNU General Public License version 3 or later.
 * @link          https://web-tolk.ru/
 */

namespace Webtolk\Cdekapi\Fields;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Webtolk\Cdekapi\Service\DeliverypointsService;

/**
 * Renders a dynamically loaded delivery-point selector with optional type and service filters.
 *
 * @since   __DEPLOY_VERSION__
 */
final class DeliverypointsField extends ListField
{
    /**
     * Form field type.
     *
     * @var    string
     * @since   __DEPLOY_VERSION__
     */
    protected $type = 'Deliverypoints';

    /**
     * Build the placeholder and preserve the saved reception-point code before lookup.
     *
     * @return  array  Joomla select options; saved values are not validated against the API here.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getOptions(): array
    {
        $options = [HTMLHelper::_('select.option', '', Text::_('LIB_WTCDEK_FIELD_DELIVERYPOINTS_SELECT'))];

        if ((string) $this->value !== '') {
            $options[] = HTMLHelper::_('select.option', $this->value, $this->value);
        }

        return $options;
    }

    /**
     * Render the point selector and accessible automatic-lookup status.
     * Register the client asset and its endpoint, field IDs and CSRF token.
     *
     * @return  string  HTML for the configured list field and lookup controls.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getInput(): string
    {
        $app = Factory::getApplication();
        $app->getLanguage()->load('lib_webtolk_cdekapi', JPATH_SITE);
        $document = $app->getDocument();
        $sources = [];
        $parameters = ['zip' => '', 'city' => '', 'fias_guid' => ''];

        try {
            $filters = DeliverypointsService::normalizeFilters([
                'type' => (string) ($this->element['point_type'] ?? 'ALL'),
                'is_reception' => (string) ($this->element['is_reception'] ?? ''),
                'is_handout' => (string) ($this->element['is_handout'] ?? ''),
            ]);
        } catch (\InvalidArgumentException $exception) {
            return '<div class="alert alert-warning">' . htmlspecialchars(Text::_('LIB_WTCDEK_FIELD_DELIVERYPOINTS_FILTER_INVALID'), ENT_QUOTES, 'UTF-8') . '</div>';
        }

        // Explicit XML attributes select literal mode, even when empty; never mix the two sources.
        if (isset($this->element['zip']) || isset($this->element['city']) || isset($this->element['fias_guid'])) {
            try {
                $parameters = [
                    'zip' => trim((string) $this->element['zip']),
                    'city' => trim((string) $this->element['city']),
                    'fias_guid' => DeliverypointsService::normalizeFiasGuid((string) $this->element['fias_guid']),
                ];
            } catch (\InvalidArgumentException $exception) {
                return '<div class="alert alert-warning">' . htmlspecialchars(Text::_('LIB_WTCDEK_FIELD_DELIVERYPOINTS_FIAS_INVALID'), ENT_QUOTES, 'UTF-8') . '</div>';
            }
        } else {
            try {
                $sources = Watchfield::resolve($this->form, (string) $this->element['watchfield'], (string) $this->group);

                if ($sources === [] || array_diff(array_keys($sources), ['zip', 'city', 'fias_guid'])) {
                    throw new \UnexpectedValueException('Pickup-point watchfield must map zip, city and/or fias_guid.');
                }
            } catch (\JsonException | \UnexpectedValueException $exception) {
                return '<div class="alert alert-warning">' . htmlspecialchars(Text::_('LIB_WTCDEK_FIELD_WATCHFIELD_INVALID'), ENT_QUOTES, 'UTF-8') . '</div>';
            }
        }

        // zip remains the public XML key; the endpoint receives postal_code.
        if (isset($sources['zip'])) {
            $sources['postal_code'] = $sources['zip'];
            unset($sources['zip']);
        }

        $parameters['postal_code'] = $parameters['zip'];
        unset($parameters['zip']);

        try {
            $options = Watchfield::dataOptions($this->element, [
                'source' => 'ajax',
                'url' => Uri::base(true) . '/index.php?option=com_ajax&group=system&plugin=wtcdekdeliverypoints&format=json',
                'path' => 'data.0.options',
                'parameters' => array_merge($parameters, $filters),
                'requiredAny' => ['city', 'postal_code', 'fias_guid'],
            ]);
        } catch (\UnexpectedValueException $exception) {
            return '<div class="alert alert-warning">' . htmlspecialchars(Text::_('LIB_WTCDEK_FIELD_WATCHFIELD_DATA_ERROR'), ENT_QUOTES, 'UTF-8') . '</div>';
        }

        $wa = $document->getWebAssetManager();
        $wa->getRegistry()->addRegistryFile('media/lib_wtcdek/joomla.assets.json');
        $wa->useScript('wtcdek.deliverypoints');
        return '<div class="wt-cdek-deliverypoints-field" ' . Watchfield::attributes($sources, $options) . '>'
            . parent::getInput()
            . '<div class="small mt-1" data-role="status" role="status" aria-live="polite"></div></div>';
    }
}
