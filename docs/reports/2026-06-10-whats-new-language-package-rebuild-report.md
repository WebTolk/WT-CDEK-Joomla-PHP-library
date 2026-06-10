# Whats New Language Update And Package Rebuild Report

## Request

Update the package `What's new` language constants and rebuild the installable package.

## Changed Files

- `language/ru-RU/pkg_lib_wtcdek.sys.ini`
- `language/en-GB/pkg_lib_wtcdek.sys.ini`

## Language Constant

Updated `PKG_LIB_WTCDEK_WHATS_NEW` for package version `1.3.2`.

Russian text now mentions:

- fixed CDEK city search for names with spaces;
- example `Нижний Новгород`.

English text now mentions:

- fixed CDEK city search for names with spaces;
- example `Nizhny Novgorod`.

## Build Command

`php E:\.agents\tools\phing-packager\phing-latest.phar -f phing.xml "3. Package release"`

## Output

- Package: `.packages/WT Cdek library package_1.3.2.zip`
- Last write time: `2026-06-10 10:29`
- Size: `367534` bytes
- SHA256: `E36A0EC075990CE914942B47C650E14870A1802F8FF6FA71A77FA92C4BFE9D88`
- Archive entries: `59`

## Package Verification

- `language/ru-RU/pkg_lib_wtcdek.sys.ini` inside ZIP contains the updated `1.3.2` `PKG_LIB_WTCDEK_WHATS_NEW` text.
- `language/en-GB/pkg_lib_wtcdek.sys.ini` inside ZIP contains the updated `1.3.2` `PKG_LIB_WTCDEK_WHATS_NEW` text.
- `country_codes` is not mentioned in the package `What's new` text.
- Forbidden local artifact entries found in ZIP: `0`.
- Checked forbidden patterns: `.webtolk/`, `.apidoc.cdek.ru/`, `.agents/`, `.packages/`.

## Manifest Verification

| Manifest | Version | Creation Date |
| --- | --- | --- |
| `pkg_lib_wtcdek.xml` | `1.3.2` | `10.06.2026` |
| `lib_webtolk_wtcdek/Cdekapi.xml` | `1.3.2` | `10.06.2026` |
| `plg_system_wtcdek/wtcdek.xml` | `1.3.2` | `10.06.2026` |
| `plg_task_updatewtcdekdata/updatewtcdekdata.xml` | `1.3.2` | `10.06.2026` |

## Runtime Install

Not executed in this pass. The request was limited to a targeted language constant update and package rebuild.

## Verdict

Passed. The installable package was rebuilt with concise `What's new` language constants that only mention the city-name-with-spaces fix.
