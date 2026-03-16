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

defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

return new class () implements ServiceProviderInterface {
	/**
	 * Register the installer service.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.3.0
	 */
	public function register(Container $container): void
	{
		$container->set(
			InstallerScriptInterface::class,
			new class ($container->get(AdministratorApplication::class)) implements InstallerScriptInterface {
				/**
				 * Constructor.
				 *
				 * @param   AdministratorApplication  $app  The application object.
				 *
				 * @since   1.3.0
				 */
				public function __construct(private readonly AdministratorApplication $app)
				{
				}

				/**
				 * Function called after the extension is installed.
				 *
				 * @param   InstallerAdapter  $adapter  The adapter calling this method.
				 *
				 * @return  bool  True on success.
				 *
				 * @since   1.3.0
				 */
				public function install(InstallerAdapter $adapter): bool
				{
					return true;
				}

				/**
				 * Function called after the extension is uninstalled.
				 *
				 * @param   InstallerAdapter  $adapter  The adapter calling this method.
				 *
				 * @return  bool  True on success.
				 *
				 * @since   1.3.0
				 */
				public function uninstall(InstallerAdapter $adapter): bool
				{
					$this->removeLayouts($adapter->getParent()->getManifest()->layouts);

					return true;
				}

				/**
				 * Function called after the extension is updated.
				 *
				 * @param   InstallerAdapter  $adapter  The adapter calling this method.
				 *
				 * @return  bool  True on success.
				 *
				 * @since   1.3.0
				 */
				public function update(InstallerAdapter $adapter): bool
				{
					return true;
				}

				/**
				 * Function called before extension installation/update/removal.
				 *
				 * @param   string            $type     The type of change.
				 * @param   InstallerAdapter  $adapter  The adapter calling this method.
				 *
				 * @return  bool  True on success.
				 *
				 * @since   1.3.0
				 */
				public function preflight(string $type, InstallerAdapter $adapter): bool
				{
					return true;
				}

				/**
				 * Function called after extension installation/update/removal.
				 *
				 * @param   string            $type     The type of change.
				 * @param   InstallerAdapter  $adapter  The adapter calling this method.
				 *
				 * @return  bool  True on success.
				 *
				 * @since   1.3.0
				 */
				public function postflight(string $type, InstallerAdapter $adapter): bool
				{
					if ($type !== 'uninstall')
					{
						$this->parseLayouts($adapter->getParent()->getManifest()->layouts, $adapter->getParent());
					}

					return true;
				}

				/**
				 * Parse layouts manifest node and copy files/folders to Joomla layouts.
				 *
				 * @param   \SimpleXMLElement  $element    The XML node to process.
				 * @param   Installer          $installer  Installer object.
				 *
				 * @return  bool  True on success.
				 *
				 * @since   1.3.0
				 */
				private function parseLayouts(\SimpleXMLElement $element, Installer $installer): bool
				{
					if (!$element || !count($element->children()))
					{
						return false;
					}

					$folder      = ((string) $element->attributes()->destination) ? '/' . $element->attributes()->destination : null;
					$destination = Path::clean(JPATH_ROOT . '/layouts' . $folder);

					$folder = (string) $element->attributes()->folder;
					$source = ($folder && file_exists($installer->getPath('source') . '/' . $folder))
						? $installer->getPath('source') . '/' . $folder
						: $installer->getPath('source');

					$files = [];

					foreach ($element->children() as $file)
					{
						$path['src']  = Path::clean($source . '/' . $file);
						$path['dest'] = Path::clean($destination . '/' . $file);
						$path['type'] = $file->getName() === 'folder' ? 'folder' : 'file';

						if (basename($path['dest']) !== $path['dest'])
						{
							$newDir = dirname($path['dest']);

							if (!Folder::create($newDir))
							{
								Log::add(
									Text::sprintf('JLIB_INSTALLER_ABORT_CREATE_DIRECTORY', $installer->getManifest()->name, $newDir),
									Log::WARNING,
									'jerror'
								);

								return false;
							}
						}

						$files[] = $path;
					}

					return $installer->copyFiles($files);
				}

				/**
				 * Parse layouts manifest node and remove installed layout files/folders.
				 *
				 * @param   \SimpleXMLElement  $element  The XML node to process.
				 *
				 * @return  bool  True on success.
				 *
				 * @since   1.3.0
				 */
				private function removeLayouts(\SimpleXMLElement $element): bool
				{
					if (!$element || !count($element->children()))
					{
						return false;
					}

					$files  = $element->children();
					$folder = ((string) $element->attributes()->destination) ? '/' . $element->attributes()->destination : null;
					$source = Path::clean(JPATH_ROOT . '/layouts' . $folder);

					foreach ($files as $file)
					{
						$path = Path::clean($source . '/' . $file);

						if (is_dir($path))
						{
							$result = Folder::delete($path);
						}
						else
						{
							$result = File::delete($path);
						}

						if ($result === false)
						{
							Log::add('Failed to delete ' . $path, Log::WARNING, 'jerror');

							return false;
						}
					}

					if (!empty($folder))
					{
						Folder::delete($source);

						$sourceParts = explode('/', $folder);
						array_pop($sourceParts);
						$parentFolder = Path::clean(JPATH_ROOT . '/layouts' . implode('/', $sourceParts));

						if (is_dir($parentFolder)
							&& empty(Folder::files($parentFolder))
							&& empty(Folder::folders($parentFolder))
						)
						{
							Folder::delete($parentFolder);
						}
					}

					return true;
				}
			}
		);
	}
};
