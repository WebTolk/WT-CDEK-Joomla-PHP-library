<?php
/**
 * @package       WT Cdek library package
 * @version       1.3.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link          https://web-tolk.ru
 * @since         1.0.0
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
 * @since 1.3.0
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
	 * @since 1.3.0
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
