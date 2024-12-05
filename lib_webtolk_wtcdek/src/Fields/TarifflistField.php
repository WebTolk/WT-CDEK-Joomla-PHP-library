<?php
/**
 * Library to connect to CDEK service.
 * @package    WT Cdek library package
 * @author     Sergey Tolkachyov
 * @copyright   Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version     1.2.0
 * @license     GNU General Public License version 3 or later. Only for *.php files!
 * @link       https://web-tolk.ru
 */

namespace Webtolk\Cdekapi\Fields;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;
use Webtolk\Cdekapi\Cdek;

class TarifflistField extends ListField
{

	protected $type = 'Tarifflist';

	protected function getInput()
	{
        $tariff_shop_options = [];
        $tariff_dostavka_options = [];
        $cdek = new Cdek();
        $tariff_shop = $cdek->getTariffListShop();
        foreach ($tariff_shop as $tariff)
        {
            $tariff_shop_options[] = HTMLHelper::_('select.option', $tariff['code'], $tariff['name'] . ' (code: ' . $tariff['code'] . ')');
        }
        $tariff_dostavka = $cdek->getTariffListDostavka();

        foreach ($tariff_dostavka as $tariff)
        {
            $tariff_dostavka_options[] = HTMLHelper::_('select.option', $tariff['code'],     $tariff['name'] . ' (code: ' . $tariff['code'] . ')');
        }

        $groups = [
            'shop' => [
                'id' => 'shop',
                'text' => 'Интернет-магазин',
                'items' => $tariff_shop_options
            ],
            'dostavka' => [
                'id' => 'dostavka',
                'text' => 'Доставка',
                'items' => $tariff_dostavka_options
            ]
        ];

        return HTMLHelper::_('select.groupedlist', $groups, 'field_name', ['id' => 'field_id_attr', 'group.id' => 'id', 'list.attr' => ['class' => 'form-select']]);
    }
}
