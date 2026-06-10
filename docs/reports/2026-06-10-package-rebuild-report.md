# Package Rebuild Report: WT Cdek Library Package 1.3.2

## Request

Rebuild the installable Joomla package from the active `.webtolk` release configuration.

## Flow And Platform Context

- Flow root: `.webtolk`
- Package config: `.webtolk/build/package.config.json`
- Packaging bridge: `phing.xml`
- Joomla platform context: already recorded in `.webtolk/context/project-context.yaml`

## Build Command

`php E:\.agents\tools\phing-packager\phing-latest.phar -f phing.xml "3. Package release"`

## Output

- Package: `.packages/WT Cdek library package_1.3.2.zip`
- Last write time: `2026-06-10 09:30`
- Size: `367562` bytes
- SHA256: `C8D6A0D1FFF25C8C1E15E5FA8ED257985BB194E0932D32A9B78298CFC48A7BD1`
- Archive entries: `59`

## Manifest Verification

| Manifest | Version | Creation Date |
| --- | --- | --- |
| `pkg_lib_wtcdek.xml` | `1.3.2` | `10.06.2026` |
| `lib_webtolk_wtcdek/Cdekapi.xml` | `1.3.2` | `10.06.2026` |
| `plg_system_wtcdek/wtcdek.xml` | `1.3.2` | `10.06.2026` |
| `plg_task_updatewtcdekdata/updatewtcdekdata.xml` | `1.3.2` | `10.06.2026` |

## Package Hygiene

Forbidden local artifacts found in ZIP: `0`.

Checked patterns:

- `.webtolk/`
- `.apidoc.cdek.ru/`
- `.agents/`
- `.packages/`

## Runtime Install

Not executed in this rebuild pass. The request was to rebuild the installable package; the previous runtime install verification for `1.3.2` is recorded in `docs/reports/2026-06-09-cdek-get-query-encoding-runtime-verification-report.md`.

## Verdict

Passed. The installable package was rebuilt and inspected successfully.
