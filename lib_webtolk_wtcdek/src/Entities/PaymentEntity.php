<?php

/**
 * CDEK payment API entity.
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

final class PaymentEntity extends AbstractEntity
{
    /**
     * GET /payment
     *
     * Получение информации о переводе наложенного платежа
     *
     * **Описание:**
     * Метод предназначен для получения информации о заказах, по которым был переведен наложенный платеж
     * интернет-магазину в заданную дату
     *
     * Источник: https://apidoc.cdek.ru/#tag/payment/operation/get_5
     *
     * @param   array{
     *             date?: string
     *         }  $request_options  Параметры запроса.
     *
     * @return  array  Ответ API.
     *
     * @since 1.3.0
     */
    public function get(array $request_options = []): array
    {
        if (empty($request_options['date'])) {
            return [
                'error_code'    => '500',
                'error_message' => 'Required option: date',
            ];
        }

        return $this->request->getResponse('/payment', $request_options, 'GET');
    }
}
