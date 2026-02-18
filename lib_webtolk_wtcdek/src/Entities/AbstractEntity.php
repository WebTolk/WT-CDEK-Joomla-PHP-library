<?php
/**
 * Base abstract class for CDEK API entities.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

defined('_JEXEC') or die;

use Webtolk\Cdekapi\CdekRequest;
use Webtolk\Cdekapi\Interfaces\EntityInterface;

abstract class AbstractEntity implements EntityInterface
{
	protected CdekRequest $request;

	public function __construct(CdekRequest $request)
	{
		$this->request = $request;
	}
}
