<?php
/**
 * Language strings for the shared Watchfield WebAsset.
 * @package       WT Cdek library package
 * @subpackage    Webtolk/Cdekapi
 * @author        Sergey Tolkachyov, WebTolk
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @version       __DEPLOY_VERSION__
 * @license       GNU General Public License version 3 or later.
 * @link          https://web-tolk.ru
 * @since         __DEPLOY_VERSION__
 */
namespace Webtolk\Cdekapi\WebAsset\AssetItem;

\defined('_JEXEC') or die;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\WebAsset\WebAssetAttachBehaviorInterface;
use Joomla\CMS\WebAsset\WebAssetItem;

/** Export translations when the common script is attached, including in administrator forms. */
final class WatchfieldAssetItem extends WebAssetItem implements WebAssetAttachBehaviorInterface
{
    public function onAttachCallback(Document $doc)
    {
        Factory::getApplication()->getLanguage()->load('lib_webtolk_cdekapi', JPATH_SITE);

        foreach (['WATCHFIELD_SOURCE_ERROR', 'WATCHFIELD_DATA_ERROR', 'WATCHFIELD_LOADING', 'TARIFFINFO_SELECT', 'TARIFFINFO_NOT_FOUND',
            'DELIVERYPOINTS_SELECT', 'DELIVERYPOINTS_LOADING', 'DELIVERYPOINTS_EMPTY', 'DELIVERYPOINTS_ERROR',
            'DELIVERYPOINTS_MISSING', 'DELIVERYPOINTS_CHANGED', 'DELIVERYPOINTS_ADDRESS_REQUIRED'] as $key) {
            Text::script('LIB_WTCDEK_FIELD_' . $key);
        }
    }
}
