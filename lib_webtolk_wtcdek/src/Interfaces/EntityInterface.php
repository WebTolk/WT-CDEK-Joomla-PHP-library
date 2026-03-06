<?php
/**
 * @package       WT Cdek library package
 * @version       1.3.0
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
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
