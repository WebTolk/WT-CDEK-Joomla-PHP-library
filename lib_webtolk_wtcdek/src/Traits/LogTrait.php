<?php
/**
 * Общий трейт логирования для библиотеки API СДЭК.
 *
 * @package    WT Cdek library package
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version    1.3.0
 * @license    GNU General Public License version 3 or later. Only for *.php files!
 * @link       https://web-tolk.ru
 */

declare(strict_types=1);

namespace Webtolk\Cdekapi\Traits;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;
use Webtolk\Cdekapi\CdekRequest;

/**
 * Логирование сообщений библиотеки в журнал Joomla.
 *
 * @since  1.3.1
 */
trait LogTrait
{
	/**
	 * Записывает сообщение в лог библиотеки.
	 *
	 * @param   string  $data      Текст сообщения.
	 * @param   string  $priority  Уровень приоритета Joomla.
	 *
	 * @return  void
	 *
	 * @since   1.3.1
	 */
	public function saveToLog(string $data, string $priority = 'NOTICE'): void
	{
		Log::addLogger(
			['text_file' => 'lib_webtolk_cdekapi_cdek.log.php'],
			Log::ALL & ~Log::DEBUG,
			['lib_webtolk_cdekapi_cdek']
		);

		$params = CdekRequest::getPluginParams();

		if ($params instanceof Registry && (int) $params->get('show_library_errors', 0) === 1)
		{
			Factory::getApplication()->enqueueMessage($data, $priority);
		}

		Log::add($data, 'Log::' . $priority, 'lib_webtolk_cdekapi_cdek');
	}
}
