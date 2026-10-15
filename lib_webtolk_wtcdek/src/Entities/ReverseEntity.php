<?php

/**
 * CDEK reverse API entity.
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

final class ReverseEntity extends AbstractEntity
{
    /**
     * POST /v2/reverse/availability
     *
     * Проверка доступности реверса
     *
     * **Описание:**
     * Метод позволяет проверить доступность реверса до создания прямого заказа. Для выполнения проверки
     * необходимо передать в запросе данные, аналогичные тем, которые будут использоваться при создании заказа,
     * включая направление, данные отправителя и получателя, офис отправки/доставки и выбранный тариф.
     * Если реверс доступен, API вернёт пустой ответ с кодом 200. В случае недоступности услуги или при наличии
     * ошибок в запросе, в ответе будет возвращено сообщение об ошибке с соответствующим описанием.
     *
     * Источник: https://apidoc.cdek.ru/#tag/reverse/operation/checkAvailability
     *
     * @param   array  $request_options  Параметры запроса.
     *
     * @return  array  Ответ API.
     *
     * @since 1.3.0
     */
    public function checkAvailability(array $request_options = []): array
    {
        if (empty($request_options)) {
            return [
                'error_code'    => '500',
                'error_message' => 'Параметры запроса is required',
            ];
        }

        return $this->request->getResponse('/reverse/availability', $request_options, 'POST');
    }
}
