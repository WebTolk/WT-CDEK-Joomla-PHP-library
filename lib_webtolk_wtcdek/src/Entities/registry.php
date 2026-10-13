<?php
/**
 * Registry of CDEK API entity names and classes.
 * @package       WT Cdek library package
 * @subpackage    Webtolk/Cdekapi
 * @author        Sergey Tolkachyov, WebTolk
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @version       __DEPLOY_VERSION__
 * @license       GNU General Public License version 3 or later.
 * @link          https://web-tolk.ru
 * @since         1.3.0
 */
return [
	'calculator'     => \Webtolk\Cdekapi\Entities\CalculatorEntity::class,
	'check'          => \Webtolk\Cdekapi\Entities\CheckEntity::class,
	'delivery'       => \Webtolk\Cdekapi\Entities\DeliveryEntity::class,
	'deliverypoints' => \Webtolk\Cdekapi\Entities\DeliverypointsEntity::class,
	'intakes'        => \Webtolk\Cdekapi\Entities\IntakesEntity::class,
	'international'  => \Webtolk\Cdekapi\Entities\InternationalEntity::class,
	'location'       => \Webtolk\Cdekapi\Entities\LocationEntity::class,
	'oauth'          => \Webtolk\Cdekapi\Entities\OauthEntity::class,
	'orders'         => \Webtolk\Cdekapi\Entities\OrdersEntity::class,
	'passport'       => \Webtolk\Cdekapi\Entities\PassportEntity::class,
	'payment'        => \Webtolk\Cdekapi\Entities\PaymentEntity::class,
	'photoDocument'  => \Webtolk\Cdekapi\Entities\PhotoDocumentEntity::class,
	'prealert'       => \Webtolk\Cdekapi\Entities\PrealertEntity::class,
	'print'          => \Webtolk\Cdekapi\Entities\PrintEntity::class,
	'reverse'        => \Webtolk\Cdekapi\Entities\ReverseEntity::class,
	'webhooks'       => \Webtolk\Cdekapi\Entities\WebhooksEntity::class,
];
