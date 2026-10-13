<?php
/**
 * Contract for CDEK API entities.
 * @package       WT Cdek library package
 * @subpackage    Webtolk/Cdekapi
 * @author        Sergey Tolkachyov, WebTolk
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @version       __DEPLOY_VERSION__
 * @license       GNU General Public License version 3 or later.
 * @link          https://web-tolk.ru
 * @since         1.3.0
 */

declare(strict_types = 1);

namespace Webtolk\Cdekapi\Interfaces;

use Webtolk\Cdekapi\CdekRequest;

\defined('_JEXEC') or die;

interface EntityInterface
{
	/**
	 * @since 1.3.0
	 */
	public function __construct(CdekRequest $request);
}
