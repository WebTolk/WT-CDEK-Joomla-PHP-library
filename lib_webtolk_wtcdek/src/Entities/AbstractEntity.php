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

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

defined('_JEXEC') or die;

use Webtolk\Cdekapi\CdekRequest;
use Webtolk\Cdekapi\Interfaces\EntityInterface;

abstract class AbstractEntity implements EntityInterface
{
	protected CdekRequest $request;

	/**
	 * @since 1.3.0
	 */
	public function __construct(CdekRequest $request)
	{
		$this->request = $request;
	}
}
