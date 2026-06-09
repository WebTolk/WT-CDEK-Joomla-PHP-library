# Release Package Report: 1.3.2

## Builder

- Command: `php E:\.agents\tools\phing-packager\phing-latest.phar -f phing.xml "3. Package release"`
- Config: `.webtolk/build/package.config.json`
- Release version input: `1.3.2`

## Output

- Package: `.packages/WT Cdek library package_1.3.2.zip`
- Size observed after final rebuild: present in `.packages`.

## Manifest Evidence

- `pkg_lib_wtcdek.xml`
  - `creationDate`: `09.06.2026`
  - `version`: `1.3.2`
- `lib_webtolk_wtcdek/Cdekapi.xml`
  - `creationDate`: `09.06.2026`
  - `version`: `1.3.2`

## Package Hygiene

- Final archive does not include:
  - `.webtolk/`
  - `.apidoc.cdek.ru/`
- The exclude correction is stored in `.webtolk/build/package.config.json`.

## Version Policy

No source XML/docblock version was manually edited. Version replacement happened only during package build.
