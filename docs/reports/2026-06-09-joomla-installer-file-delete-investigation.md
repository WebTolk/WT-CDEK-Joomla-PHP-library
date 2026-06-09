# Investigation: `File::delete: Failed deleting pkg_lib_wtcdek.xml`

## Symptom

Joomla CLI install failed with:

`Joomla\Filesystem\File::delete: Failed deleting pkg_lib_wtcdek.xml`

Trace:

- `ExtensionInstallCommand::processPathInstallation()`
- `InstallerHelper::cleanupInstall()`
- `Folder::delete()`
- `File::delete()`

## Joomla Core Path

`ExtensionInstallCommand::processPathInstallation()` calls:

- `InstallerHelper::unpack($path, true)`
- `$jInstaller->install($package['extractdir'])`
- `InstallerHelper::cleanupInstall($tmpPath, $package['extractdir'])`

`InstallerHelper::unpack()` extracts the package next to the source ZIP:

`dirname($packageFilename) . '/install_*'`

So when installing:

`E:\dev\WT-CDEK-Joomla-PHP-library\.packages\WT Cdek library package_1.3.2.zip`

the temporary extraction directories were created under:

`E:\dev\WT-CDEK-Joomla-PHP-library\.packages\install_*`

The reported `pkg_lib_wtcdek.xml` was the package manifest inside the temporary extraction directory, not the installed manifest in `administrator/manifests/packages`.

## Evidence

Leftover failed extraction directories were found:

- `.packages/install_6a2842e51e4f6/pkg_lib_wtcdek.xml`
- `.packages/install_6a28433376bd7/pkg_lib_wtcdek.xml`

File attributes:

- not read-only
- owned by `SERVER\Sergey`
- readable/writable from the current account

Minimal PHP delete probe inside sandbox:

- created file: yes
- writable: yes
- `unlink()`: no
- file remained after `unlink()`

The same OSPanel PHP command outside sandbox:

- created file: yes
- writable: yes
- `unlink()`: yes
- file did not remain after `unlink()`

Joomla CLI install outside sandbox:

- command: `php cli\joomla.php extension:install --path="...\WT Cdek library package_1.3.2.zip" -vvv`
- result: `[OK] Extension installed successfully.`

## Conclusion

The failure was caused by sandbox/permission restrictions on PHP `unlink()` during this Codex tool session. Joomla cleanup uses PHP `unlink()` through `Joomla\Filesystem\File::delete()`, so cleanup failed when PHP ran inside the sandbox.

It was not caused by:

- invalid `pkg_lib_wtcdek.xml`
- wrong package structure
- a locked installed manifest
- the CDEK URL encoding code change

## Post-Check

After running Joomla CLI install outside sandbox, `joomla.local` shows:

- `System - WT CDEK`: `1.3.2`
- `PLG_UPDATEWTCDEKDATA`: `1.3.2`
- `WT Cdek library package`: `1.3.2`
- `WebTolk Cdekapi library`: `1.3.2`

Installed manifests also show:

- `libraries/Webtolk/Cdekapi/Cdekapi.xml`: version `1.3.2`, creation date `09.06.2026`
- `administrator/manifests/packages/pkg_lib_wtcdek.xml`: version `1.3.2`, creation date `09.06.2026`

## Residual Notes

Two old temporary extraction directories remain in `.packages/` from the failed sandbox install attempts. They are not release artifacts and can be removed with normal filesystem cleanup.

Running `extension:list` inside sandbox now emits a separate language cache write warning:

`file_put_contents(...administrator/cache/language/...plg_system_wtcdek.ini...): Permission denied`

This is the same class of sandbox write/delete restriction and is separate from the package install result.
