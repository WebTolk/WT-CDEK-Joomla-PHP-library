<?php

/**
 * Joomla Form dependency resolution for WT CDEK fields.
 * @package       WT Cdek library package
 * @subpackage    Webtolk/Cdekapi
 * @author        Sergey Tolkachyov, WebTolk
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @version       __DEPLOY_VERSION__
 * @license       GNU General Public License version 3 or later.
 * @link          https://web-tolk.ru
 */

namespace Webtolk\Cdekapi\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\Form;

/**
 * Prepare declarative configuration for the Watchfield WebAsset through Joomla Form.
 *
 * @since  __DEPLOY_VERSION__
 */
final class Watchfield
{
    /**
     * Resolve dependency IDs, input names and initial values without assuming a form control prefix.
     * A dot-separated source name can address another Joomla fields group.
     *
     * @param   Form    $form       Owning Joomla form.
     * @param   string  $attribute  Single source name or JSON object of parameter => source name.
     * @param   string  $group      Current field group, used for unqualified names.
     *
     * @return  array<string, array{id:string,name:string,value:mixed}>
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function resolve(Form $form, string $attribute, string $group = ''): array
    {
        $attribute = trim($attribute);

        if ($attribute === '') {
            return [];
        }

        if ($attribute[0] === '{' || $attribute[0] === '[') {
            $map = json_decode($attribute, true, 16, JSON_THROW_ON_ERROR);

            if (!\is_array($map) || array_values($map) === $map) {
                throw new \UnexpectedValueException('watchfield must be a named JSON object.');
            }
        } else {
            $map = ['value' => $attribute];
        }

        $sources = [];

        foreach ($map as $parameter => $source) {
            if (!\is_string($parameter) || $parameter === '' || !\is_string($source) || trim($source) === '') {
                throw new \UnexpectedValueException('watchfield keys and field names must be non-empty strings.');
            }

            $source      = trim($source);
            $sourceGroup = $group;

            if (($separator = strrpos($source, '.')) !== false) {
                $sourceGroup = substr($source, 0, $separator);
                $source      = substr($source, $separator + 1);
            }

            $field = $form->getField($source, $sourceGroup ?: null);

            if (!$field) {
                throw new \UnexpectedValueException('watchfield source is not present in the Joomla form.');
            }

            $sources[$parameter] = ['id' => $field->id, 'name' => $field->name, 'value' => $field->value];
        }

        return $sources;
    }

    /** Build the shared data-provider configuration; the options key is literal, the path is dotted. */
    public static function dataOptions(\SimpleXMLElement $element, array $defaults): array
    {
        $options = $defaults;

        foreach (['source', 'options', 'path', 'url'] as $key) {
            $attribute = 'watchfield-data-' . ($key === 'source' ? 'source' : $key);

            if (isset($element[$attribute])) {
                $options[$key] = trim((string) $element[$attribute]);
            }
        }

        if (($options['source'] ?? '') === 'onpage') {
            $options['source'] = 'scriptoptions';
        }

        if ($options['source'] !== $defaults['source'] && !isset($element['watchfield-data-path'])) {
            $options['path'] = '';
        }

        if (
            !\in_array($options['source'] ?? '', ['ajax', 'scriptoptions'], true)
            || ($options['source'] === 'ajax' && empty($options['url']))
            || ($options['source'] === 'scriptoptions' && empty($options['options']))
        ) {
            throw new \UnexpectedValueException('Invalid watchfield data source.');
        }

        return $options;
    }

    /** Encode declarative configuration for HTML, including repeatable form rows. */
    public static function attributes(array $sources, array $options): string
    {
        $names = [];

        foreach ($sources as $parameter => $source) {
            $names[$parameter] = $source['name'];
        }

        $encode = static fn ($value): string => htmlspecialchars(json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

        return 'data-watchfield="' . $encode((object) $names) . '" data-watchfield-options="' . $encode($options) . '"';
    }
}
