<?php

/**
 * Base implementation for CDEK API entities.
 * @package       WT Cdek library package
 * @subpackage    Webtolk/Cdekapi
 * @author        Sergey Tolkachyov, WebTolk
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @version       __DEPLOY_VERSION__
 * @license       GNU General Public License version 3 or later.
 * @link          https://web-tolk.ru
 * @since         1.3.0
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Entities;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
