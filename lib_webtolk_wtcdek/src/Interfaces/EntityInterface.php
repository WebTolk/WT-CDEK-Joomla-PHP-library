<?php
/**
 * Base interface for CDEK API entities.
 *
 * @package    WT Cdek library package
 * @since      1.2.1
 */

declare(strict_types = 1);

namespace Webtolk\Cdekapi\Interfaces;

use Webtolk\Cdekapi\CdekRequest;

\defined('_JEXEC') or die;

interface EntityInterface
{
	public function __construct(CdekRequest $request);
}
